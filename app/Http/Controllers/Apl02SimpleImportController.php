<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use App\Helpers\WordParser;
use App\Models\Apl02SimpleItem;
use Exception;

class Apl02SimpleImportController extends Controller
{
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:doc,docx'
            ]);

            $phpWord = IOFactory::load($request->file('file')->getPathName());
            $texts = [];

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $texts = array_merge($texts, WordParser::extractText($element));
                }
            }

            $judulSkema = null;
            $nomorSkema = null;
            $unitKe = 0;
            $kodeUnit = null;
            $judulUnit = null;
            $currentElemenIndex = null;
            $currentElemenText = null;
            $recordCount = 0;

            foreach ($texts as $line) {
                $line = trim(preg_replace('/\s+/', ' ', $line));
                if (empty($line)) continue;

                // Clean up the line from table separators
                $cleanLine = str_replace(['|', '**', '*'], '', $line);
                $cleanLine = trim($cleanLine);

                // 1. Capture Judul Skema - look for pattern with "Pemrogram Junior"
                if (preg_match('/Judul\s*:?\s*(.*(?:Pemrogram\s+Junior|Junior\s+Coder).*)/i', $cleanLine, $m) && !$judulSkema) {
                    $judulSkema = trim($m[1]);
                    continue;
                }

                // Alternative pattern for schema title
                if (preg_match('/(Pemrogram\s+Junior.*(?:Coder)?)/i', $cleanLine, $m) && !$judulSkema && !preg_match('/unit/i', $cleanLine)) {
                    $judulSkema = trim($m[1]);
                    continue;
                }

                // 2. Capture Nomor Skema - look for SKM pattern
                if (preg_match('/Nomor\s*:?\s*(SKM\.[\w\.\/]+)/i', $cleanLine, $m) && !$nomorSkema) {
                    $nomorSkema = trim($m[1]);
                    continue;
                }

                // Alternative pattern for nomor skema
                if (preg_match('/(SKM\.[\w\.\/]+)/i', $cleanLine, $m) && !$nomorSkema) {
                    $nomorSkema = trim($m[1]);
                    continue;
                }

                // 3. Capture Unit Kompetensi and Kode Unit
                if (preg_match('/Unit\s+Kompetensi\s+(\d+).*Kode\s+Unit\s*:?\s*(J\.[\d\.]+)/i', $cleanLine, $m)) {
                    $unitKe = (int)$m[1];
                    $kodeUnit = trim($m[2]);
                    continue;
                }

                // Separate patterns for unit detection
                if (preg_match('/Unit\s+Kompetensi\s+(\d+)/i', $cleanLine, $m)) {
                    $unitKe = (int)$m[1];
                    continue;
                }

                if (preg_match('/Kode\s+Unit\s*:?\s*(J\.[\d\.]+)/i', $cleanLine, $m)) {
                    $kodeUnit = trim($m[1]);
                    continue;
                }

                // 4. Capture Judul Unit
                if (preg_match('/Judul\s+Unit\s*:?\s*(.+)/i', $cleanLine, $m)) {
                    $judulUnit = trim($m[1]);
                    // Clean up common artifacts
                    $judulUnit = preg_replace('/\s*(Dapatkah|Saya).*$/i', '', $judulUnit);
                    $judulUnit = trim($judulUnit);
                    continue;
                }

                // Alternative pattern for unit title
                if (preg_match('/(Menggunakan\s+Struktur\s+Data|Menggunakan\s+Spesifikasi\s+Program|Menerapkan\s+Perintah|Menulis\s+Kode|Mengimplementasikan|Membuat\s+Dokumen|Melakukan\s+Debugging|Melaksanakan\s+Pengujian)/i', $cleanLine, $m) 
                    && !preg_match('/Elemen/i', $cleanLine) 
                    && !$judulUnit) {
                    
                    // Extract the full title
                    $judulUnit = trim($m[1]);
                    if (preg_match('/(' . preg_quote($m[1], '/') . '[^|]*)/i', $cleanLine, $fullMatch)) {
                        $judulUnit = trim($fullMatch[1]);
                    }
                    continue;
                }

                // 5. Capture Elemen - look for "Elemen X :" pattern
                if (preg_match('/Elemen\s+(\d+)\s*:?\s*(.+?)(?:\s*Kriteria|$)/i', $cleanLine, $m)) {
                    $currentElemenIndex = (int)$m[1];
                    $currentElemenText = trim($m[2]);
                    
                    // Clean up the element text
                    $currentElemenText = preg_replace('/\s*(Kriteria\s+Unjuk\s+Kerja|Dapatkah|Saya).*$/i', '', $currentElemenText);
                    $currentElemenText = trim($currentElemenText);

                    // Only save if we have minimum required data
                    if ($judulSkema || $nomorSkema || $kodeUnit) {
                        Apl02SimpleItem::create([
                            'judul_skema' => $judulSkema,
                            'nomor_skema' => $nomorSkema,
                            'unit_ke' => $unitKe,
                            'kode_unit' => $kodeUnit,
                            'judul_unit' => $judulUnit,
                            'elemen_index' => $currentElemenIndex,
                            'elemen_text' => $currentElemenText,
                            'sub_index' => null,
                            'sub_text' => null,
                        ]);
                        $recordCount++;
                    }
                    continue;
                }

                // 6. Capture Kriteria Unjuk Kerja (sub-elemen) - numbered list
                if (preg_match('/^(\d+)\.\s*(.+)/i', $cleanLine, $m) && $currentElemenIndex !== null) {
                    $subText = trim($m[2]);
                    
                    // Clean up common artifacts from table parsing
                    $subText = preg_replace('/\s*(Kriteria|Unjuk|Kerja|Dapatkah|Saya|Bukti|relevan).*$/i', '', $subText);
                    $subText = trim($subText);
                    
                    // Skip if the text is too short or seems like table header
                    if (strlen($subText) > 10 && !preg_match('/^(K|B|☐)$/i', $subText)) {
                        // Only save if we have minimum required data
                        if ($judulSkema || $nomorSkema || $kodeUnit) {
                            Apl02SimpleItem::create([
                                'judul_skema' => $judulSkema,
                                'nomor_skema' => $nomorSkema,
                                'unit_ke' => $unitKe,
                                'kode_unit' => $kodeUnit,
                                'judul_unit' => $judulUnit,
                                'elemen_index' => $currentElemenIndex,
                                'elemen_text' => $currentElemenText,
                                'sub_index' => $currentElemenIndex . '.' . $m[1],
                                'sub_text' => $subText,
                            ]);
                            $recordCount++;
                        }
                    }
                    continue;
                }

                // Additional patterns for capturing missed unit titles
                if (!$judulUnit && preg_match('/(Membuat\s+Dokumen\s+Kode\s+Program|Melakukan\s+Debugging|Melaksanakan\s+Pengujian\s+Unit\s+Program)/i', $cleanLine, $m)) {
                    $judulUnit = trim($m[1]);
                    continue;
                }
            }

            if ($recordCount === 0) {
                return response()->json([
                    'error' => true,
                    'message' => 'Tidak ada data yang berhasil diimport',
                    'debug' => [
                        'total_lines' => count($texts),
                        'sample_lines' => array_slice($texts, 0, 30),
                        'extracted_data' => [
                            'judul_skema' => $judulSkema,
                            'nomor_skema' => $nomorSkema,
                            'sample_units' => $unitKe,
                            'sample_kode_unit' => $kodeUnit,
                        ]
                    ]
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diimport',
                'total_records' => $recordCount,
                'summary' => [
                    'judul_skema' => $judulSkema,
                    'nomor_skema' => $nomorSkema,
                    'total_units' => $unitKe,
                ],
                'sample_data' => Apl02SimpleItem::orderBy('id', 'desc')->first()
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function show($id)
    {
        $item = Apl02SimpleItem::findOrFail($id);
        return response()->json($item);
    }

    public function index()
    {
        $items = Apl02SimpleItem::orderBy('unit_ke')
                                ->orderBy('elemen_index')
                                ->orderBy('sub_index')
                                ->get();
        
        return response()->json([
            'success' => true,
            'data' => $items,
            'total' => $items->count()
        ]);
    }

    public function clear()
    {
        Apl02SimpleItem::truncate();
        
        return response()->json([
            'success' => true,
            'message' => 'Semua data berhasil dihapus'
        ]);
    }
}