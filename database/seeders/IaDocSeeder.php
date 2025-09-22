<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IaDocSeeder extends Seeder
{
    /**
     * Seed IA docs by copying .doc/.docx files into storage/app/ia_docs/{skema_id}/
     *
     * Place your source files under database/seeders/ia_docs/
     * Use filename convention: {skema_id}__{FORM}.docx
     * Examples:
     * - 3__IA-01-CL.docx
     * - 3__IA-02-TPD.docx
     * - 3__IA-03.docx
     */
    public function run(): void
    {
        $sourceDir = database_path('seeders/ia_docs');
        if (!is_dir($sourceDir)) {
            // nothing to seed
            return;
        }

        $files = glob($sourceDir . '/*.{doc,docx}', GLOB_BRACE);
        if (!$files) return;

        foreach ($files as $filePath) {
            $basename = basename($filePath);
            // Expect pattern: {skema_id}__{FORM}.docx
            if (!preg_match('/^(\d+)__([A-Za-z0-9\-]+)\.(docx?|DOCX?)$/', $basename, $m)) {
                // skip unrecognized naming
                continue;
            }
            $skemaId = (int) $m[1];
            $form = strtoupper($m[2]);
            $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
            $targetExt = $ext === 'doc' ? 'doc' : 'docx';

            $dir = "ia_docs/{$skemaId}";
            Storage::disk('local')->makeDirectory($dir);
            $target = $dir . '/' . $form . '.' . $targetExt;

            $stream = fopen($filePath, 'r');
            Storage::disk('local')->put($target, $stream);
            if (is_resource($stream)) fclose($stream);
        }
    }
}
