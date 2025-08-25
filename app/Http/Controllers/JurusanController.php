<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jurusan;

class JurusanController extends Controller
{
    //
    public function index()
    {
        $jurusans = Jurusan::all();
        return response()->json(['message' => 'Jurusan index', 'data' => $jurusans], 200);
    }

    public function show($id)
    {
        $jurusan = Jurusan::find($id);
        if (!$jurusan) {
            return response()->json(['message' => 'Jurusan not found'], 404);
        }
        return response()->json(['message' => 'Jurusan detail', 'data' => $jurusan], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_jurusan'=> 'required|string|max:5|unique:jurusan',
            'nama_jurusan' => 'required|string|max:100|unique:jurusan',
            'jenjang' => 'required|string|max:50',
            'deskripsi' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $jurusan = Jurusan::create([
                'kode_jurusan' => $request->kode_jurusan,
                'nama_jurusan' => $request->nama_jurusan,
                'jenjang' => $request->jenjang,
                'deskripsi' => $request->deskripsi
            ]);

            DB::commit(); // ✅ commit transaksi

            return response()->json([
                'message' => 'Jurusan created',
                'data' => $jurusan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Jurusan failed to create',
                'error'   => $e->getMessage() // ✅ lebih aman tampilkan message aja
            ], 500);
        }
       
    }
}
