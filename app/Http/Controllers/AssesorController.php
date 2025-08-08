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

    public function show($id)
    {
        $assesor = Assesor::with(['user', 'attachments'])->findOrFail($id);

        return response()->json([
            'assesor' => $assesor
        ], 200);
    }
}
