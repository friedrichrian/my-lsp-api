<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
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

            $judulSkema = null;
            $nomorSkema = null;
            $units = [];
            $unitKe = null;
            $currentElemenIndex = null;
            $kukMode = false;
            $kukIndex = 1;

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (!($element instanceof \PhpOffice\PhpWord\Element\Table)) {
                        continue;
                    }

                    // Skip tabel tanda tangan/ttd
                    $skipTable = false;
                    foreach ($element->getRows() as $row) {
                        foreach ($row->getCells() as $cell) {
                            foreach ($cell->getElements() as $el) {
                                if (method_exists($el, 'getText')) {
                                    $text = strtolower(trim($el->getText()));
                                    if (strpos($text, 'tanda tangan') !== false || strpos($text, 'ttd') !== false) {
                                        $skipTable = true;
                                        break 3; // keluar dari cell, row, element loops
                                    }
                                }
                            }
                        }
                    }
                    if ($skipTable) continue;

                    foreach ($element->getRows() as $row) {
                        $cells = $row->getCells();
                        $cellTexts = [];

                        // Ambil semua paragraf dari setiap cell
                        foreach ($cells as $cell) {
                            $paragraphs = [];
                            foreach ($cell->getElements() as $el) {
                                if (method_exists($el, 'getText')) {
                                    $text = trim(preg_replace('/\s+/', ' ', $el->getText()));
                                    if ($text !== '') {
                                        $paragraphs[] = $text;
                                    }
                                }
                            }
                            $cellTexts[] = $paragraphs;
                        }

                        // Cek judul skema (scan semua cell)
                        if (!$judulSkema) {
                            foreach ($cellTexts as $i => $col) {
                                foreach ($col as $p) {
                                    if (stripos($p, 'Judul') !== false) {
                                        // Ambil teks di cell kanan (jika ada)
                                        $judulSkema = $cellTexts[$i + 1][0] ?? null;
                                        break 2;
                                    }
                                }
                            }
                        }

                        // Cek nomor skema
                        foreach ($cellTexts as $col) {
                            foreach ($col as $p) {
                                if (preg_match('/SKM\.[\w\.\/-]+/i', $p, $m)) {
                                    $nomorSkema = $m[0];
                                }
                            }
                        }

                        // Cek unit kompetensi
                        foreach ($cellTexts as $col) {
                            foreach ($col as $p) {
                                if (preg_match('/Unit\s+Kompetensi\s+(\d+)/i', $p, $m)) {
                                    $unitKe = (int)$m[1];
                                    if (!isset($units[$unitKe])) {
                                        $units[$unitKe] = [
                                            'unit_ke' => $unitKe,
                                            'kode_unit' => null,
                                            'judul_unit' => null,
                                            'elemen' => []
                                        ];
                                    }
                                    $currentElemenIndex = null;
                                    $kukMode = false;
                                    $kukIndex = 1;
                                }
                            }
                        }

                        // Ambil kode unit & judul unit dengan scan semua cell
                        foreach ($cellTexts as $i => $col) {
                            foreach ($col as $p) {
                                if (stripos($p, 'Kode Unit') !== false) {
                                    if (isset($cellTexts[$i + 1][0]) && preg_match('/J\.[\d\.]+/i', $cellTexts[$i + 1][0], $m)) {
                                        $units[$unitKe]['kode_unit'] = $m[0];
                                    }
                                }
                                if (stripos($p, 'Judul Unit') !== false) {
                                    if (isset($cellTexts[$i + 1][0])) {
                                        $units[$unitKe]['judul_unit'] = $cellTexts[$i + 1][0];
                                    }
                                }
                            }
                        }

                        // Loop elemen & KUK
                        foreach ($cellTexts as $col) {
                            foreach ($col as $p) {
                                // Deteksi Elemen
                                if (preg_match('/^Elemen\s+(\d+)\s*:\s*(.+)/i', $p, $m)) {
                                    $idx = (int)$m[1];
                                    $name = $m[2];
                                    $units[$unitKe]['elemen'][$idx] = [
                                        'elemen_index' => $idx,
                                        'nama_elemen' => $name,
                                        'kuk' => []
                                    ];
                                    $currentElemenIndex = $idx;
                                    $kukMode = false;
                                    $kukIndex = 1;
                                    continue;
                                }

                                // Deteksi awal KUK
                                if (stripos($p, 'Kriteria Unjuk Kerja') !== false) {
                                    $kukMode = true;
                                    $kukIndex = 1;
                                    continue;
                                }

                                // Masukkan KUK kalau mode aktif
                                if ($kukMode && $currentElemenIndex !== null && $p !== '') {
                                    if (preg_match('/^Elemen\s+\d+/i', $p)) {
                                        $kukMode = false;
                                        continue;
                                    }
                                    $clean = preg_replace('/^\s*\d+(\.\d+)*\s*/', '', $p);
                                    if ($clean !== '') {
                                        $units[$unitKe]['elemen'][$currentElemenIndex]['kuk'][] = [
                                            'urutan' => $kukIndex,
                                            'deskripsi_kuk' => $clean
                                        ];
                                        $kukIndex++;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Rapikan output (urutkan elemen dan kuk)
            foreach ($units as &$unit) {
                ksort($unit['elemen']);
                foreach ($unit['elemen'] as &$elemen) {
                    if (!empty($elemen['kuk'])) {
                        usort($elemen['kuk'], fn($a, $b) => $a['urutan'] <=> $b['urutan']);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'judul_skema' => $judulSkema,
                'nomor_skema' => $nomorSkema,
                'data' => array_values($units)
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}
