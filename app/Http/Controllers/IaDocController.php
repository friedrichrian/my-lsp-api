<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IaDocController extends Controller
{
    // Serve IA document file for a given form code and skema_id
    // Example path: storage/app/ia_docs/{skema_id}/IA-01.docx
    public function download(Request $request, string $form, int $skema_id)
    {
        $form = strtoupper($form);
        // Normalize form codes like IA-01-CL, IA-01, IA01
        $candidates = [
            $form . '.docx',
            str_replace('-', '', $form) . '.docx',
        ];

        $baseDir = "ia_docs/{$skema_id}";
        $path = null;
        foreach ($candidates as $name) {
            $candidate = $baseDir . '/' . $name;
            if (Storage::disk('local')->exists($candidate)) {
                $path = $candidate;
                break;
            }
        }

        if (!$path) {
            return response()->json([
                'status' => 'false',
                'message' => 'Dokumen IA tidak ditemukan untuk skema ini',
            ], 404);
        }

        $downloadName = $form . "-SKEMA-{$skema_id}.docx";
        $absolutePath = Storage::disk('local')->path($path);

        return response()->download($absolutePath, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    // Upload IA .docx for a given form and skema_id (admin use)
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'skema_id' => 'required|integer',
            'form' => 'required|string', // e.g., IA-01-CL, IA-02-TPD
            'file' => 'required|file|mimes:doc,docx|max:20480', // up to ~20MB
        ]);

        $form = strtoupper($validated['form']);
        $skemaId = (int) $validated['skema_id'];
        $filename = $form . '.docx';

        $dir = "ia_docs/{$skemaId}";
        Storage::disk('local')->makeDirectory($dir);
        $path = $dir . '/' . $filename;

        // Store file, overwrite if exists
        $stream = fopen($request->file('file')->getRealPath(), 'r');
        Storage::disk('local')->put($path, $stream);
        if (is_resource($stream)) fclose($stream);

        return response()->json([
            'status' => 'true',
            'message' => 'IA document uploaded',
            'data' => [
                'skema_id' => $skemaId,
                'form' => $form,
                'path' => $path,
            ],
        ], 201);
    }

    // List IA docs available for a skema_id
    public function listBySkema(Request $request, int $skema_id)
    {
        $dir = "ia_docs/{$skema_id}";
        if (!Storage::disk('local')->exists($dir)) {
            return response()->json([
                'status' => 'true',
                'message' => 'No IA docs found',
                'data' => [],
            ], 200);
        }
        $files = Storage::disk('local')->files($dir);
        $docs = array_values(array_filter($files, function ($f) {
            return preg_match('/\.docx?$/i', $f);
        }));

        // Map to form codes
        $items = array_map(function ($path) use ($skema_id) {
            $basename = basename($path);
            $form = preg_replace('/\.docx?$/i', '', $basename);
            return [
                'form' => strtoupper($form),
                'skema_id' => (int) $skema_id,
                'path' => $path,
            ];
        }, $docs);

        return response()->json([
            'status' => 'true',
            'message' => 'IA docs listed',
            'data' => $items,
        ], 200);
    }
}
