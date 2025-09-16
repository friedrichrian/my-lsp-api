<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assesor;
use App\Models\AssesorAttachment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AssesorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_registrasi' => 'required|string|unique:assesor,no_registrasi|max:20|regex:/^[A-Za-z0-9\-]+$/',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'email' => 'required|email|unique:users|max:255',
            'no_telepon' => 'nullable|string|max:15|regex:/^[0-9]+$/',
            'kompetensi' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|max:50',
            'attachments' => 'required|array',
            'attachments.*' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Create User
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role' => 'assesor'
            ]);

            // Create Assesor
            $assesor = Assesor::create([
                'user_id' => $user->id,
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_registrasi' => $validated['no_registrasi'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'email' => $validated['email'],
                'no_telepon' => $validated['no_telepon'],
                'kompetensi' => $validated['kompetensi']
            ]);

            // Store Attachments
            foreach ($validated['attachments'] as $attachment) {
                $filename = uniqid() . '_' . $attachment->getClientOriginalName();
                $path = $attachment->storeAs('assesor/' . $assesor->id, $filename, 'private');

                AssesorAttachment::create([
                    'assesor_id' => $assesor->id,
                    'nama_dokumen' => $attachment->getClientOriginalName(),
                    'file_path' => $path
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assesor created successfully',
                'data' => $assesor
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Assesor: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Assesor',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $size = $request->query('size', 10);

        $assessors = Assesor::with('user')->paginate($size);

        return response()->json([
            'success' => true,
            'message' => 'List of Assessors',
            'data' => $assessors
        ], 200);
    }

    public function show($id)
    {
        $assesor = Assesor::with(['user', 'attachments'])->find($id);

        if (!$assesor) {
            return response()->json([
                'success' => false,
                'message' => 'Assesor not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Assesor details',
            'data' => $assesor
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $assesor = Assesor::with('user', 'attachments')->find($id);

        if (!$assesor) {
            return response()->json([
                'success' => false,
                'message' => 'Assesor not found'
            ], 404);
        }

        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'no_registrasi' => 'sometimes|required|string|unique:assesor,no_registrasi,' . $assesor->id . '|max:20|regex:/^[A-Za-z0-9\-]+$/',
            'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
            'email' => 'sometimes|required|email|unique:users,email,' . $assesor->user_id,
            'no_telepon' => 'nullable|string|max:15|regex:/^[0-9]+$/',
            'kompetensi' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $assesor->user_id,
            'password' => 'nullable|string|min:6|max:50',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'remove_attachments' => 'nullable|array'
        ]);

        DB::beginTransaction();
        try {
            // Update User
            $assesor->user->update([
                'username' => $validated['username'] ?? $assesor->user->username,
                'email' => $validated['email'] ?? $assesor->user->email,
                'password' => isset($validated['password']) ? bcrypt($validated['password']) : $assesor->user->password,
            ]);

            // Update Assesor
            $assesor->update($validated);

            // Remove Attachments
            if (!empty($validated['remove_attachments'])) {
                foreach ($validated['remove_attachments'] as $attachmentId) {
                    $attachment = AssesorAttachment::find($attachmentId);
                    if ($attachment && $attachment->assesor_id == $assesor->id) {
                        Storage::delete($attachment->file_path);
                        $attachment->delete();
                    }
                }
            }

            // Add New Attachments
            if (!empty($validated['attachments'])) {
                foreach ($validated['attachments'] as $attachment) {
                    $filename = uniqid() . '_' . $attachment->getClientOriginalName();
                    $path = $attachment->storeAs('assesor/' . $assesor->id, $filename, 'private');

                    AssesorAttachment::create([
                        'assesor_id' => $assesor->id,
                        'nama_dokumen' => $attachment->getClientOriginalName(),
                        'file_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assesor updated successfully',
                'data' => $assesor
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Assesor: ' . $e->getMessage(), [
                'assesor_id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update Assesor',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $assesor = Assesor::with('user', 'attachments')->find($id);

        if (!$assesor) {
            return response()->json([
                'success' => false,
                'message' => 'Assesor not found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Delete Attachments
            foreach ($assesor->attachments as $attachment) {
                Storage::delete($attachment->file_path);
                $attachment->delete();
            }

            // Delete User
            $assesor->user->delete();

            // Delete Assesor
            $assesor->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assesor deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Assesor: ' . $e->getMessage(), [
                'assesor_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Assesor',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }
}
