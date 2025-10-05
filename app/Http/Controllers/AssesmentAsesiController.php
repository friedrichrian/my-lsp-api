<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assesment_Asesi;
use App\Models\Assesment;
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
            'data' => Assesment_Asesi::with('asesi')->get()
        ]);
    }

    public function show($id)
    {
        try {
            $assesmentAsesi = Assesment_Asesi::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Assessment participant details retrieved successfully',
                'data' => $assesmentAsesi
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment participant not found',
                'error'   => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while retrieving assessment participant',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function showByUser($user_id){
        try{
            $assesmentAsesi = Assesment_Asesi::whereHas('asesi', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->get();
            if ($assesmentAsesi->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No assessment participants found for this asesi',
                    'data'    => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Assessment participants retrieved successfully',
                'data'    => $assesmentAsesi
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while retrieving assessment participants',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function showByAsesi($assesi_id)
    {
        try {
            $assesmentAsesi = Assesment_Asesi::where('assesi_id', $assesi_id)
            ->with('assesi')
            ->get();


            if ($assesmentAsesi->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No assessment participants found for this asesi',
                    'data'    => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Assessment participants retrieved successfully',
                'data'    => $assesmentAsesi
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while retrieving assessment participants',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function showAsesiByAsesor($assesor_id)
    {
        try {
            $assesments = Assesment::where('status', 'active')
                ->where('assesor_id', $assesor_id)
                ->with('assesment_asesi.asesi')
                ->get();

            if ($assesments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active assessments found for this assessor',
                    'data'    => []
                ], 404);
            }

            // Ambil hanya data asesi dari assesment
            $asesiList = $assesments->flatMap(function ($assesment) {
                return $assesment->assesment_asesi->map(function ($aa) {
                    return $aa->asesi;
                });
            })->unique('id')->values(); // biar gak duplikat kalau ada

            return response()->json([
                'success' => true,
                'message' => 'Active asesi list for this assessor',
                'data'    => $asesiList
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while retrieving asesi for assessor',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function showAssesmentAsesiByAssesment($assesment_id)
    {
        try {
            $assesmentAsesi = Assesment_Asesi::where('assesment_id', $assesment_id)
                ->with('asesi') // load relasi asesi biar dapet detail peserta
                ->get();

            if ($assesmentAsesi->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No participants found for this assessment',
                    'data'    => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Assessment participants retrieved successfully',
                'data'    => $assesmentAsesi
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while retrieving participants for this assessment',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'assesment_id' => 'required|exists:assesments,id',
            // Table name is 'assesi' as per migration and model
            'assesi_id'    => 'required|exists:assesi,id',
        ]);

        try {
            $assesmentAsesi = DB::transaction(function () use ($validated) {
                // Cek apakah peserta sudah pernah daftar assessment lain
            $alreadyJoined = Assesment_Asesi::where('assesi_id', $validated['assesi_id'])
                ->whereNotIn('status', ['selesai','kompeten', 'tidak kompeten', 'gagal'])
                ->exists();

                if ($alreadyJoined) {
                    throw new \Exception('Peserta sudah terdaftar pada assessment lain dan tidak bisa mendaftar lagi.');
                }

                // Kalau belum pernah daftar, simpan
                return Assesment_Asesi::create([
                    'assesment_id' => $validated['assesment_id'],
                    'assesi_id'    => $validated['assesi_id']
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
            'assesi_id' => 'sometimes|exists:assesi,id',
            'status' => 'sometimes|in:belum,mengerjakan,selesai',
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

    public function setAssesmentAsesiStatus(Request $request, $id)
    {
        $assesmentAsesi = Assesment_Asesi::findOrFail($id)->with('assesment', 'assesi')->first();
        $validated = $request->validate([
            'status' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $assesmentAsesi->update(['status' => $validated['status']]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Assessment participant status updated successfully',
                'data' => $assesmentAsesi
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating assessment participant status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assessment participant status',
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
