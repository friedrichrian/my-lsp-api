<?php

namespace App\Helpers;

use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextBreak;

class WordParser
{
    /**
     * Extract plain text from a PhpWord element
     */
    public static function extractText($element): array
    {
        $texts = [];

        if ($element instanceof Text) {
            $text = trim($element->getText());
            if (!empty($text)) {
                $texts[] = $text;
            }

        } elseif ($element instanceof TextRun) {
            $parts = [];
            foreach ($element->getElements() as $child) {
                if ($child instanceof Text) {
                    $text = trim($child->getText());
                    if (!empty($text)) {
                        $parts[] = $text;
                    }
                }
            }
            if (!empty($parts)) {
                $texts[] = implode(' ', $parts);
            }

        } elseif ($element instanceof Table) {
            $texts = array_merge($texts, self::extractTableText($element));

        } elseif ($element instanceof TextBreak) {
            // Skip text breaks
        } else {
            // Handle other element types by trying to extract text recursively
            if (method_exists($element, 'getElements')) {
                foreach ($element->getElements() as $child) {
                    $texts = array_merge($texts, self::extractText($child));
                }
            }
        }

        return array_filter($texts, function($text) {
            return !empty(trim($text));
        });
    }

    /**
     * Extract text from table with special handling for APL.02 format
     */
    private static function extractTableText($table): array
    {
        $texts = [];
        
        foreach ($table->getRows() as $rowIndex => $row) {
            $cellTexts = [];
            $significantCells = [];
            
            foreach ($row->getCells() as $cellIndex => $cell) {
                $cellContent = [];
                
                foreach ($cell->getElements() as $element) {
                    $cellContent = array_merge($cellContent, self::extractText($element));
                }
                
                $cellText = trim(implode(' ', $cellContent));
                
                // Skip empty cells and cells with only symbols or single characters
                if (!empty($cellText) && 
                    !preg_match('/^[\|\*\s☐]+$/', $cellText) &&
                    strlen(trim(str_replace(['*', '|', '☐'], '', $cellText))) > 1) {
                    $significantCells[] = $cellText;
                }
                
                $cellTexts[] = $cellText;
            }
            
            // If we have significant content in this row
            if (!empty($significantCells)) {
                // For APL.02 format, combine cells intelligently
                $rowText = implode(' | ', $significantCells);
                
                // Clean up multiple spaces and separators
                $rowText = preg_replace('/\s*\|\s*\|\s*/', ' | ', $rowText);
                $rowText = preg_replace('/\s+/', ' ', $rowText);
                $rowText = trim($rowText);
                
                if (!empty($rowText)) {
                    $texts[] = $rowText;
                    
                    // Also add individual significant cells if they contain structured data
                    foreach ($significantCells as $cellText) {
                        if (self::isStructuredContent($cellText)) {
                            $texts[] = $cellText;
                        }
                    }
                }
            }
        }
        
        return $texts;
    }

    /**
     * Check if content appears to be structured (contains important keywords)
     */
    private static function isStructuredContent($text): bool
    {
        $keywords = [
            'Elemen \d+',
            'Kode Unit',
            'Judul Unit',
            'Unit Kompetensi',
            'Kriteria Unjuk Kerja',
            'SKM\.',
            'J\.\d+',
            '^\d+\.\s+\w+', // Numbered lists
            'Menggunakan|Menerapkan|Membuat|Melakukan|Mengimplementasikan',
            'Pemrogram Junior|Junior Coder'
        ];
        
        foreach ($keywords as $pattern) {
            if (preg_match('/' . $pattern . '/i', $text)) {
                return true;
            }
        }
        
        return false;
    }
}