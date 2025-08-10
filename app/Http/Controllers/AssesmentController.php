<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormApl01;
use App\Models\FormApl01Attachments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssesmentController extends Controller
{
    public function formApl01(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_ktp' => 'required|string|unique:form_apl01,no_ktp',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'kebangsaan' => 'required|string|max:100',
            'alamat_rumah' => 'required|string|max:255',
            'kode_pos' => 'required|string|max:10',
            'no_telepon_rumah' => 'required|string|max:15',
            'no_telepon_kantor' => 'required|string|max:15',
            'no_telepon' => 'required|string|max:15',
            'email' => 'required|email|unique:form_apl01,email',
            'kualifikasi_pendidikan' => 'required|string|max:255',
            'nama_institusi' => 'required|string|max:255',
            'jabatan' => 'required|string|max:100',
            'alamat_kantor' => 'required|string|max:255',
            'kode_pos_kantor' => 'required|string|max:10',
            'fax_kantor' => 'required|string|max:15',
            'email_kantor' => 'required|email|max:255',
            'status' => 'required|in:pending,accepted,rejected',
            'attachments' => 'required|array',
            'attachments.*' => 'required|mimes:pdf|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $formApl01 = FormApl01::create([
                'user_id' => auth()->id(),
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_ktp' => $validated['no_ktp'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'kebangsaan' => $validated['kebangsaan'],
                'alamat_rumah' => $validated['alamat_rumah'],
                'kode_pos' => $validated['kode_pos'],
                'no_telepon_rumah' => $validated['no_telepon_rumah'],
                'no_telepon_kantor' => $validated['no_telepon_kantor'],
                'no_telepon' => $validated['no_telepon'],
                'email' => $validated['email'],
                'kualifikasi_pendidikan' => $validated['kualifikasi_pendidikan'],
                'nama_institusi' => $validated['nama_institusi'],
                'jabatan' => $validated['jabatan'],
                'alamat_kantor' => $validated['alamat_kantor'],
                'kode_pos_kantor' => $validated['kode_pos_kantor'],
                'fax_kantor' => $validated['fax_kantor'],
                'email_kantor' => $validated['email_kantor'],
                'status' => $validated['status']
            ]);

            foreach ($validated['attachments'] as $attachment) {
                // Generate a unique filename
                $filename = uniqid().'_'.$attachment->getClientOriginalName();
                $path = 'formapl01/'.$formApl01->id.'/'.$filename;

                // Get file contents and encrypt
                $encryptedContents = encrypt(file_get_contents($attachment->getRealPath()));

                // Store using file_put_contents for encrypted data
                $fullPath = Storage::path($path);
                Storage::makeDirectory(dirname($path));
                file_put_contents($fullPath, $encryptedContents);

                // Create attachment record
                FormApl01Attachments::create([
                    'form_apl01_id' => $formApl01->id,
                    'nama_dokumen' => $attachment->getClientOriginalName(),
                    'file_path' => 'private/'.$path
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create Form APL01',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Form APL01 created successfully'
        ], 201);
    }
}
