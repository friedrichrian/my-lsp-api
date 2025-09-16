<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormApl01;
use App\Models\Assesi;
use App\Models\Element;
use App\Models\BuktiDokumenAssesi;
use App\Models\FormApl02Submission;
use App\Models\FormApl02Attachments;
use App\Models\FormApl02SubmissionDetail;
use App\Models\FormAk01Submission;
use App\Models\FormAk01Attachment;
use App\Models\FormIa01Submission;
use App\Models\FormIa01SubmissionDetail;
use App\Models\FormIa01PenilaianLanjut;
use App\Models\Assesor;
use Illuminate\Validation\Rule;
use App\Models\Schema;
use App\Models\Jurusan;
use App\Models\FormApl01Submission;
use App\Models\AssesiSubmission;
use App\Models\FormApl01Attachments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Assesment;
use App\Models\Assesment_Asesi;

class AssesmentController extends Controller
{
    public function index()
    {
        $assessments = Assesment::with(['schema', 'admin', 'assesor'])->get();
        return response()->json([
            'success' => true,
            'message' => 'List of assessments',
            'data' => $assessments
        ]);
    }
    public function createAssesment(Request $request)
    {
        $validated = $request->validate([
            'skema_id' => 'required|exists:schemas,id',
            'admin_id' => 'required|exists:admin,id_admin',
            'assesor_id' => 'required|exists:assesor,id',
            'tanggal_assesment' => 'required|date',
            'status' => 'required|in:expired,active',
        ], [
            // Skema
            'skema_id.required' => 'Anda wajib memilih skema sebelum melanjutkan.',
            'skema_id.exists'   => 'Skema yang Anda pilih tidak tersedia dalam sistem.',

            // Admin
            'admin_id.required' => 'Data admin harus diisi.',
            'admin_id.exists'   => 'Admin dengan ID yang dimasukkan tidak ditemukan.',

            // Asesor
            'assesor_id.required' => 'Silakan pilih asesor yang akan melakukan asesmen.',
            'assesor_id.exists'   => 'Asesor yang dipilih tidak tersedia.',

            // Tanggal
            'tanggal_assesment.required' => 'Tanggal asesmen wajib diisi.',
            'tanggal_assesment.date'     => 'Format tanggal asesmen tidak valid (contoh: 2025-08-25).',

            // Status
            'status.required' => 'Status asesmen wajib dipilih.',
            'status.in'       => 'Status asesmen hanya boleh bernilai "expired" atau "active".',
        ]);

        DB::beginTransaction();
        try {
            $assessment = Assesment::create([
                'skema_id' => $validated['skema_id'],
                'admin_id' => $validated['admin_id'],
                'assesor_id' => $validated['assesor_id'],
                'tanggal_assesment' => $validated['tanggal_assesment'],
                'status' => $validated['status'],
                'tuk' => $request->input('tuk', null), // optional
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assessment berhasil dibuat',
                'data' => $assessment
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat assessment. Silakan coba lagi.',
                'error_detail' => $e->getMessage(), // untuk debug
            ], 500);
        }
    }

    public function updateAssesment(Request $request, $id)
    {
        $validated = $request->validate([
            'id' => 'required|exists:assesments,id',
            'admin_id' => 'sometimes|exists:admin,id_admin',
            'status' => 'sometimes|in:expired,active',
            'tuk' => 'sometimes|string|max:255',
            'skema_id' => 'sometimes|exists:schemas,id',
            'assesor_id' => 'sometimes|exists:assesor,id',
            'tanggal_assesment' => 'sometimes|date'
            ]);

        DB::beginTransaction();
        try {
            $assessment = Assesment::findOrFail($id);
            $assessment->update($validated);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Assessment updated successfully',
                'data' => $assessment
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assessment Update Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assessment',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function deleteAssesment($id)
    {
        DB::beginTransaction();
        try {
            $assessment = Assesment::findOrFail($id);
            $assessment->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Assessment deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assessment Deletion Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'assessment_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete assessment',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $assessment = Assesment::with(['skema', 'admin', 'assesor'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Assessment details',
                'data' => $assessment
            ]);
        } catch (\Exception $e) {
            Log::error('Assessment Show Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'assessment_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Assessment not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

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
            $formApl01 = FormApl01::create(array_merge($validated, [
                'user_id' => auth()->id()
            ]));

            foreach ($validated['attachments'] as $attachment) {
                // Generate a unique filename
                $file = $attachment['file'];
                $filename = uniqid().'_'.$file->getClientOriginalName();
                $path = 'formapl01/'.$formApl01->id.'/'.$filename;

                // Get file contents and encrypt
                $encryptedContents = encrypt(file_get_contents($file->getRealPath()));

                // Store using Storage facade for better consistency
                Storage::disk('private')->put($path, $encryptedContents);

                // Create attachment record
                FormApl01Attachments::create([
                    'form_apl01_id' => $formApl01->id,
                    'nama_dokumen' => $file->getClientOriginalName(),
                    'file_path' => $path, // Simpan path tanpa 'private/' karena sudah menggunakan disk 'private'
                    'description' => $attachment['description'] ?? null
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Form APL01 created successfully',
                'data' => $formApl01
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Form APL01 Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Form APL01',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function formApl02(Request $request)
    {
        $validated = $request->validate([
            'skema_id' => 'required|exists:schemas,id',
            'assesment_assesi_id' => 'required|exists:assesment_asesi,id',
            'submissions' => 'required|array',
            'submissions.*.unit_ke' => 'required|integer',
            'submissions.*.kode_unit' => 'required|string',
            'submissions.*.elemen' => 'required|array',
            'submissions.*.elemen.*.elemen_id' => 'required|exists:elements,id',
            'submissions.*.elemen.*.kompetensinitas' => 'required|in:k,bk',
            'submissions.*.elemen.*.bukti_yang_relevan' => 'required|array',
            'submissions.*.elemen.*.bukti_yang_relevan.*.bukti_description' => [
                'required',
                Rule::exists('bukti_dokumen_assesi', 'description')->where(function ($query) {
                    $query->where('assesi_id', auth()->user()->assesi->id);
                })
            ]
        ]);

        $assesi = auth()->user()->assesi;
        if (!$assesi) {
            return response()->json(['message' => 'Assesi not found'], 404);
        }

        DB::beginTransaction();
        try {
            // Create the main submission
            $mainSubmission = $assesi->apl02Submissions()->create([
                'skema_id' => $validated['skema_id'],
                'assesment_asesi_id' => $validated['assesment_assesi_id'],
                'submission_date' => now()
            ]);

            // Preload all relevant bukti dokumen for efficiency
            $buktiDescriptions = collect($validated['submissions'])
                ->pluck('elemen.*.bukti_yang_relevan.*.bukti_description')
                ->flatten();

            $buktiDokumenMap = BuktiDokumenAssesi::whereIn('description', $buktiDescriptions)
                ->where('assesi_id', $assesi->id)
                ->get()
                ->keyBy('description');

            // Process each submission
            foreach ($validated['submissions'] as $unit) {
                foreach ($unit['elemen'] as $elemen) {
                    // Create submission details
                    $submission = $mainSubmission->details()->create([
                        'unit_ke' => $unit['unit_ke'],
                        'kode_unit' => $unit['kode_unit'],
                        'elemen_id' => $elemen['elemen_id'],
                        'kompetensinitas' => $elemen['kompetensinitas']
                    ]);

                    // Attach relevant bukti dokumen
                    foreach ($elemen['bukti_yang_relevan'] as $bukti) {
                        $buktiDokumen = $buktiDokumenMap[$bukti['bukti_description']] ?? null;

                        if (!$buktiDokumen) {
                            throw new \Exception("Bukti dokumen not found: " . $bukti['bukti_description']);
                        }

                        $submission->attachments()->create([
                            'bukti_id' => $buktiDokumen->id
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Full APL02 submission successful',
                'submission_id' => $mainSubmission->id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('APL02 Submission Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit APL02',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function formAk01(Request $request)
    {
        $validated = $request->validate([
            'assesment_asesi_id' => 'required|exists:assesment_asesi,id',
            'skema_id' => 'required|exists:schemas,id',
            'attachments' => 'required|array',
            'attachments.*.file' => 'required|mimes:pdf|max:2048',
            'attachments.*.description' => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            // Create the main AK01 submission
            $ak01Submission = FormAk01Submission::Create([
                'assesment_asesi_id' => $validated['assesment_asesi_id'],
                'skema_id' => $validated['skema_id'],
                'submission_date' => now()
            ]);

            // Process each attachment
            foreach ($validated['attachments'] as $attachment) {
                // Generate a unique filename
                $file = $attachment['file'];
                $filename = uniqid().'_'.$file->getClientOriginalName();
                $path = 'formak01/'.$ak01Submission->id.'/'.$filename;

                // Get file contents and encrypt
                $encryptedContents = encrypt(file_get_contents($file->getRealPath()));

                // Store using Storage facade for better consistency
                Storage::disk('private')->put($path, $encryptedContents);

                // Create attachment record
                $ak01Submission->attachments()->create([
                    'file_path' => $path,
                    'description' => $attachment['description'] ?? null
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'AK01 submission created successfully',
                'data' => $ak01Submission
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AK01 Submission Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create AK01 submission',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function formIa01(Request $request){
        $validated = $request->validate([
            'skema_id' => 'required|exists:schemas,id',
            'assesment_asesi_id' => 'required|exists:assesment_asesi,id',
            'submissions' => 'required|array',
            'submissions.*.unit_ke' => 'required|integer',
            'submissions.*.kode_unit' => 'required|string',
            'submissions.*.elemen' => 'required|array',
            'submissions.*.elemen.*.elemen_id' => 'required|exists:elements,id',
            'submissions.*.elemen.*.skkni' => 'required|in:ya,tidak',
            'submissions.*.elemen.*.penilaian_lanjut' => 'required|array',
            'submissions.*.elemen.*.penilaian_lanjut.*.teks_penilaian' => 'required|string'
        ]);

        $assesor = auth()->user()->assesor;
        if (!$assesor) {
            return response()->json(['message' => 'Assesor not found'], 404);
        }

        

        DB::beginTransaction();
        try {
            $assesment_assesi = Assesment_Asesi::where('id', $validated['assesment_asesi_id'])->first();
            $assesi = Assesi::where('id', $assesment_assesi->assesi_id)->first();
            // Create the main submission
            $mainSubmission = FormIa01Submission::create([
                'assesment_asesi_id' => $validated['assesment_asesi_id'],
                'assesor_id' => $assesor->id,
                'assesi_id' => $assesi->id,
                'skema_id' => $validated['skema_id'],
                'submission_date' => now()
            ]);

            // Process each submission
            foreach ($validated['submissions'] as $unit) {
                foreach ($unit['elemen'] as $elemen) {
                    // Create submission details
                    $submissionDetail = FormIa01SubmissionDetail::create([
                        'submission_id' => $mainSubmission->id,
                        'unit_ke' => $unit['unit_ke'],
                        'kode_unit' => $unit['kode_unit'],
                        'elemen_id' => $elemen['elemen_id'],
                        'skkni' => $elemen['skkni']
                    ]);

                    // Create penilaian lanjut
                    foreach ($elemen['penilaian_lanjut'] as $penilaian) {
                        FormIa01PenilaianLanjut::create([
                            'submission_detail_id' => $submissionDetail->id,
                            'teks_penilaian' => $penilaian['teks_penilaian']
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Full IA01 submission successful',
                'submission_id' => $mainSubmission->id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IA01 Submission Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit IA01',
                'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function assesmentAssesiStatus(Request $request)
    {
        $validated = $request->validate([
            'assesment_id' => 'required|exists:assesments,id',
            'status' => 'required|in:mengerjakan,belum,selesai',
        ], [
            'assesment_id.required' => 'Anda wajib memilih asesmen sebelum melanjutkan.',
            'assesment_id.exists' => 'Asesmen yang Anda pilih tidak tersedia dalam sistem.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status hanya boleh bernilai "mengerjakan", "belum", atau "selesai".',
        ]);

        $Assesi = Assesi::firstWhere('user_id', auth()->user()->id);
        
        if(!$Assesi) {
            return response()->json([
                'success' => false,
                'message' => 'Assesi not found for the authenticated user'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $assesment_assesi = Assesment_Asesi::updateOrCreate(
                [
                    'assesment_id' => $validated['assesment_id'],
                    'assesi_id'    => $Assesi->id,
                ],
                [
                    'status'       => $validated['status'],
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status asesmen asesi berhasil diperbarui',
                'data'    => $assesment_assesi
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Status Assesment Asesi Error: ' . $e->getMessage(), [
                'user_id'      => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status asesmen asesi',
                'error'   => 'Terjadi kesalahan tak terduga. Silakan coba lagi nanti.'
            ], 500);
        }

    }

    public function getAssesmentAssesiStatus(Request $request)
    {
        $Assesi = Assesi::firstWhere('user_id', auth()->user()->id);
        
        if(!$Assesi) {
            return response()->json([
                'success' => false,
                'message' => 'Assesi not found for the authenticated user'
            ], 404);
        }

        try {
            
            $assesment_assesi = Assesment_Asesi::firstWhere('assesi_id', $Assesi->id);
            return response()->json([
                'success' => true,
                'message' => 'Status asesmen asesi berhasil diambil',
                'data' => $assesment_assesi
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get Status Assesment Asesi Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'assesi_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status asesmen asesi',
                'error' => 'Terjadi kesalahan tak terduga. Silakan coba lagi nanti.'
            ], 500);
        }
    }

    public function showApl02ByAssesi($assesi_id){
        $apl02 = FormApl02Submission::where('assesi_id', $assesi_id)->get();

        return response()->json([
            'status' => 'true',
            'message' => 'Apl 02 by assesi',
            'data' => $apl02
        ], 200);
    }

    public function showAk01ByAssesi($assesi_id){
        $ak01 = Assesment_Asesi::where('assesi_id', $assesi_id)->with('form_ak01_submissions.attachments')->get();
        return response()->json([
            'status' => 'true',
            'message' => 'Ak 01 by assesi',
            'data' => $ak01
        ], 200);
    }
}
