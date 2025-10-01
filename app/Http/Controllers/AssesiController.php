<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Assesi;
use App\Models\FormApl01;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssesiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:6|max:50',
            'jurusan_id' => 'required|exists:jurusan,id',
            'nama_lengkap' => 'required|string|max:255',
            'no_ktp' => 'required|string|max:16|unique:assesi,no_ktp|regex:/^[0-9]+$/',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:15|regex:/^[0-9]+$/',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'kode_pos' => 'required|string|max:10|regex:/^[0-9]+$/',
            'kualifikasi_pendidikan' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            $assesi = Assesi::create([
                'user_id' => $user->id,
                'jurusan_id' => $validated['jurusan_id'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_ktp' => $validated['no_ktp'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'alamat' => $validated['alamat'],
                'no_telepon' => $validated['no_telepon'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'kode_pos' => $validated['kode_pos'],
                'kualifikasi_pendidikan' => $validated['kualifikasi_pendidikan'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assesi created successfully',
                'data' => $assesi
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Assesi: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Assesi',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function profileSelf(Request $request)
    {
        $user = $request->user();
        $assesi = Assesi::with('jurusan')->where('user_id', $user->id)->first();

        if (!$assesi) {
            return response()->json([
                'success' => false,
                'message' => 'Assesi profile not found for current user.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'user' => $user->only(['id','username','email','jurusan_id','role']),
                'assesi' => $assesi,
            ]
        ], 200);
    }

    public function updateSelf(Request $request)
    {
        $user = $request->user();
        $assesi = Assesi::where('user_id', $user->id)->first();
        if (!$assesi) {
            return response()->json([
                'success' => false,
                'message' => 'Assesi profile not found for current user.'
            ], 404);
        }

        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'no_ktp' => 'sometimes|required|string|max:16|unique:assesi,no_ktp,' . $assesi->id . '|regex:/^[0-9]+$/',
            'tempat_lahir' => 'sometimes|required|string|max:255',
            'tanggal_lahir' => 'sometimes|required|date',
            'alamat' => 'sometimes|required|string|max:255',
            'no_telepon' => 'sometimes|required|string|max:15|regex:/^[0-9]+$/',
            'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
            'kode_pos' => 'sometimes|required|string|max:10|regex:/^[0-9]+$/',
            'kualifikasi_pendidikan' => 'sometimes|required|string|max:255',
        ]);

        try {
            $assesi->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $assesi
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update self profile: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function index()
    {
        $assesis = Assesi::with(['user', 'jurusan'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List of Assesi',
            'data' => $assesis,
            'count' => $assesis->count()
        ], 200);
    }

    public function show(Request $request)
    {
        $user_id = $request->user()->id;

        $formApl01 = FormApl01::where('user_id', $user_id)
            ->with(['attachments', 'sertificationData'])
            ->first();

        if(!$formApl01) {
            return response()->json([
                'success' => false,
                'message' => 'Form APL01 has not been created'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Form APL01 retrieved successfully',
            'data' => $formApl01
        ], 200);
    }

    /**
     * Get APL-01 attachments as bukti options for APL-02
     */
    public function getApl01AttachmentsAsBukti(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;

        // Ensure user has assesi record (auto-create if needed)
        if (!$user->assesi) {
            \App\Models\Assesi::create([
                'user_id' => $user->id,
                'jurusan_id' => $user->jurusan_id ?: 1,
                'nama_lengkap' => $user->username ?: 'Asesi Baru',
                'no_ktp' => 'TEMP_' . $user->id . '_' . time(),
                'tempat_lahir' => 'Belum Diisi',
                'tanggal_lahir' => '2000-01-01',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Belum Diisi',
                'kode_pos' => '00000',
                'kualifikasi_pendidikan' => 'Belum Diisi',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $formApl01 = FormApl01::where('user_id', $user_id)
            ->with('attachments')
            ->first();

        if (!$formApl01 || !$formApl01->attachments) {
            return response()->json([
                'success' => true,
                'message' => 'No APL-01 attachments found',
                'data' => []
            ], 200);
        }

        // Format attachments as bukti options
        $buktiOptions = $formApl01->attachments->map(function ($attachment) {
            return [
                'id' => 'apl01_' . $attachment->id, // Prefix to distinguish from regular bukti
                'label' => $attachment->description,
                'file_path' => $attachment->file_path,
                'source' => 'apl01'
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'APL-01 attachments retrieved successfully',
            'data' => $buktiOptions
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $assesi = Assesi::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'no_ktp' => 'sometimes|required|string|max:16|unique:assesi,no_ktp,' . $assesi->id . '|regex:/^[0-9]+$/',
            'tempat_lahir' => 'sometimes|required|string|max:255',
            'tanggal_lahir' => 'sometimes|required|date',
            'alamat' => 'sometimes|required|string|max:255',
            'no_telepon' => 'sometimes|required|string|max:15|regex:/^[0-9]+$/',
            'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
            'kode_pos' => 'sometimes|required|string|max:10|regex:/^[0-9]+$/',
            'kualifikasi_pendidikan' => 'sometimes|required|string|max:255',
        ]);

        try {
            $assesi->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Assesi updated successfully',
                'data' => $assesi
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update Assesi: ' . $e->getMessage(), [
                'assesi_id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update Assesi',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $assesi = Assesi::findOrFail($id);

        try {
            $assesi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Assesi deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete Assesi: ' . $e->getMessage(), [
                'assesi_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Assesi',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }
}
