<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormApl01;
use App\Models\FormApl01Attachments;
use App\Models\BuktiDokumenAssesi;
use App\Models\Assesi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApprovementController extends Controller
{
    public function viewAttachment($id)
    {
        $attachment = FormApl01Attachments::findOrFail($id);

        $path = storage_path('app/' . $attachment->file_path);
        $contents = decrypt(file_get_contents($path));

        return response($contents, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$attachment->nama_dokumen.'"');
    }

    public function showFormApl01($id)
    {
        $formApl01 = FormApl01::with('user', 'attachments')
            ->where('id', $id)
            ->first();

        if (!$formApl01) {
            return response()->json(['message' => 'Form APL01 not found'], 404);
        }

        // tambahkan URL view untuk tiap attachment
        $formApl01->attachments->transform(function ($attachment) {
            $attachment->view_url = route('form-apl01.attachment.view', $attachment->id);
            return $attachment;
        });

        return response()->json($formApl01);
    }


    public function approveFormApl01(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $formApl01 = FormApl01::with('user', 'attachments')
            ->where('id', $id)
            ->first();

        DB::beginTransaction();
        try {
            $formApl01->status = $validated['status'];
            $formApl01->save();

            if($validated['status'] === 'accepted') {
                Assesi::create([
                    'user_id' => $formApl01->user_id,
                    'jurusan_id' => $formApl01->user->jurusan_id,
                    'nama_lengkap' => $formApl01->nama_lengkap,
                    'no_ktp' => $formApl01->no_ktp,
                    'tempat_lahir' => $formApl01->tempat_lahir,
                    'tanggal_lahir' => $formApl01->tanggal_lahir,
                    'jenis_kelamin' => $formApl01->jenis_kelamin,
                    'alamat' => $formApl01->alamat_rumah,
                    'kode_pos' => $formApl01->kode_pos,
                    'no_telepon' => $formApl01->no_telepon,
                    'kualifikasi_pendidikan' => $formApl01->kualifikasi_pendidikan,
                    'form_apl01_id' => $formApl01->user->id,
                    'status' => 'approved'
                ]);

                foreach ($formApl01->attachments as $attachment) {
                    $buktiDokumen = new BuktiDokumenAssesi();
                    $buktiDokumen->assesi_id = Assesi::where('user_id', $formApl01->user_id)->first()->id;
                    $buktiDokumen->nama_dokumen = $attachment->nama_dokumen;
                    $buktiDokumen->file_path = $attachment->file_path;
                    $buktiDokumen->description = $attachment->description;
                    $buktiDokumen->save();
                }
            }

            DB::commit();
            return response()->json(['message' => 'Form APL01 '. $formApl01->status .' successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error approving Form APL01', 'error' => $e->getMessage()], 500);
        }
    }
}
