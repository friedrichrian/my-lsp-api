<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assesment_Asesi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AssesmentAsesiController extends Controller
{
    //
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'List of assessment participants',
            'data' => Assesment_Asesi::all()
        ]);
    }

    public function show($id){
        $assesmentAsesi = Assesment_Asesi::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Assessment participant details',
            'data' => $assesmentAsesi
        ]);
    }

    public function showByAsesi($assesi_id)
    {
        $assesmentAsesi = Assesment_Asesi::where('assesi_id', $assesi_id)->get();
        return response()->json([
            'success' => true,
            'message' => 'Assessment participants for assesi',
            'data' => $assesmentAsesi
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assesment_id' => 'required|exists:assesments,id',
            'assesi_id'    => 'required|exists:assesi,id',
        ]);

        try {
            $assesmentAsesi = DB::transaction(function () use ($validated) {
                // Cek apakah peserta sudah pernah daftar assessment lain
                $alreadyJoined = Assesment_Asesi::where('assesi_id', $validated['assesi_id'])->exists();

                if ($alreadyJoined) {
                    throw new \Exception('Peserta sudah terdaftar pada assessment lain dan tidak bisa mendaftar lagi.');
                }

                // Kalau belum pernah daftar, simpan
                return Assesment_Asesi::create([
                    'assesment_id' => $validated['assesment_id'],
                    'assesi_id'    => $validated['assesi_id'],
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Assessment participant created successfully',
                'data'    => $assesmentAsesi
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating assessment participant: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400); // error validasi logika
        }
    }



    public function update(Request $request, $id)
    {
        $assesmentAsesi = Assesment_Asesi::findOrFail($id);
        $validated = $request->validate([
            'assesment_id' => 'sometimes|exists:assesments,id',
            'assesi_id' => 'sometimes|exists:asesis,id',
            'status' => 'sometimes|in:k,bk',
        ]);

        DB::beginTransaction();
        try {
            $assesmentAsesi->update($validated);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Assessment participant updated successfully',
                'data' => $assesmentAsesi
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating assessment participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assessment participant',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $assesmentAsesi = Assesment_Asesi::findOrFail($id);
        DB::beginTransaction();
        try {
            $assesmentAsesi->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Assessment participant deleted successfully' 
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting assessment participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete assessment participant',
            ], 500);    
        }    
    }
}
