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

            $phpWord = IOFactory::load($request->file('file')->getPathName());
            $texts = [];

            // Extract all text from document
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $texts = array_merge($texts, WordParser::extractText($element));
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

            foreach ($texts as $line) {
                $line = trim(preg_replace('/\s+/', ' ', $line));
                if (empty($line)) continue;

                $cleanLine = str_replace(['|', '**', '*'], '', $line);
                $cleanLine = trim($cleanLine);

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

                // 3. Capture Unit Kompetensi dan Kode Unit
                if (preg_match('/Unit\s+Kompetensi\s+(\d+).*Kode\s+Unit\s*:?\s*(J\.[\d\.]+)/i', $cleanLine, $m)) {
                    $unitKe = (int)$m[1];
                    $kodeUnit = trim($m[2]);
                    $units[$unitKe] = [
                        'unit_ke' => $unitKe,
                        'kode_unit' => $kodeUnit,
                        'judul_unit' => null,
                        'elemen' => []
                    ];
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
                    continue;
                }

                if (preg_match('/Kode\s+Unit\s*:?\s*(J\.[\d\.]+)/i', $cleanLine, $m)) {
                    $kodeUnit = trim($m[1]);
                    if (isset($units[$unitKe])) {
                        $units[$unitKe]['kode_unit'] = $kodeUnit;
                    }
                    continue;
                }

                // 4. Capture Judul Unit
                if (preg_match('/Judul\s+Unit\s*:?\s*(.+)/i', $cleanLine, $m)) {
                    $judulUnit = trim($m[1]);
                    $judulUnit = preg_replace('/\s*(Dapatkah|Saya).*$/i', '', $judulUnit);
                    $judulUnit = trim($judulUnit);
                    if (isset($units[$unitKe])) {
                        $units[$unitKe]['judul_unit'] = $judulUnit;
                    }
                    continue;
                }

                // 5. Capture Elemen
                if (preg_match('/Elemen\s+(\d+)\s*:?\s*(.+)$/i', $cleanLine, $m)) {
                    $currentElemenIndex = (int)$m[1];
                    $elemenContent = trim($m[2]);

                    // Initialize unit if not exists
                    if (!isset($units[$unitKe])) {
                        $units[$unitKe] = [
                            'unit_ke' => $unitKe,
                            'kode_unit' => $kodeUnit,
                            'judul_unit' => $judulUnit,
                            'elemen' => []
                        ];
                    }

                    // Split element content
                    $patternPertanyaan = '/\b(Kriteria\s+Unjuk\s+Kerja[:]?)/i';

                    if (preg_match($patternPertanyaan, $elemenContent, $pertanyaanMatch, PREG_OFFSET_CAPTURE)) {
                        $pos = $pertanyaanMatch[0][1];
                        $elemenPernyataan = trim(substr($elemenContent, 0, $pos));
                        $subsText = trim(substr($elemenContent, $pos + strlen($pertanyaanMatch[0][0])));

                        // Initialize element
                        $units[$unitKe]['elemen'][$currentElemenIndex] = [
                            'elemen_index' => $currentElemenIndex,
                            'nama_elemen' => $elemenPernyataan,
                            'kuk' => []
                        ];

                        // Extract KUK items
                        preg_match_all('/([A-Z][^A-Z]+)/', $subsText, $subsMatches);
                        $kukList = !empty($subsMatches[0]) ? $subsMatches[0] : [$subsText];

                        foreach ($kukList as $index => $kuk) {
                            $kuk = trim($kuk);
                            if (strlen($kuk) > 10 && !preg_match('/^(K|B|☐)$/i', $kuk)) {
                                $units[$unitKe]['elemen'][$currentElemenIndex]['kuk'][] = [
                                    'urutan' => $index + 1,
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

                // 6. Capture KUK (numbered items)
                if (preg_match('/^(\d+)\.\s*(.+)/i', $cleanLine, $m) && $currentElemenIndex !== null) {
                    $kukText = trim($m[2]);
                    $kukText = preg_replace('/\s*(Kriteria|Unjuk|Kerja|Dapatkah|Saya|Bukti|relevan).*$/i', '', $kukText);
                    $kukText = trim($kukText);

                    if (strlen($kukText) > 10 && !preg_match('/^(K|B|☐)$/i', $kukText)) {
                        if (isset($units[$unitKe]['elemen'][$currentElemenIndex])) {
                            $units[$unitKe]['elemen'][$currentElemenIndex]['kuk'][] = [
                                'urutan' => (int)$m[1],
                                'deskripsi_kuk' => $kukText
                            ];
                        }
                    }
                    continue;
                }
            }

            // Sort units and elements
            ksort($units);
            foreach ($units as &$unit) {
                ksort($unit['elemen']);
                foreach ($unit['elemen'] as &$elemen) {
                    usort($elemen['kuk'], fn($a, $b) => $a['urutan'] <=> $b['urutan']);
                }
            }

            // Save to database
            $schema = $this->saveToDatabase($judulSkema, $jurusanId, $nomorSkema, $units);

            return response()->json([
                'success' => true,
                'message' => 'Data successfully imported',
                'schema_id' => $schema->id,
                'judul_skema' => $schema->judul_skema,
                'nomor_skema' => $schema->nomor_skema,
                'total_units' => $schema->units()->count(),
                'total_elements' => $schema->units()->withCount('elements')->get()->sum('elements_count'),
                'total_kuk' => $schema->countTotalKuk()
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    private function saveToDatabase($judulSkema, $jurusanId, $nomorSkema, $units)
    {
        // Create or update schema
        $schema = Schema::updateOrCreate(
            [
                'jurusan_id' => $jurusanId,
                'nomor_skema' => $nomorSkema,
                'judul_skema' => $judulSkema
            ]
        );

        // Delete existing related data if exists
        $schema->units()->delete();

        foreach ($units as $unitData) {
            $unit = $schema->units()->create([
                'unit_ke' => $unitData['unit_ke'],
                'kode_unit' => $unitData['kode_unit'],
                'judul_unit' => $unitData['judul_unit']
                // No jurusan_id here
            ]);

            foreach ($unitData['elemen'] as $elemenData) {
                $element = $unit->elements()->create([
                    'elemen_index' => $elemenData['elemen_index'],
                    'nama_elemen' => $elemenData['nama_elemen']
                    // No jurusan_id here
                ]);

                foreach ($elemenData['kuk'] as $kukData) {
                    $element->kriteriaUntukKerja()->create([
                        'urutan' => $kukData['urutan'],
                        'deskripsi_kuk' => $kukData['deskripsi_kuk']
                        // No jurusan_id here
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
}