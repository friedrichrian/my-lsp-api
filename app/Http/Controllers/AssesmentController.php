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
use Illuminate\Support\Facades\Log;
use App\Models\Assesment;

class AssesmentController extends Controller
{
    public function index()
    {
        $assessments = Assesment::with(['skema', 'admin', 'assesor'])->get();
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
            'tanggal_assessment' => 'required|date',
            'status' => 'required|in:expired,active',
        ], [
            'skema_id.required' => 'Skema harus diisi.',
            'skema_id.exists'   => 'Skema yang dipilih tidak ditemukan.',

            'admin_id.required' => 'Admin harus diisi.',
            'admin_id.exists'   => 'Admin yang dipilih tidak ditemukan.',

            'assesor_id.required' => 'Assesor harus diisi.',
            'assesor_id.exists'   => 'Assesor yang dipilih tidak ditemukan.',

            'tanggal_assessment.required' => 'Tanggal assessment wajib diisi.',
            'tanggal_assessment.date'     => 'Format tanggal assessment tidak valid.',

            'status.required' => 'Status harus diisi.',
            'status.in'       => 'Status hanya boleh bernilai expired atau active.',
        ]);

        DB::beginTransaction();
        try {
            $assessment = Assesment::create([
                'skema_id' => $validated['skema_id'],
                'admin_id' => $validated['admin_id'],
                'assesor_id' => $validated['assesor_id'],
                'tanggal_assessment' => $validated['tanggal_assessment'],
                'status' => $validated['status'],
                'tuk' => $request->input('tuk', null), // optional
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assessment created successfully',
                'data' => $assessment
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat assessment',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function updateAssesment(Request $request, $id)
    {
        $validated = $request->validate([
            'id' => 'required|exists:assesments,id',
            'admin_id' => 'sometimes|exists:admin,id',
            'status' => 'sometimes|in:expired,active',
            'tuk' => 'sometimes|string|max:255',
            'skema_id' => 'sometimes|exists:schemas,id',
            'assesor_id' => 'sometimes|exists:assessors,id',
            'tanggal_assessment' => 'sometimes|date'
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
                $file = $attachment['file'];
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('formapl01/' . $formApl01->id, $filename, 'private');

                FormApl01Attachments::create([
                    'form_apl01_id' => $formApl01->id,
                    'nama_dokumen' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'description' => $attachment['description']
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

}
