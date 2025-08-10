<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assesor;
use App\Models\AssesorAttachment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssesorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_registrasi' => 'required|string|unique:assesi,no_ktp',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'email' => 'required|email|unique:users',
            'no_telepon' => 'nullable|string|max:15',
            'kompetensi' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'attachments' => 'required|array',
            'attachments.*' => 'required|image|mimes:jpg,jpeg,png'
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            $assesor = Assesor::create([
                'user_id' => $user->id,
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_registrasi' => $validated['no_registrasi'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'email' => $validated['email'],
                'no_telepon' => $validated['no_telepon'],
                'kompetensi' => $validated['kompetensi']
            ]);
            
            foreach ($validated['attachments'] as $attachment) {
                // Generate a unique filename
                $filename = uniqid().'_'.$attachment->getClientOriginalName();
                $path = 'private/assesor/'.$assesor->id.'/'.$filename;
                
                // Get file contents and encrypt
                $encryptedContents = encrypt(file_get_contents($attachment->getRealPath()));
                
                // Store using file_put_contents for encrypted data
                $fullPath = Storage::path($path);
                Storage::makeDirectory(dirname($path));
                file_put_contents($fullPath, $encryptedContents);
                
                // Create attachment record
                AssesorAttachment::create([
                    'assesor_id' => $assesor->id,
                    'nama_dokumen' => $attachment->getClientOriginalName(),
                    'file_path' => $path // Store the relative path
                ]);
            }
            
            DB::commit();

            return response()->json([
                'message' => 'Assesor created successfully'
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create Assesor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $size = $request->query('size', 10);

        $assessors = Assesor::with('user')->paginate($size, ['*'], 'page', $page);

        return response()->json([
            'page' => $page,
            'size' => $size,
            'assessors' => $assessors
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $assesor = Assesor::with(['user', 'attachments'])
            ->where('id', $id)
            ->first();

        if (!$assesor) {
            return response()->json([
                'message' => 'Assesor not found'
            ], 404);
        }



        return response()->json([
            'assesor' => $assesor
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $assesor = Assesor::with('user', 'attachments')->where('id',$id)->first();
        if (!$assesor) {
            return response()->json([
                'message' => 'Assesor not found'
            ], 404);
        }

        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'no_registrasi' => 'sometimes|required|string|unique:assesi,no_ktp,'.$assesor->id,
            'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
            'email' => 'sometimes|required|email|unique:users,email,'.$assesor->user_id,
            'no_telepon' => 'nullable|string|max:15',
            'kompetensi' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|unique:users,username,'.$assesor->user_id,
            'password' => 'nullable|string|min:6',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|image|mimes:jpg,jpeg,png',
            'remove_attachments' => 'nullable|array' // list of attachment IDs to delete
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
            $assesor->update([
                'nama_lengkap' => $validated['nama_lengkap'] ?? $assesor->nama_lengkap,
                'no_registrasi' => $validated['no_registrasi'] ?? $assesor->no_registrasi,
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? $assesor->jenis_kelamin,
                'email' => $validated['email'] ?? $assesor->email,
                'no_telepon' => $validated['no_telepon'] ?? $assesor->no_telepon,
                'kompetensi' => $validated['kompetensi'] ?? $assesor->kompetensi,
            ]);

            // Remove attachments if requested
            if (!empty($validated['remove_attachments'])) {
                foreach ($validated['remove_attachments'] as $attachmentId) {
                    $attachment = AssesorAttachment::find($attachmentId);
                    if ($attachment && $attachment->assesor_id == $assesor->id) {
                        Storage::delete($attachment->file_path);
                        $attachment->delete();
                    }
                }
            }

            // Add new attachments
            if (!empty($validated['attachments'])) {
                foreach ($validated['attachments'] as $attachment) {
                    $filename = uniqid().'_'.$attachment->getClientOriginalName();
                    $path = 'assesor/'.$assesor->id.'/'.$filename;
                    $encryptedContents = encrypt(file_get_contents($attachment->getRealPath()));
                    Storage::makeDirectory(dirname($path));
                    file_put_contents(Storage::path($path), $encryptedContents);

                    AssesorAttachment::create([
                        'assesor_id' => $assesor->id,
                        'nama_dokumen' => $attachment->getClientOriginalName(),
                        'file_path' => 'private/'.$path
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Assesor updated successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update Assesor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $assesor = Assesor::with('user', 'attachments')->find($id);
        if (!$assesor) {
            return response()->json([
                'message' => 'Assesor not found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Delete attachments
            foreach ($assesor->attachments as $attachment) {
                Storage::delete($attachment->file_path);
                $attachment->delete();
            }

            // Delete user
            $assesor->user->delete();

            // Delete assesor
            $assesor->delete();

            DB::commit();
            return response()->json([
                'message' => 'Assesor deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete Assesor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
