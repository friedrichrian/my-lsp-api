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
use App\Models\Assesor;
use App\Models\Ak02Submission;
use App\Models\Ak02SubmissionDetail;
use App\Models\Ak02DetailBukti;
use App\Models\Ak03Submission;
use App\Models\Ak03SubmissionDetail;
use App\Models\Komponen;
use App\Models\Ak05Submission;
use App\Models\Ak04Submission;
use App\Models\Ia02Submission;
use App\Models\Ia06ASubmission;
use App\Models\Ia03Submission;
use App\Models\Ia03SubmissionDetail;
use Illuminate\Validation\Rule;
use App\Models\Schema;
use App\Models\Jurusan;
use App\Models\FormApl01Submission;
use App\Models\AssesiSubmission;
use App\Models\FormApl01SertificationData;
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
        $assessments = Assesment::with(['schema.jurusan', 'admin', 'assesor'])->get();
        return response()->json([
            'success' => true,
            'message' => 'List of assessments',
            'data' => $assessments
        ]);
    }

    // IA-03 store answers (ya/tidak + response text) per question
    public function formIa03(Request $request)
    {
        $validated = $request->validate([
            'assesment_asesi_id' => 'required|exists:assesment_asesi,id',
            'skema_id' => 'nullable|exists:schemas,id',
            'questions' => 'required|array',
            'questions.*.question_id' => 'required|exists:questions,id',
            'questions.*.selected_option' => 'required|in:ya,tidak',
            'questions.*.response_text' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $submission = Ia03Submission::create([
                'assesment_asesi_id' => $validated['assesment_asesi_id'],
                'skema_id' => $validated['skema_id'] ?? null,
                'submission_date' => now(),
            ]);

            foreach ($validated['questions'] as $q) {
                Ia03SubmissionDetail::create([
                    'submission_id' => $submission->id,
                    'question_id' => $q['question_id'],
                    'selected_option' => $q['selected_option'],
                    'response_text' => $q['response_text'],
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'IA-03 submission berhasil disimpan',
                'submission_id' => $submission->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IA-03 Submission Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit IA-03',
                'error' => config('app.debug') ? $e->getMessage() : 'Unexpected error',
            ], 500);
        }
    }

    public function getIa03ByAssesi($assesi_id)
    {
        try {
            $subs = Ia03Submission::with(['details'])
                ->whereHas('assesmentAsesi', function ($q) use ($assesi_id) {
                    $q->where('assesi_id', $assesi_id);
                })
                ->orderByDesc('id')
                ->get();

            if ($subs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IA-03 submissions not found for the given assesi ID.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'IA-03 submissions retrieved successfully.',
                'data' => $subs,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get IA-03 Submission Error: ' . $e->getMessage(), [
                'assesi_id' => $assesi_id,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve IA-03 submissions.',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    // IA-06.A simple submission store
    public function formIa06a(Request $request)
    {
        $validated = $request->validate([
            'assesment_asesi_id' => 'required|exists:assesment_asesi,id',
            'skema_id' => 'nullable|exists:schemas,id',
            'catatan' => 'nullable|string',
            'ttd_asesi' => 'nullable|in:belum,sudah',
            'ttd_asesor' => 'nullable|in:belum,sudah',
            'extra' => 'nullable|array',
        ]);

        try {
            $submission = Ia06ASubmission::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'IA-06.A submission berhasil disimpan',
                'submission_id' => $submission->id,
            ], 201);
        } catch (\Exception $e) {
            Log::error('IA-06.A Submission Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit IA-06.A',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getIa06aByAssesi($assesi_id)
    {
        try {
            $submissions = Ia06ASubmission::whereHas('assesmentAsesi', function ($q) use ($assesi_id) {
                $q->where('assesi_id', $assesi_id);
            })->get();

            if ($submissions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IA-06.A submissions not found for the given assesi ID.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'IA-06.A submissions retrieved successfully.',
                'data' => $submissions,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get IA-06.A Submission Error: ' . $e->getMessage(), [
                'assesi_id' => $assesi_id,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve IA-06.A submissions.',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    // IA-02 (FR.IA.02 TPD) simple submission store
    public function formIa02(Request $request)
    {
        $validated = $request->validate([
            'assesment_asesi_id' => 'required|exists:assesment_asesi,id',
            'skema_id' => 'nullable|exists:schemas,id',
            'skema_sertifikasi' => 'nullable|string',
            'judul_unit' => 'nullable|string',
            'kode_unit' => 'nullable|string',
            'tuk' => 'nullable|string',
            'nama_asesor' => 'nullable|string',
            'nama_asesi' => 'nullable|string',
            'tanggal_asesmen' => 'nullable|date',
            'extra' => 'nullable|array',
        ]);

        try {
            $submission = Ia02Submission::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'IA-02 submission berhasil disimpan',
                'submission_id' => $submission->id,
            ], 201);
        } catch (\Exception $e) {
            Log::error('IA-02 Submission Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit IA-02',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get IA-02 submissions by asesi id via assesment_asesi relation
    public function getIa02ByAssesi($assesi_id)
    {
        try {
            $submissions = Ia02Submission::whereHas('assesmentAsesi', function ($q) use ($assesi_id) {
                $q->where('assesi_id', $assesi_id);
            })->get();

            if ($submissions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IA-02 submissions not found for the given assesi ID.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'IA-02 submissions retrieved successfully.',
                'data' => $submissions,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get IA-02 Submission Error: ' . $e->getMessage(), [
                'assesi_id' => $assesi_id,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve IA-02 submissions.',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function formAk04(Request $request)
    {
        $validated = $request->validate([
            'assesment_asesi_id' => 'required|exists:assesment_asesi,id',
            'nama_asesor' => 'nullable|string',
            'nama_asesi' => 'nullable|string',
            'tanggal_asesmen' => 'nullable|date',
            'skema_sertifikasi' => 'nullable|string',
            'no_skema_sertifikasi' => 'nullable|string',
            'alasan_banding' => 'nullable|string',
            'tanggal_approve' => 'nullable|date',
            'answers' => 'nullable|array',
        ]);

        try {
            $submission = Ak04Submission::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'AK04 submission berhasil disimpan',
                'submission_id' => $submission->id,
            ], 201);
        } catch (\Exception $e) {
            Log::error('AK04 Submission Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit AK04',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAk04ByAssesi($assesi_id)
    {
        try {
            $submissions = Ak04Submission::whereHas('assesmentAsesi', function ($q) use ($assesi_id) {
                $q->where('assesi_id', $assesi_id);
            })->get();

            if ($submissions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AK04 submissions not found for the given assesi ID.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'AK04 submissions retrieved successfully.',
                'data' => $submissions,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get AK04 Submission Error: ' . $e->getMessage(), [
                'assesi_id' => $assesi_id,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve AK04 submissions.',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }
    public function createAssesment(Request $request)
    {
        $validated = $request->validate([
            'skema_id' => 'required|exists:schemas,id',
            'admin_id' => 'required|exists:admin,id_admin',
            'assesor_id' => 'required|exists:assesor,id',
            'tanggal_assesment' => 'required|date',
            'status' => 'required|in:expired,active',
            'tuk' => 'sometimes|string|max:255',
            'tanggal_mulai' => 'sometimes|date',
            'tanggal_selesai' => 'sometimes|date|after_or_equal:tanggal_mulai'
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

            //Tuk
            'tuk.string'      => 'TUK harus berupa teks.',
            'tuk.max'         => 'TUK maksimal 255 karakter.',

            // Tanggal Mulai dan Selesai
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid (contoh: 2025-08-25).',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid (contoh: 2025-08-25).',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.'

        ]);

        DB::beginTransaction();
        try {
            $assessment = Assesment::create([
                'skema_id' => $validated['skema_id'],
                'admin_id' => $validated['admin_id'],
                'assesor_id' => $validated['assesor_id'],
                'tanggal_assesment' => $validated['tanggal_assesment'],
                'status' => $validated['status'],
                'tuk' => $validated['tuk'] ?? null, 
                'tanggal_mulai' => $validated['tanggal_mulai'] ?? null, 
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null

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
            'tujuan_asesmen' => 'required|string|max:255',
            'schema_id' => 'required|exists:schemas,id',
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

            $formApl01SertificationData = FormApl01SertificationData::create([
                'form_apl01_id' => $formApl01->id,
                'schema_id' => $validated['schema_id'],
                'tujuan_asesmen' => $validated['tujuan_asesmen']
            ]);

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

    public function formAk02(Request $request)
    {
        $validated = $request->validate([
            'assesment_asesi_id' => 'required|exists:assesment_asesi,id',
            'ttd_asesi' => 'nullable|in:belum,sudah',
            'ttd_asesor' => 'nullable|in:belum,sudah',
            'units' => 'required|array',
            'units.*.unit_id' => 'required|exists:units,id',
            'units.*.rekomendasi_hasil' => 'required|in:kompeten,tidak_kompeten',
            'units.*.tindak_lanjut' => 'nullable|string',
            'units.*.komentar_asesor' => 'nullable|string',
            'units.*.bukti_yang_relevan' => 'required|array',
            'units.*.bukti_yang_relevan.*.bukti_description' => [
                'required',
                Rule::exists('bukti_dokumen_assesi', 'description')->where(function ($query) {
                    $query->where('assesi_id', Assesment_Asesi::find(request('assesment_asesi_id'))->assesi_id);
                })
            ]
        ],
        [
            // General errors
            'assesment_asesi_id.required' => 'ID asesmen asesi wajib diisi.',
            'assesment_asesi_id.exists' => 'ID asesmen asesi tidak ditemukan dalam sistem.',

            // Units array errors
            'units.required' => 'Data unit wajib diisi.',
            'units.array' => 'Data unit harus berupa array.',

            // Unit-specific errors
            'units.*.unit_id.required' => 'ID unit wajib diisi untuk setiap unit.',
            'units.*.unit_id.exists' => 'ID unit tidak valid atau tidak ditemukan dalam sistem.',
            'units.*.rekomendasi_hasil.required' => 'Rekomendasi hasil wajib diisi untuk setiap unit.',
            'units.*.rekomendasi_hasil.in' => 'Rekomendasi hasil hanya boleh bernilai "kompeten" atau "tidak_kompeten".',
            'units.*.tindak_lanjut.string' => 'Tindak lanjut harus berupa teks.',
            'units.*.komentar_asesor.string' => 'Komentar asesor harus berupa teks.',
            'units.*.ttd_asesi.string' => 'Tanda tangan asesi harus berupa teks.',
            'units.*.ttd_asesor.string' => 'Tanda tangan asesor harus berupa teks.',

            // Bukti yang relevan errors
            'units.*.bukti_yang_relevan.required' => 'Bukti yang relevan wajib diisi untuk setiap unit.',
            'units.*.bukti_yang_relevan.array' => 'Bukti yang relevan harus berupa array.',
            'units.*.bukti_yang_relevan.*.bukti_description.required' => 'Deskripsi bukti wajib diisi.',
            'units.*.bukti_yang_relevan.*.bukti_description.exists' => 'Bukti dokumen yang dipilih tidak ditemukan untuk asessee ini.'
        ]);

        
        $assesi_id = Assesment_Asesi::find(request('assesment_asesi_id'))->assesi_id;


        DB::beginTransaction();
        try {
            $mainSubmission = Ak02Submission::create([
                'assesment_asesi_id' => $validated['assesment_asesi_id'],
                'ttd_asesi' => $validated['ttd_asesi'] ?? null,
                'ttd_asesor' => $validated['ttd_asesor'] ?? null,
            ]);

            // Preload bukti untuk efisiensi
            $buktiDescriptions = collect($validated['units'])
                ->pluck('bukti_yang_relevan.*.bukti_description')
                ->flatten();

            $buktiDokumenMap = BuktiDokumenAssesi::whereIn('description', $buktiDescriptions)
                ->where('assesi_id', $assesi_id)
                ->get()
                ->keyBy('description');

            foreach ($validated['units'] as $unit) {
                $detail = $mainSubmission->details()->create([
                    'unit_id' => $unit['unit_id'],
                    'rekomendasi_hasil' => $unit['rekomendasi_hasil'],
                    'tindak_lanjut' => $unit['tindak_lanjut'] ?? null,
                    'komentar_asesor' => $unit['komentar_asesor'] ?? null,
                ]);

                foreach ($unit['bukti_yang_relevan'] as $bukti) {
                    $buktiDokumen = $buktiDokumenMap[$bukti['bukti_description']] ?? null;
                    if (!$buktiDokumen) {
                        throw new \Exception("Bukti dokumen not found: " . $bukti['bukti_description']);
                    }

                    $detail->bukti()->create([
                        'bukti_id' => $buktiDokumen->id
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'AK02 submission successful',
                'submission_id' => $mainSubmission->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AK02 Submission Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit AK02',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function formIa01(Request $request){
        $validated = $request->validate([
            'skema_id' => 'required|exists:schemas,id',
            'assesment_asesi_id' => 'required|exists:assesment_asesi,id',
            'submissions' => 'sometimes|array',
            'submissions.*.unit_ke' => 'required_with:submissions|integer',
            'submissions.*.kode_unit' => 'required_with:submissions|string',
            'submissions.*.elemen' => 'required_with:submissions|array',
            'submissions.*.elemen.*.elemen_id' => 'required_with:submissions|exists:elements,id',
            'submissions.*.elemen.*.kuk' => 'required_with:submissions|array',
            'submissions.*.elemen.*.kuk.*.kuk_id' => 'required_with:submissions|exists:kriteria_untuk_kerja,id',
            'submissions.*.elemen.*.kuk.*.skkni' => 'required_with:submissions|in:ya,tidak',
            'submissions.*.elemen.*.kuk.*.teks_penilaian' => 'required_with:submissions|string'
        ],
        [
            // General errors
            'skema_id.required' => 'ID skema wajib diisi.',
            'skema_id.exists' => 'ID skema tidak ditemukan dalam sistem.',
            'assesment_asesi_id.required' => 'ID asesmen asesi wajib diisi.',
            'assesment_asesi_id.exists' => 'ID asesmen asesi tidak ditemukan dalam sistem.',

            // Submissions array errors (optional for CL page)
            'submissions.array' => 'Data submissions harus berupa array.',

            // Unit-specific errors
            'submissions.*.unit_ke.required_with' => 'Unit ke wajib diisi untuk setiap submission.',
            'submissions.*.unit_ke.integer' => 'Unit ke harus berupa angka.',
            'submissions.*.kode_unit.required_with' => 'Kode unit wajib diisi untuk setiap submission.',
            'submissions.*.kode_unit.string' => 'Kode unit harus berupa teks.',

            // Elemen-specific errors
            'submissions.*.elemen.required_with' => 'Data elemen wajib diisi untuk setiap submission.',
            'submissions.*.elemen.array' => 'Data elemen harus berupa array.',
            'submissions.*.elemen.*.elemen_id.required_with' => 'ID elemen wajib diisi untuk setiap elemen.',
            'submissions.*.elemen.*.elemen_id.exists' => 'ID elemen tidak ditemukan dalam sistem.',

            // KUK-specific errors
            'submissions.*.elemen.*.kuk.required_with' => 'Data KUK wajib diisi untuk setiap elemen.',
            'submissions.*.elemen.*.kuk.array' => 'Data KUK harus berupa array.',
            'submissions.*.elemen.*.kuk.*.kuk_id.required_with' => 'ID KUK wajib diisi untuk setiap KUK.',
            'submissions.*.elemen.*.kuk.*.kuk_id.exists' => 'ID KUK tidak ditemukan dalam sistem.',
            'submissions.*.elemen.*.kuk.*.skkni.required_with' => 'SKKNI wajib diisi untuk setiap KUK.',
            'submissions.*.elemen.*.kuk.*.skkni.in' => 'SKKNI hanya boleh bernilai "ya" atau "tidak".',
            'submissions.*.elemen.*.kuk.*.teks_penilaian.required_with' => 'Teks penilaian wajib diisi untuk setiap KUK.',
            'submissions.*.elemen.*.kuk.*.teks_penilaian.string' => 'Teks penilaian harus berupa teks.'
        ]);

        // Prefer assesor from the related assessment, fallback to authenticated user's assesor if any
        $assesment_assesi = Assesment_Asesi::where('id', $validated['assesment_asesi_id'])->first();
        $assesi = Assesi::where('id', $assesment_assesi->assesi_id)->first();
        $assesment = Assesment::where('id', $assesment_assesi->assesment_id)->first();
        $assesorId = $assesment?->assesor_id;
        $authAssesor = auth()->user()->assesor ?? null;
        if (!$assesorId && $authAssesor) {
            $assesorId = $authAssesor->id;
        }

        DB::beginTransaction();
        try {
            // Create the main submission
            $mainSubmission = FormIa01Submission::create([
                'assesment_asesi_id' => $validated['assesment_asesi_id'],
                'assesor_id' => $assesorId,
                'assesi_id' => $assesi->id,
                'skema_id' => $validated['skema_id'],
                'submission_date' => now()
            ]);

            // Process each submission if provided
            if (!empty($validated['submissions']) && is_array($validated['submissions'])) {
                foreach ($validated['submissions'] as $unit) {
                    foreach ($unit['elemen'] as $elemen) {
                        foreach ($elemen['kuk'] as $kuk) {
                            // Create submission details for KUK
                            $submissionDetail = FormIa01SubmissionDetail::create([
                                'submission_id' => $mainSubmission->id,
                                'unit_ke' => $unit['unit_ke'],
                                'kode_unit' => $unit['kode_unit'],
                                'elemen_id' => $elemen['elemen_id'],
                                'kuk_id' => $kuk['kuk_id'], // Tambahkan kolom kuk_id
                                'skkni' => $kuk['skkni'],
                                'teks_penilaian' => $kuk['teks_penilaian']
                            ]);
                        }
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

    public function formAk03(Request $request)
    {
        $validated = $request->validate([
            'assesment_asesi_id' => 'required|exists:assesment_asesi,id',
            'catatan_tambahan' => 'nullable|string',
            'komponen' => 'required|array',
            'komponen.*.komponen_id' => 'required|exists:komponen,id',
            'komponen.*.hasil' => 'required|in:ya,tidak',
            'komponen.*.catatan_asesi' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $submission = Ak03Submission::create([
                'assesment_asesi_id' => $validated['assesment_asesi_id'],
                'catatan_tambahan' => $validated['catatan_tambahan'] ?? null,
            ]);

            foreach ($validated['komponen'] as $item) {
                $submission->details()->create([
                    'komponen_id' => $item['komponen_id'],
                    'hasil' => $item['hasil'],
                    'catatan_asesi' => $item['catatan_asesi'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'AK03 submission berhasil disimpan',
                'submission_id' => $submission->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AK03 Submission Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit AK03',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function formAk05(Request $request)
    {

        $validated = $request->validate([
            'assesment_asesi_id' => 'required|exists:assesment_asesi,id',
            'keputusan' => 'required|in:k,bk',
            'keterangan' => 'nullable|string',
            'aspek_positif' => 'nullable|string',
            'aspek_negatif' => 'nullable|string',
            'penolakan_hasil' => 'nullable|string',
            'saran_perbaikan' => 'nullable|string',
            'ttd_asesor' => 'required|in:sudah,belum',
        ]);

        try {
            $submission = Ak05Submission::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'AK05 submission berhasil disimpan',
                'submission_id' => $submission->id
            ], 201);

        } catch (\Exception $e) {
            Log::error('AK05 Submission Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit AK05',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getIa01ByAssesi($assesi_id)
    {
        try {
            // Ambil data FormIa01Submission berdasarkan assesi_id
            $ia01Submissions = FormIa01Submission::where('assesi_id', $assesi_id)
                ->with([
                    'details' => function ($query) {
                        $query->with(['element', 'kuk']);
                    },
                    'assesor',
                    'skema'
                ])
                ->get();

            // Jika tidak ada data
            if ($ia01Submissions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IA01 submissions not found for the given assesi ID.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'IA01 submissions retrieved successfully.',
                'data' => $ia01Submissions
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get IA01 Submission Error: ' . $e->getMessage(), [
                'assesi_id' => $assesi_id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve IA01 submissions.',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function getAk02ByAssesi($assesi_id)
    {
        try {
            // Ambil data Ak02Submission berdasarkan assesi_id
            $ak02Submissions = Ak02Submission::whereHas('assesmentAsesi', function ($query) use ($assesi_id) {
                $query->where('assesi_id', $assesi_id);
            })
            ->with([
                'details' => function ($query) {
                    $query->with(['unit', 'bukti.bukti']);
                },
                'assesmentAsesi'
            ])
            ->get();

            // Jika tidak ada data
            if ($ak02Submissions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AK02 submissions not found for the given assesi ID.'
                ], 404);
            }

            // Tambahkan view_url untuk setiap bukti dokumen
            $ak02Submissions->each(function ($submission) {
                $submission->details->each(function ($detail) {
                    $detail->bukti->each(function ($bukti) {
                        if ($bukti->bukti) {
                            $bukti->bukti->view_url = route('bukti-dokumen.view', $bukti->bukti->id);
                        }
                    });
                });
            });

            return response()->json([
                'success' => true,
                'message' => 'AK02 submissions retrieved successfully.',
                'data' => $ak02Submissions
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get AK02 Submission Error: ' . $e->getMessage(), [
                'assesi_id' => $assesi_id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve AK02 submissions.',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function getAk03ByAssesi($assesi_id)
    {
        try {
            // Ambil data Ak03Submission berdasarkan assesi_id
            $ak03Submissions = Ak03Submission::whereHas('assesmentAsesi', function ($query) use ($assesi_id) {
                $query->where('assesi_id', $assesi_id);
            })
            ->with([
                'details' => function ($query) {
                    $query->with('komponen');
                },
                'assesmentAsesi'
            ])
            ->get();

            // Jika tidak ada data
            if ($ak03Submissions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AK03 submissions not found for the given assesi ID.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'AK03 submissions retrieved successfully.',
                'data' => $ak03Submissions
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get AK03 Submission Error: ' . $e->getMessage(), [
                'assesi_id' => $assesi_id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve AK03 submissions.',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function getAk05ByAssesi($assesi_id)
    {
        try {
            // Ambil data Ak05Submission berdasarkan assesi_id
            $ak05Submissions = Ak05Submission::whereHas('assesmentAsesi', function ($query) use ($assesi_id) {
                $query->where('assesi_id', $assesi_id);
            })
            ->with('assesmentAsesi.asesi')
            ->get();

            // Jika tidak ada data
            if ($ak05Submissions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AK05 submissions not found for the given assesi ID.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'AK05 submissions retrieved successfully.',
                'data' => $ak05Submissions
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get AK05 Submission Error: ' . $e->getMessage(), [
                'assesi_id' => $assesi_id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve AK05 submissions.',
                'error' => 'An unexpected error occurred. Please try again later.'
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
