<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormApl01;
use App\Models\Assesi;
use App\Models\Element;
use App\Models\BuktiDokumenAssesi;
use App\Models\FormApl02Submission;
use App\Models\FormApl02Attachments;
use App\Models\Assesor;
use App\Models\FormApl02SubmissionDetails;
use Illuminate\Validation\Rule;
use App\Models\Skema;
use App\Models\Jurusan;
use App\Models\FormApl01Submission;
use App\Models\AssesiSubmission;
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
            'no_telepon_kantor' => 'sometimes|string|max:15',
            'no_telepon' => 'required|string|max:15',
            'email' => 'required|email|unique:form_apl01,email',
            'kualifikasi_pendidikan' => 'required|string|max:255',
            'nama_institusi' => 'required|string|max:255',
            'jabatan' => 'required|string|max:100',
            'alamat_kantor' => 'sometimes|string|max:255',
            'kode_pos_kantor' => 'sometimes|string|max:10',
            'fax_kantor' => 'sometimes|string|max:15',
            'email_kantor' => 'sometimes|email|max:255',
            'status' => 'required|in:pending,accepted,rejected',
            'attachments' => 'required|array',
            'attachments.*.file' => 'required|mimes:pdf|max:2048',
            'attachments.*.description' => 'required|string|max:255'
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
                $file = $attachment['file'];
                $filename = uniqid().'_'.$file->getClientOriginalName();
                $path = 'formapl01/'.$formApl01->id.'/'.$filename;

                // Get file contents and encrypt
                $encryptedContents = encrypt(file_get_contents($file->getRealPath()));

                // Store using file_put_contents for encrypted data
                $fullPath = Storage::path($path);
                Storage::makeDirectory(dirname($path));
                file_put_contents($fullPath, $encryptedContents);

                // Create attachment record
                FormApl01Attachments::create([
                    'form_apl01_id' => $formApl01->id,
                    'nama_dokumen' => $file->getClientOriginalName(),
                    'file_path' => 'private/'.$path,
                    'description' => $attachment['description'] ?? null
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

    public function formApl02(Request $request) {
        $validated = $request->validate([
            'skema_id' => 'required|exists:schemas,id',
            'submissions' => 'required|array',
            'submissions.*.unit_ke' => 'required|integer',
            'submissions.*.kode_unit' => 'required|string',
            'submissions.*.elemen' => 'required|array',
            'submissions.*.elemen.*.elemen_id' => 'required|exists:elements,id',
            'submissions.*.elemen.*.kompetensinitas' => 'required|in:k,bk',
            'submissions.*.elemen.*.bukti_yang_relevan' => 'required|array',
            'submissions.*.elemen.*.bukti_yang_relevan.*.bukti_description' => [
                'required',
                Rule::exists('bukti_dokumen_assesi', 'description')->where('assesi_id', auth()->user()->assesi->id)
            ]
        ]);

        $assesi = auth()->user()->assesi;
        if (!$assesi) {
            return response()->json(['message' => 'Assesi not found'], 404);
        }

        DB::beginTransaction();
        try {
            $mainSubmission = $assesi->apl02Submissions()->create([
                'skema_id' => $validated['skema_id'],
                'submission_date' => now()
            ]);

            foreach ($validated['submissions'] as $unit) {
                foreach ($unit['elemen'] as $elemen) {
                    $submission = $mainSubmission->details()->create([
                        'unit_ke' => $unit['unit_ke'],
                        'kode_unit' => $unit['kode_unit'],
                        'elemen_id' => $elemen['elemen_id'],
                        'kompetensinitas' => $elemen['kompetensinitas']
                    ]);

                    foreach ($elemen['bukti_yang_relevan'] as $bukti) {
                        $buktiDokumen = BuktiDokumenAssesi::where('description', $bukti['bukti_description'])
                            ->where('assesi_id', $assesi->id)
                            ->firstOrFail();

                        $submission->attachments()->create([
                            'bukti_id' => $buktiDokumen->id
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Full APL02 submission successful',
                'submission_id' => $mainSubmission->id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to submit APL02',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function debug(Request $request){
        $user = auth()->user()->assesi;
        $assesi = Assesi::where('user_id', $user->id)
            ->get();
        return response()->json($user);
    }
}
