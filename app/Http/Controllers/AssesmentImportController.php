<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use App\Helpers\WordParser;
use App\Models\Asesmen;
use App\Models\FormAK01;
use App\Models\FormAK02;
use App\Models\FormAK02Bukti;
use App\Models\FormAK03;
use App\Models\FormAK05;
use App\Models\FormIA05;
use App\Models\FormIA05Jawaban;
use App\Models\Schema;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AssesmentImportController extends Controller
{
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:doc,docx',
                'schema_id' => 'required|exists:schemas,id',
                'asesor_id' => 'required|exists:users,id',
                'asesi_id' => 'required|exists:users,id',
                'form_type' => 'required|in:AK01,AK02,AK03,AK05,IA05'
            ]);

            $filePath = $request->file('file')->getRealPath();
            $extension = $request->file('file')->getClientOriginalExtension();
            
            $texts = [];
            $structuredData = [];

            if (strtolower($extension) === 'docx') {
                $result = $this->parseDocxWithXml($filePath);
                $texts = $result['texts'];
                $structuredData = $result['structured'];
            } else {
                $phpWord = IOFactory::load($filePath);
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        $texts = array_merge($texts, WordParser::extractText($element));
                    }
                }
            }

            $formType = $request->input('form_type');
            $schemaId = $request->input('schema_id');
            $asesorId = $request->input('asesor_id');
            $asesiId = $request->input('asesi_id');

            // Parse data berdasarkan jenis formulir
            switch ($formType) {
                case 'AK01':
                    $data = $this->parseAK01($texts, $structuredData);
                    break;
                case 'AK02':
                    $data = $this->parseAK02($texts, $structuredData);
                    break;
                case 'AK03':
                    $data = $this->parseAK03($texts, $structuredData);
                    break;
                case 'AK05':
                    $data = $this->parseAK05($texts, $structuredData);
                    break;
                case 'IA05':
                    $data = $this->parseIA05($texts, $structuredData);
                    break;
                default:
                    throw new Exception('Jenis formulir tidak dikenali');
            }

            // Simpan ke database
            $result = $this->saveToDatabase($formType, $data, $schemaId, $asesorId, $asesiId);

            return response()->json([
                'success' => true,
                'message' => 'Formulir ' . $formType . ' berhasil diimpor',
                'data' => $result
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    private function parseAK01($texts, $structuredData)
    {
        $data = [
            'metode_pengumpulan' => [],
            'pernyataan_asesi' => '',
            'pernyataan_asesor' => '',
            'persetujuan_asesi' => '',
            'tanggal_asesmen' => null,
            'waktu_asesmen' => null,
            'tuk' => null
        ];

        $currentSection = '';
        
        foreach ($texts as $index => $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if (empty($line)) continue;

            // Deteksi section
            if (strpos($line, 'Bukti yang akan dikumpulkan') !== false) {
                $currentSection = 'metode';
                continue;
            } elseif (strpos($line, 'Asesi :') !== false) {
                $currentSection = 'pernyataan_asesi';
                continue;
            } elseif (strpos($line, 'Asesor :') !== false) {
                $currentSection = 'pernyataan_asesor';
                continue;
            } elseif (strpos($line, 'Pelaksanaan asesmen disepakati pada') !== false) {
                $currentSection = 'jadwal';
                continue;
            }

            // Parse metode pengumpulan bukti
            if ($currentSection === 'metode') {
                if (strpos($line, 'Hasil Verifikasi Portofolio') !== false) {
                    $data['metode_pengumpulan']['verifikasi_portofolio'] = $this->isChecked($line);
                } elseif (strpos($line, 'Hasil Reviu Produk') !== false) {
                    $data['metode_pengumpulan']['reviu_produk'] = $this->isChecked($line);
                } elseif (strpos($line, 'Hasil Observasi Langsung') !== false) {
                    $data['metode_pengumpulan']['observasi_langsung'] = $this->isChecked($line);
                } elseif (strpos($line, 'Hasil Kegiatan Terstruktur') !== false) {
                    $data['metode_pengumpulan']['kegiatan_terstruktur'] = $this->isChecked($line);
                } elseif (strpos($line, 'Hasil Pertanyaan Lisan') !== false) {
                    $data['metode_pengumpulan']['pertanyaan_lisan'] = $this->isChecked($line);
                } elseif (strpos($line, 'Hasil Pertanyaan Tertulis') !== false) {
                    $data['metode_pengumpulan']['pertanyaan_tertulis'] = $this->isChecked($line);
                } elseif (strpos($line, 'Hasil Pertanyaan Wawancara') !== false) {
                    $data['metode_pengumpulan']['wawancara'] = $this->isChecked($line);
                } elseif (strpos($line, 'Lainnya') !== false) {
                    $data['metode_pengumpulan']['metode_lainnya'] = $this->extractLainnya($line);
                }
            }

            // Parse pernyataan asesmen
            if($currentSection === 'pernyataan_asesi' && !empty(trim($line))) {
                $data['pernyataan_asesi'] .= $line . "\n";
            } elseif ($currentSection === 'pernyataan_asesor' && !empty(trim($line))) {
                $data['pernyataan_asesor'] .= $line . "\n";
            }

            // Parse jadwal
            if ($currentSection === 'jadwal') {
                if (preg_match('/Hari\/\s*Tanggal\s*:\s*(.+)/i', $line, $matches)) {
                    $data['tanggal_asesmen'] = $this->parseDate($matches[1]);
                } elseif (preg_match('/Waktu\s*:\s*(.+)/i', $line, $matches)) {
                    $data['waktu_asesmen'] = $matches[1];
                } elseif (preg_match('/TUK\s*:\s*(.+)/i', $line, $matches)) {
                    $data['tuk'] = trim($matches[1]);
                }
            }

            // Parse persetujuan asesi (setelah pernyataan asesor)
            if (strpos($line, 'Saya setuju mengikuti asesmen') !== false) {
                $currentSection = 'persetujuan_asesi';
            }

            if ($currentSection === 'persetujuan_asesi' && !empty(trim($line))) {
                $data['persetujuan_asesi'] .= $line . "\n";
            }
        }

        // Bersihkan teks pernyataan
        $data['pernyataan_asesi'] = trim($data['pernyataan_asesi']);
        $data['pernyataan_asesor'] = trim($data['pernyataan_asesor']);
        $data['persetujuan_asesi'] = trim($data['persetujuan_asesi']);

        return $data;
    }

    private function parseAK02($texts, $structuredData)
    {
        $data = [
            'rekomendasi' => null,
            'tindak_lanjut' => '',
            'komentar_asesor' => '',
            'bukti_per_unit' => [],
            'current_unit' => null
        ];

        $currentSection = '';
        $units = Unit::where('schema_id', request('schema_id'))->get()->keyBy('judul_unit');
        
        foreach ($texts as $index => $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if (empty($line)) continue;

            // Deteksi section
            if (strpos($line, 'Rekomendasi hasil asesmen') !== false) {
                $currentSection = 'rekomendasi';
                continue;
            } elseif (strpos($line, 'Tindak lanjut yang dibutuhkan') !== false) {
                $currentSection = 'tindak_lanjut';
                continue;
            } elseif (strpos($line, 'Komentar/ Observasi oleh asesor') !== false) {
                $currentSection = 'komentar';
                continue;
            }

            // Parse rekomendasi
            if ($currentSection === 'rekomendasi') {
                if (strpos($line, 'Kompeten') !== false && $this->isChecked($line)) {
                    $data['rekomendasi'] = 'Kompeten';
                } elseif (strpos($line, 'Belum kompeten') !== false && $this->isChecked($line)) {
                    $data['rekomendasi'] = 'Belum Kompeten';
                }
            }

            // Parse tindak lanjut dan komentar
            if ($currentSection === 'tindak_lanjut' && !empty(trim($line))) {
                $data['tindak_lanjut'] .= $line . "\n";
            } elseif ($currentSection === 'komentar' && !empty(trim($line))) {
                $data['komentar_asesor'] .= $line . "\n";
            }

            // Parse bukti per unit kompetensi
            foreach ($units as $unit) {
                if (strpos($line, $unit->judul_unit) !== false) {
                    $data['current_unit'] = $unit->id;
                    $data['bukti_per_unit'][$unit->id] = [
                        'observasi_demonstrasi' => false,
                        'portofolio' => false,
                        'pernyataan_pihak_ketiga' => false,
                        'pertanyaan_lisan' => false,
                        'pertanyaan_tertulis' => false,
                        'proyek_kerja' => false,
                        'lainnya' => false,
                        'keterangan_lainnya' => null
                    ];
                }
            }

            // Parse metode bukti untuk unit saat ini
            if ($data['current_unit']) {
                $unitId = $data['current_unit'];
                if (strpos($line, 'Observasi Demonstrasi') !== false) {
                    $data['bukti_per_unit'][$unitId]['observasi_demonstrasi'] = $this->isCheckedInTable($line);
                } elseif (strpos($line, 'Portofolio') !== false) {
                    $data['bukti_per_unit'][$unitId]['portofolio'] = $this->isCheckedInTable($line);
                } elseif (strpos($line, 'Pernyataan Pihak Ketiga') !== false) {
                    $data['bukti_per_unit'][$unitId]['pernyataan_pihak_ketiga'] = $this->isCheckedInTable($line);
                } elseif (strpos($line, 'Pertanyaan Lisan') !== false) {
                    $data['bukti_per_unit'][$unitId]['pertanyaan_lisan'] = $this->isCheckedInTable($line);
                } elseif (strpos($line, 'Pertanyaan Tertulis') !== false) {
                    $data['bukti_per_unit'][$unitId]['pertanyaan_tertulis'] = $this->isCheckedInTable($line);
                } elseif (strpos($line, 'Proyek Kerja') !== false) {
                    $data['bukti_per_unit'][$unitId]['proyek_kerja'] = $this->isCheckedInTable($line);
                } elseif (strpos($line, 'Lainnya') !== false) {
                    $data['bukti_per_unit'][$unitId]['lainnya'] = $this->isCheckedInTable($line);
                    $data['bukti_per_unit'][$unitId]['keterangan_lainnya'] = $this->extractLainnya($line);
                }
            }
        }

        // Bersihkan teks
        $data['tindak_lanjut'] = trim($data['tindak_lanjut']);
        $data['komentar_asesor'] = trim($data['komentar_asesor']);

        return $data;
    }

    private function parseAK03($texts, $structuredData)
    {
        $data = [
            'penjelasan_proses' => false,
            'kesempatan_belajar' => false,
            'diskusi_metoda' => false,
            'penggalian_bukti' => false,
            'demonstrasi_kompetensi' => false,
            'penjelasan_keputusan' => false,
            'umpan_balik' => false,
            'studi_dokumen' => false,
            'jaminan_kerahasiaan' => false,
            'komunikasi_efektif' => false,
            'catatan_tambahan' => ''
        ];

        $currentSection = '';
        
        foreach ($texts as $index => $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if (empty($line)) continue;

            // Deteksi section komentar tambahan
            if (strpos($line, 'Catatan/komentar lainnya') !== false) {
                $currentSection = 'catatan_tambahan';
                continue;
            }

            // Parse checklist umpan balik
            if (strpos($line, 'Saya mendapatkan penjelasan yang cukup') !== false) {
                $data['penjelasan_proses'] = $this->isCheckedYa($line);
            } elseif (strpos($line, 'Saya diberikan kesempatan untuk mempelajari') !== false) {
                $data['kesempatan_belajar'] = $this->isCheckedYa($line);
            } elseif (strpos($line, 'Asesor memberikan kesempatan untuk mendiskusikan') !== false) {
                $data['diskusi_metoda'] = $this->isCheckedYa($line);
            } elseif (strpos($line, 'Asesor berusaha menggali seluruh bukti') !== false) {
                $data['penggalian_bukti'] = $this->isCheckedYa($line);
            } elseif (strpos($line, 'Saya sepenuhnya diberikan kesempatan') !== false) {
                $data['demonstrasi_kompetensi'] = $this->isCheckedYa($line);
            } elseif (strpos($line, 'Saya mendapatkan penjelasan yang memadai') !== false) {
                $data['penjelasan_keputusan'] = $this->isCheckedYa($line);
            } elseif (strpos($line, 'Asesor memberikan umpan balik yang mendukung') !== false) {
                $data['umpan_balik'] = $this->isCheckedYa($line);
            } elseif (strpos($line, 'Asesor bersama saya mempelajari semua dokumen') !== false) {
                $data['studi_dokumen'] = $this->isCheckedYa($line);
            } elseif (strpos($line, 'Saya mendapatkan jaminan kerahasiaan') !== false) {
                $data['jaminan_kerahasiaan'] = $this->isCheckedYa($line);
            } elseif (strpos($line, 'Asesor menggunakan keterampilan komunikasi') !== false) {
                $data['komunikasi_efektif'] = $this->isCheckedYa($line);
            }

            // Parse catatan tambahan
            if ($currentSection === 'catatan_tambahan' && !empty(trim($line))) {
                $data['catatan_tambahan'] .= $line . "\n";
            }
        }

        $data['catatan_tambahan'] = trim($data['catatan_tambahan']);

        return $data;
    }

    private function parseAK05($texts, $structuredData)
    {
        $data = [
            'rekomendasi' => null,
            'keterangan' => '',
            'aspek_negatif_positif' => '',
            'pencatatan_penolakan' => '',
            'saran_perbaikan' => '',
            'no_reg_asesor' => null
        ];

        $currentSection = '';
        $currentAsesi = null;
        
        foreach ($texts as $index => $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if (empty($line)) continue;

            // Deteksi section
            if (strpos($line, 'Aspek Negatif dan Positif') !== false) {
                $currentSection = 'aspek_negatif_positif';
                continue;
            } elseif (strpos($line, 'Pencatatan Penolakan Hasil Asesmen') !== false) {
                $currentSection = 'pencatatan_penolakan';
                continue;
            } elseif (strpos($line, 'Saran Perbaikan') !== false) {
                $currentSection = 'saran_perbaikan';
                continue;
            } elseif (strpos($line, 'No. Reg') !== false) {
                if (preg_match('/No\.\s*Reg\s*:\s*(.+)/i', $line, $matches)) {
                    $data['no_reg_asesor'] = trim($matches[1]);
                }
            }

            // Parse rekomendasi per asesi
            if (preg_match('/^\d+\./', $line)) {
                if (strpos($line, '☐') !== false) {
                    if (strpos($line, 'K') !== false && $this->isChecked($line)) {
                        $data['rekomendasi'] = 'K';
                    } elseif (strpos($line, 'BK') !== false && $this->isChecked($line)) {
                        $data['rekomendasi'] = 'BK';
                    }
                }
            }

            // Parse section content
            if ($currentSection === 'aspek_negatif_positif' && !empty(trim($line))) {
                $data['aspek_negatif_positif'] .= $line . "\n";
            } elseif ($currentSection === 'pencatatan_penolakan' && !empty(trim($line))) {
                $data['pencatatan_penolakan'] .= $line . "\n";
            } elseif ($currentSection === 'saran_perbaikan' && !empty(trim($line))) {
                $data['saran_perbaikan'] .= $line . "\n";
            }

            // Parse keterangan untuk rekomendasi BK
            if ($data['rekomendasi'] === 'BK' && !empty(trim($line)) && 
                !in_array($currentSection, ['aspek_negatif_positif', 'pencatatan_penolakan', 'saran_perbaikan'])) {
                $data['keterangan'] .= $line . "\n";
            }
        }

        // Bersihkan teks
        $data['aspek_negatif_positif'] = trim($data['aspek_negatif_positif']);
        $data['pencatatan_penolakan'] = trim($data['pencatatan_penolakan']);
        $data['saran_perbaikan'] = trim($data['saran_perbaikan']);
        $data['keterangan'] = trim($data['keterangan']);

        return $data;
    }

    private function parseIA05($texts, $structuredData)
    {
        $data = [
            'jawaban' => [],
            'umpan_balik' => '',
            'no_reg_asesor' => null
        ];

        $currentSection = '';
        
        foreach ($texts as $index => $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if (empty($line)) continue;

            // Deteksi section
            if (strpos($line, 'Umpan balik untuk asesi') !== false) {
                $currentSection = 'umpan_balik';
                continue;
            } elseif (strpos($line, 'No. Reg') !== false) {
                if (preg_match('/No\.\s*Reg\s*:\s*(.+)/i', $line, $matches)) {
                    $data['no_reg_asesor'] = trim($matches[1]);
                }
            }

            // Parse jawaban soal
            if (preg_match('/^\d+\./', $line) || isset($structuredData[$index]['number'])) {
                $soalNumber = isset($structuredData[$index]['number']) ? 
                    (int)$structuredData[$index]['number'] : 
                    (int)preg_replace('/\..*$/', '', $line);
                
                $pencapaian = $this->isCheckedYa($line);
                
                // Extract jawaban jika ada
                $jawaban = preg_replace('/^\d+\.\s*/', '', $line);
                $jawaban = preg_replace('/\s*☐\s*(Ya|Tidak).*$/i', '', $jawaban);
                $jawaban = trim($jawaban);
                
                if (!empty($jawaban) || $soalNumber > 0) {
                    $data['jawaban'][$soalNumber] = [
                        'jawaban' => $jawaban,
                        'pencapaian' => $pencapaian
                    ];
                }
            }

            // Parse umpan balik
            if ($currentSection === 'umpan_balik' && !empty(trim($line))) {
                $data['umpan_balik'] .= $line . "\n";
            }
        }

        $data['umpan_balik'] = trim($data['umpan_balik']);

        return $data;
    }

    private function isChecked($line)
    {
        return strpos($line, '☐') !== false || 
               strpos($line, '√') !== false || 
               strpos($line, '✓') !== false ||
               strpos($line, '[X]') !== false ||
               strpos($line, '[x]') !== false;
    }

    private function isCheckedYa($line)
    {
        // Cek jika kotak "Ya" dicentang
        $pattern = '/Ya\s*☐\s*(?:\\[X\\]|\\[x\\]|√|✓)/';
        return preg_match($pattern, $line) || 
               (strpos($line, 'Ya') !== false && $this->isChecked($line));
    }

    private function isCheckedInTable($line)
    {
        // Untuk tabel, cek jika ada centang di cell
        return preg_match('/[☐√✓\[\]xX]\s*$/', $line) || 
               preg_match('/^\s*[☐√✓\[\]xX]\s*$/', $line);
    }

    private function extractLainnya($line)
    {
        if (preg_match('/Lainnya\s*(?:\.{3,}|:)?\s*(.+)/i', $line, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    private function parseDate($dateString)
    {
        try {
            return \Carbon\Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDocxWithXml($filePath)
    {
        // Implementasi sama seperti di Apl02ImportController
        $zip = new \ZipArchive;
        $texts = [];
        $structuredData = [];
        
        if ($zip->open($filePath) === true) {
            $documentXml = $zip->getFromName('word/document.xml');
            $numberingXml = $zip->getFromName('word/numbering.xml');
            $zip->close();

            $doc = simplexml_load_string($documentXml);
            $doc->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            $counters = [];
            $textIndex = 0;

            foreach ($doc->xpath('//w:p') as $p) {
                $numPr = $p->xpath('.//w:numPr/w:numId');
                $level = $p->xpath('.//w:numPr/w:ilvl');
                $textParts = $p->xpath('.//w:t');

                if ($textParts) {
                    $text = trim(implode(' ', array_map('strval', $textParts)));
                } else {
                    $text = '';
                }

                $texts[] = $text;

                if ($numPr && !empty($text)) {
                    $numId = (string)$numPr[0]['w:val'];
                    $lvl = $level ? (int)$level[0]['w:val'] : 0;

                    if (!isset($counters[$numId])) $counters[$numId] = [];
                    if (!isset($counters[$numId][$lvl])) $counters[$numId][$lvl] = 1;
                    else $counters[$numId][$lvl]++;

                    $numberParts = [];
                    for ($i = 0; $i <= $lvl; $i++) {
                        $numberParts[] = $counters[$numId][$i] ?? 1;
                    }
                    $number = implode('.', $numberParts);

                    $structuredData[$textIndex] = [
                        'number' => $number,
                        'level' => $lvl,
                        'numId' => $numId
                    ];
                }

                $textIndex++;
            }
        }

        return [
            'texts' => $texts,
            'structured' => $structuredData
        ];
    }

    private function saveToDatabase($formType, $data, $schemaId, $asesorId, $asesiId)
    {
        // Cari atau buat record asesmen
        $asesmen = Asesmen::firstOrCreate(
            [
                'schema_id' => $schemaId,
                'asesor_id' => $asesorId,
                'asesi_id' => $asesiId,
                'tanggal_asesmen' => $data['tanggal_asesmen'] ?? now()->format('Y-m-d')
            ],
            [
                'tuk' => $data['tuk'] ?? 'Sewaktu',
                'waktu_asesmen' => $data['waktu_asesmen'] ?? now()->format('H:i:s'),
                'lokasi_tuk' => $data['tuk'] ?? 'Lokasi Default',
                'status' => 'completed'
            ]
        );

        // Simpan data berdasarkan jenis formulir
        switch ($formType) {
            case 'AK01':
                $form = FormAK01::updateOrCreate(
                    ['asesmen_id' => $asesmen->id],
                    array_merge($data['metode_pengumpulan'], [
                        'pernyataan_asesi' => $data['pernyataan_asesi'],
                        'pernyataan_asesor' => $data['pernyataan_asesor'],
                        'persetujuan_asesi' => $data['persetujuan_asesi'],
                        'tanggal_tanda_tangan_asesor' => now(),
                        'tanggal_tanda_tangan_asesi' => now()
                    ])
                );
                break;

            case 'AK02':
                $form = FormAK02::updateOrCreate(
                    ['asesmen_id' => $asesmen->id],
                    [
                        'rekomendasi' => $data['rekomendasi'],
                        'tindak_lanjut' => $data['tindak_lanjut'],
                        'komentar_asesor' => $data['komentar_asesor'],
                        'tanggal_tanda_tangan_asesor' => now(),
                        'tanggal_tanda_tangan_asesi' => now()
                    ]
                );

                // Simpan bukti per unit
                foreach ($data['bukti_per_unit'] as $unitId => $bukti) {
                    FormAK02Bukti::updateOrCreate(
                        [
                            'form_ak02_id' => $form->id,
                            'unit_id' => $unitId
                        ],
                        $bukti
                    );
                }
                break;

            case 'AK03':
                $form = FormAK03::updateOrCreate(
                    ['asesmen_id' => $asesmen->id],
                    array_merge([
                        'catatan_tambahan' => $data['catatan_tambahan']
                    ], array_filter($data, function($key) {
                        return $key !== 'catatan_tambahan';
                    }, ARRAY_FILTER_USE_KEY))
                );
                break;

            case 'AK05':
                $form = FormAK05::updateOrCreate(
                    ['asesmen_id' => $asesmen->id],
                    [
                        'rekomendasi' => $data['rekomendasi'],
                        'keterangan' => $data['keterangan'],
                        'aspek_negatif_positif' => $data['aspek_negatif_positif'],
                        'pencatatan_penolakan' => $data['pencatatan_penolakan'],
                        'saran_perbaikan' => $data['saran_perbaikan'],
                        'no_reg_asesor' => $data['no_reg_asesor'],
                        'tanggal_tanda_tangan_asesor' => now()
                    ]
                );
                break;

            case 'IA05':
                $form = FormIA05::updateOrCreate(
                    ['asesmen_id' => $asesmen->id],
                    [
                        'umpan_balik' => $data['umpan_balik'],
                        'no_reg_asesor' => $data['no_reg_asesor'],
                        'tanggal_tanda_tangan_asesor' => now(),
                        'tanggal_tanda_tangan_asesi' => now()
                    ]
                );

                // Simpan jawaban soal
                foreach ($data['jawaban'] as $nomorSoal => $jawaban) {
                    FormIA05Jawaban::updateOrCreate(
                        [
                            'form_ia05_id' => $form->id,
                            'nomor_soal' => $nomorSoal
                        ],
                        [
                            'jawaban' => $jawaban['jawaban'],
                            'pencapaian' => $jawaban['pencapaian']
                        ]
                    );
                }
                break;
        }

        return [
            'asesmen_id' => $asesmen->id,
            'form_id' => $form->id,
            'form_type' => $formType
        ];
    }
}