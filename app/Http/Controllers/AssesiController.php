<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Assesi;
use Illuminate\Support\Facades\DB;

class AssesiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'jurusan_id' => 'required|exists:jurusan,id',
            'nama_lengkap' => 'required|string|max:255',
            'no_ktp' => 'required|string|max:16|unique:assesi,no_ktp|regex:/^[0-9]+$/',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:15',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'kode_pos' => 'required|string|max:10',
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
                'message' => 'Assesi created successfully',
                'assesi' => $assesi
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create Assesi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $assesis = Assesi::with('user', 'jurusan')->get();
        return response()->json([
            'assesi' => [
                'count' => $assesis->count(),
                'message' => 'List of Assesi',
                'status' => 'success',
                'data' => $assesis
            ]
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
            'no_telepon' => 'sometimes|required|string|max:15',
            'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
            'kode_pos' => 'sometimes|required|string|max:10',
            'kualifikasi_pendidikan' => 'sometimes|required|string|max:255',
        ]);

        $assesi->update($validated);

        return response()->json([
            'message' => 'Assesi updated successfully',
            'assesi' => $assesi
        ], 200);
    }

    public function destroy($id)
    {
        $assesi = Assesi::findOrFail($id);
        $assesi->delete();

        return response()->json([
            'message' => 'Assesi deleted successfully'
        ], 200);
    }
}
