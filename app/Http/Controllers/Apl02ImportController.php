<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use App\Helpers\WordParser;
use App\Models\Schema;
use App\Models\Unit;
use App\Models\Element;
use App\Models\KriteriaUntukKerja;
use Exception;

class Apl02ImportController extends Controller
{
    
     public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:doc,docx',
                'jurusan_id' => 'required|exists:jurusan,id'
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

            $jurusanId = $request->input('jurusan_id');
            $judulSkema = null;
            $nomorSkema = null;
            $units = [];
            $unitKe = 0;
            $kodeUnit = null;
            $judulUnit = null;
            $currentElemenIndex = null;
            $currentKukCounter = 1; // Counter untuk urutan KUK per elemen

            // Flag untuk menandai bahwa kita telah menemukan kode unit dan sedang mencari judul unit
            $expectingJudulUnit = false;
            $currentUnitIndex = null;

            foreach ($texts as $index => $line) {
                $line = trim(preg_replace('/\s+/', ' ', $line));
                if (empty($line)) continue;

                $cleanLine = str_replace(['|', '**', '*'], '', $line);
                $cleanLine = trim($cleanLine);

                $numberInfo = isset($structuredData[$index]) ? $structuredData[$index] : null;

                // 1. Capture Judul Skema
                if (preg_match('/Judul\s*:?\s*(.*(?:Pemrogram\s+Junior|Junior\s+Coder).*)/i', $cleanLine, $m) && !$judulSkema) {
                    $judulSkema = trim($m[1]);
                    continue;
                }

                if (preg_match('/(Pemrogram\s+Junior.*(?:Coder)?)/i', $cleanLine, $m) && !$judulSkema && !preg_match('/unit/i', $cleanLine)) {
                    $judulSkema = trim($m[1]);
                    continue;
                }

                // 2. Capture Nomor Skema
                if (preg_match('/Nomor\s*:?\s*(SKM\.[\w\.\/]+)/i', $cleanLine, $m) && !$nomorSkema) {
                    $nomorSkema = trim($m[1]);
                    continue;
                }

                if (preg_match('/(SKM\.[\w\.\/]+)/i', $cleanLine, $m) && !$nomorSkema) {
                    $nomorSkema = trim($m[1]);
                    continue;
                }

                // 3. Capture Unit Kompetensi
                if (preg_match('/Unit\s+Kompetensi\s+(\d+).*Kode\s+Unit\s*:?\s*(J\.[\d\.]+)/i', $cleanLine, $m)) {
                    $unitKe = (int)$m[1];
                    $kodeUnit = trim($m[2]);
                    $units[$unitKe] = [
                        'unit_ke' => $unitKe,
                        'kode_unit' => $kodeUnit,
                        'judul_unit' => null,
                        'elemen' => []
                    ];
                    $expectingJudulUnit = true;
                    $currentUnitIndex = $unitKe;
                    continue;
                }

                if (preg_match('/Unit\s+Kompetensi\s+(\d+)/i', $cleanLine, $m)) {
                    $unitKe = (int)$m[1];
                    if (!isset($units[$unitKe])) {
                        $units[$unitKe] = [
                            'unit_ke' => $unitKe,
                            'kode_unit' => null,
                            'judul_unit' => null,
                            'elemen' => []
                        ];
                    }
                    $currentUnitIndex = $unitKe;
                    continue;
                }

                // Capture Kode Unit
                if (preg_match('/Kode\s+Unit\s*:?\s*(J\.[\w\d\.\/\-]+)/i', $cleanLine, $m)) {
                    $kodeUnit = trim($m[1]);
                    if (isset($units[$unitKe])) {
                        $units[$unitKe]['kode_unit'] = $kodeUnit;
                    } else {
                        // Jika unit belum ada, buat unit baru
                        $unitKe = count($units) + 1;
                        $units[$unitKe] = [
                            'unit_ke' => $unitKe,
                            'kode_unit' => $kodeUnit,
                            'judul_unit' => null,
                            'elemen' => []
                        ];
                    }
                    $expectingJudulUnit = true;
                    $currentUnitIndex = $unitKe;
                    continue;
                }

                // 4. Capture Judul Unit - PERBAIKAN
                if (preg_match('/Judul\s+Unit/i', $cleanLine)) {
                    // Ada tulisan "Judul Unit" tapi bisa jadi baris selanjutnya isinya
                    if (preg_match('/Judul\s+Unit\s*:?\s*(.+)/i', $cleanLine, $m) && strlen(trim($m[1])) > 0) {
                        // Kasus: "Judul Unit : Menggunakan Struktur Data" (langsung 1 baris)
                        $judulUnit = trim($m[1]);
                        if ($currentUnitIndex !== null && isset($units[$currentUnitIndex])) {
                            $units[$currentUnitIndex]['judul_unit'] = $judulUnit;
                        }
                        $expectingJudulUnit = false;
                    } else {
                        // Kasus: "Judul Unit" lalu baris berikutnya berisi judul
                        $expectingJudulUnit = 'next_line';
                    }
                    continue;
                }

                // Jika baris berikutnya setelah "Judul Unit" adalah judulnya
                if ($expectingJudulUnit === 'next_line' && $currentUnitIndex !== null && isset($units[$currentUnitIndex])) {
                    if (!preg_match('/(Dapatkah|Elemen|Kriteria|Saya|Bukti)/i', $cleanLine) && strlen($cleanLine) > 3) {
                        $judulUnit = trim($cleanLine);
                        $units[$currentUnitIndex]['judul_unit'] = $judulUnit;
                        $expectingJudulUnit = false;
                        continue;
                    }
                }


                // 5. Capture Elemen - Reset KUK counter setiap elemen baru
                if (preg_match('/Elemen\s+(\d+)\s*:?\s*(.+)$/i', $cleanLine, $m)) {
                    $currentElemenIndex = (int)$m[1];
                    $currentKukCounter = 1; // Reset counter KUK untuk elemen baru
                    $elemenContent = trim($m[2]);
                    $expectingJudulUnit = false; // Pastikan flag dinonaktifkan

                    if (!isset($units[$unitKe])) {
                        $units[$unitKe] = [
                            'unit_ke' => $unitKe,
                            'kode_unit' => $kodeUnit,
                            'judul_unit' => $judulUnit,
                            'elemen' => []
                        ];
                    }

                    $patternPertanyaan = '/\b(Kriteria\s+Unjuk\s+Kerja[:]?)/i';

                    if (preg_match($patternPertanyaan, $elemenContent, $pertanyaanMatch, PREG_OFFSET_CAPTURE)) {
                        $pos = $pertanyaanMatch[0][1];
                        $elemenPernyataan = trim(substr($elemenContent, 0, $pos));
                        $subsText = trim(substr($elemenContent, $pos + strlen($pertanyaanMatch[0][0])));

                        $units[$unitKe]['elemen'][$currentElemenIndex] = [
                            'elemen_index' => $currentElemenIndex,
                            'nama_elemen' => $elemenPernyataan,
                            'kuk' => []
                        ];

                        preg_match_all('/([A-Z][^A-Z]+)/', $subsText, $subsMatches);
                        $kukList = !empty($subsMatches[0]) ? $subsMatches[0] : [$subsText];

                        foreach ($kukList as $kuk) {
                            $kuk = trim($kuk);
                            if (strlen($kuk) > 10 && !preg_match('/^(K|B|☐)$/i', $kuk)) {
                                $units[$unitKe]['elemen'][$currentElemenIndex]['kuk'][] = [
                                    'urutan' => $currentElemenIndex . '.' . $currentKukCounter++,
                                    'deskripsi_kuk' => $kuk
                                ];
                            }
                        }
                    } else {
                        $units[$unitKe]['elemen'][$currentElemenIndex] = [
                            'elemen_index' => $currentElemenIndex,
                            'nama_elemen' => $elemenContent,
                            'kuk' => []
                        ];
                    }
                    continue;
                }

                // 6. Capture KUK dengan format penomoran baru
                $kukMatched = false;
                
                // Gunakan XML numbering info jika ada
                if ($numberInfo && $currentElemenIndex !== null) {
                    $kukText = trim($cleanLine);
                    $kukText = preg_replace('/\s*(Kriteria|Unjuk|Kerja|Dapatkah|Saya|Bukti|relevan).*$/i', '', $kukText);
                    $kukText = trim($kukText);

                    if (strlen($kukText) > 10 && !preg_match('/^(K|B|☐)$/i', $kukText)) {
                        if (isset($units[$unitKe]['elemen'][$currentElemenIndex])) {
                            $units[$unitKe]['elemen'][$currentElemenIndex]['kuk'][] = [
                                'urutan' => $currentElemenIndex . '.' . $currentKukCounter++,
                                'deskripsi_kuk' => $kukText
                            ];
                            $kukMatched = true;
                        }
                    }
                }
                
                // Fallback ke regex pattern matching
                if (!$kukMatched && preg_match('/^(\d+)\.\s*(.+)/i', $cleanLine, $m) && $currentElemenIndex !== null) {
                    $kukText = trim($m[2]);
                    $kukText = preg_replace('/\s*(Kriteria|Unjuk|Kerja|Dapatkah|Saya|Bukti|relevan).*$/i', '', $kukText);
                    $kukText = trim($kukText);

                    if (strlen($kukText) > 10 && !preg_match('/^(K|B|☐)$/i', $kukText)) {
                        if (isset($units[$unitKe]['elemen'][$currentElemenIndex])) {
                            $units[$unitKe]['elemen'][$currentElemenIndex]['kuk'][] = [
                                'urutan' => $currentElemenIndex . '.' . $currentKukCounter++,
                                'deskripsi_kuk' => $kukText
                            ];
                        }
                    }
                }

                // Fallback untuk KUK tanpa penomoran
                if (!$kukMatched && $currentElemenIndex !== null && !empty(trim($cleanLine))) {
                    $kukText = trim($cleanLine);
                    if (strlen($kukText) > 10 && !preg_match('/^(K|B|☐|Elemen|Unit)/i', $kukText)) {
                        if (isset($units[$unitKe]['elemen'][$currentElemenIndex])) {
                            $units[$unitKe]['elemen'][$currentElemenIndex]['kuk'][] = [
                                'urutan' => $currentElemenIndex . '.' . $currentKukCounter++,
                                'deskripsi_kuk' => $kukText
                            ];
                        }
                    }
                }
            }

            // Validasi data sebelum menyimpan
            if (empty($judulSkema)) {
                throw new Exception('Judul Skema tidak ditemukan dalam dokumen');
            }
            
            if (empty($nomorSkema)) {
                throw new Exception('Nomor Skema tidak ditemukan dalam dokumen');
            }
            
            if (empty($units)) {
                throw new Exception('Tidak ada Unit Kompetensi yang ditemukan dalam dokumen');
            }

            // Berikan nilai default jika diperlukan
            foreach ($units as $unitIndex => $unit) {
                if (empty($unit['judul_unit'])) {
                    $units[$unitIndex]['judul_unit'] = 'Unit Kompetensi ' . $unit['unit_ke'];
                    \Log::warning("Judul Unit tidak ditemukan untuk Unit {$unit['unit_ke']}, menggunakan default: " . $units[$unitIndex]['judul_unit']);
                }
                if (empty($unit['kode_unit'])) {
                    $units[$unitIndex]['kode_unit'] = 'J.' . str_pad($unit['unit_ke'], 3, '0', STR_PAD_LEFT) . '.00.00.00';
                    \Log::warning("Kode Unit tidak ditemukan untuk Unit {$unit['unit_ke']}, menggunakan default: " . $units[$unitIndex]['kode_unit']);
                }
            }

            // Urutkan unit dan elemen
            ksort($units);
            foreach ($units as &$unit) {
                ksort($unit['elemen']);
                foreach ($unit['elemen'] as &$elemen) {
                    usort($elemen['kuk'], function($a, $b) {
                        return version_compare($a['urutan'], $b['urutan']);
                    });
                }
            }

            // Simpan ke database
            $schema = $this->saveToDatabase($judulSkema, $jurusanId, $nomorSkema, $units);

            return response()->json([
                'success' => true,
                'message' => 'Data successfully imported',
                'schema_id' => $schema->id,
                'judul_skema' => $schema->judul_skema,
                'nomor_skema' => $schema->nomor_skema,
                'total_units' => $schema->units()->count(),
                'total_elements' => $schema->units()->withCount('elements')->get()->sum('elements_count'),
                'total_kuk' => $schema->countTotalKuk(),
                'debug_info' => config('app.debug') ? [
                    'parsed_units' => count($units),
                    'found_schema_title' => !empty($judulSkema),
                    'found_schema_number' => !empty($nomorSkema),
                    'units_summary' => array_map(function($unit) {
                        return [
                            'unit_ke' => $unit['unit_ke'],
                            'has_kode_unit' => !empty($unit['kode_unit']),
                            'has_judul_unit' => !empty($unit['judul_unit']),
                            'kode_unit' => $unit['kode_unit'],
                            'judul_unit' => $unit['judul_unit'],
                            'elements_count' => count($unit['elemen'])
                        ];
                    }, $units)
                ] : null
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }


    private function parseDocxWithXml($filePath)
    {
        $zip = new \ZipArchive;
        $texts = [];
        $structuredData = [];
        
        if ($zip->open($filePath) === true) {
            $documentXml = $zip->getFromName('word/document.xml');
            $numberingXml = $zip->getFromName('word/numbering.xml');
            $zip->close();

            $doc = simplexml_load_string($documentXml);
            $doc->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            $counters = []; // Track counters per numId
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

                // Store text
                $texts[] = $text;

                // Store structured numbering info if available
                if ($numPr && !empty($text)) {
                    $numId = (string)$numPr[0]['w:val'];
                    $lvl = $level ? (int)$level[0]['w:val'] : 0;

                    if (!isset($counters[$numId])) {
                        $counters[$numId] = [];
                    }
                    if (!isset($counters[$numId][$lvl])) {
                        $counters[$numId][$lvl] = 1;
                    } else {
                        $counters[$numId][$lvl]++;
                    }

                    // Build number like 1.1
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

    private function extractUrutanFromNumber($number, $elemenIndex)
    {
        // Format urutan menjadi [elemen_index].[urutan], misal elemen 1 => 1.1, 1.2, 1.3
        $parts = explode('.', $number);
        $counter = (int)end($parts);
        return $elemenIndex . '.' . $counter;
    }

    private function saveToDatabase($judulSkema, $jurusanId, $nomorSkema, $units)
    {
        // Validate required data
        if (empty($judulSkema)) {
            throw new Exception('Judul Skema is required');
        }
        
        if (empty($nomorSkema)) {
            throw new Exception('Nomor Skema is required');
        }

        // Create or update schema
        $schema = Schema::updateOrCreate(
            [
                'jurusan_id' => $jurusanId,
                'nomor_skema' => $nomorSkema,
                'judul_skema' => $judulSkema
            ]
        );

        // Delete existing related data if exists
        $schema->units()->each(function($unit) {
            // Delete child elements and their KUKs first
            $unit->elements()->each(function($element) {
                $element->kriteriaUntukKerja()->delete();
            });
            $unit->elements()->delete();
            
            // Then delete the unit
            $unit->delete();
        });

        foreach ($units as $unitData) {
            // Ensure judul_unit is not null
            $judulUnit = $unitData['judul_unit'] ?? 'Unit Kompetensi ' . $unitData['unit_ke'];
            $kodeUnit = $unitData['kode_unit'] ?? 'J.000.00.00.00';
            
            $unit = $schema->units()->create([
                'unit_ke' => $unitData['unit_ke'],
                'kode_unit' => $kodeUnit,
                'judul_unit' => $judulUnit
            ]);

            foreach ($unitData['elemen'] as $elemenData) {
                $element = $unit->elements()->create([
                    'elemen_index' => $elemenData['elemen_index'],
                    'nama_elemen' => $elemenData['nama_elemen'] ?? 'Elemen ' . $elemenData['elemen_index']
                ]);

                foreach ($elemenData['kuk'] as $kukData) {
                    $element->kriteriaUntukKerja()->create([
                        'urutan' => $kukData['urutan'],
                        'deskripsi_kuk' => $kukData['deskripsi_kuk'] ?? 'Kriteria ' . $kukData['urutan']
                    ]);
                }
            }
        }

        return $schema;
    }

    public function show($id)
    {
        $schema = Schema::with(['units.elements.kriteriaUntukKerja'])->findOrFail($id);
        $jurusan = $schema->jurusan;
        
        return response()->json([
            'success' => true,
            'jurusan' => [
                'id' => $jurusan->id,
                'nama_jurusan' => $jurusan->nama_jurusan
            ],
            'judul_skema' => $schema->judul_skema,
            'nomor_skema' => $schema->nomor_skema,
            'data' => $schema->units->map(function($unit) {
                return [
                    'unit_ke' => $unit->unit_ke,
                    'kode_unit' => $unit->kode_unit,
                    'judul_unit' => $unit->judul_unit,
                    'elemen' => $unit->elements->mapWithKeys(function($element) {
                        return [
                            $element->elemen_index => [
                                'elemen_index' => $element->elemen_index,
                                'nama_elemen' => $element->nama_elemen,
                                'kuk' => $element->kriteriaUntukKerja->map(function($kuk) {
                                    return [
                                        'urutan' => $kuk->urutan,
                                        'deskripsi_kuk' => $kuk->deskripsi_kuk
                                    ];
                                })->sortBy('urutan')->values()->toArray()
                            ]
                        ];
                    })
                ];
            })->sortBy('unit_ke')->values()->toArray()
        ]);
    }

    public function schemaIndex() {
        $schemas = Schema::with(['jurusan', 'units.elements.kriteriaUntukKerja'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $schemas->map(function($schema) {
                return [
                    'id' => $schema->id,
                    'judul_skema' => $schema->judul_skema,
                    'nomor_skema' => $schema->nomor_skema,
                    'jurusan' => [
                        'id' => $schema->jurusan->id,
                        'nama_jurusan' => $schema->jurusan->nama_jurusan
                    ],
                    'total_units' => $schema->units()->count(),
                    'total_elements' => $schema->units()->withCount('elements')->get()->sum('elements_count'),
                    'total_kuk' => $schema->countTotalKuk(),
                    'tanggal_mulai' => $schema->tanggal_mulai,
                    'tanggal_selesai' => $schema->tanggal_selesai,
                    'created_at' => $schema->created_at,
                ];
            })
        ]);
    }

    public function destroy($id)
    {
        $schema = Schema::findOrFail($id);
        $schema->units()->each(function($unit) {
            // Delete child elements and their KUKs first
            $unit->elements()->each(function($element) {
                $element->kriteriaUntukKerja()->delete();
            });
            $unit->elements()->delete();
            
            // Then delete the unit
            $unit->delete();
        });
        $schema->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schema and related data deleted successfully'
        ]);
    }
}