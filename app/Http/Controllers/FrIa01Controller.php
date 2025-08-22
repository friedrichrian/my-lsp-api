<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssessmentSession;
use App\Models\ObservationGroup;
use App\Models\ObservationUnit;
use App\Models\ObservationElement;
use App\Models\ObservationKuk;
use App\Models\Unit;
use App\Models\Element;
use App\Models\KriteriaUntukKerja;
use App\Models\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FrIa01Controller extends Controller
{
    /**
     * Membuat sesi asesmen FR.IA.01 baru dengan auto-seed kelompok & unit
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul_skema' => 'required|string',
            'nomor_skema' => 'required|string',
            'tuk' => 'required|string',
            'assesor_id' => 'required|exists:assesor,id',
            'assesi_id' => 'required|exists:assesi,id',
            'tanggal_asesmen' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Buat sesi asesmen
            $assessmentSession = AssessmentSession::create($request->only([
                'judul_skema',
                'nomor_skema',
                'tuk',
                'assesor_id',
                'assesi_id',
                'tanggal_asesmen'
            ]));

            // Auto-seed kelompok pekerjaan (Kelompok 1, 2, 3)
            $groups = ['Kelompok 1', 'Kelompok 2', 'Kelompok 3'];
            foreach ($groups as $groupName) {
                $group = ObservationGroup::create([
                    'assessment_session_id' => $assessmentSession->id,
                    'nama_kelompok' => $groupName
                ]);

                // Auto-seed unit kompetensi untuk setiap kelompok
                $schema = Schema::where('nomor_skema', 'SSP.BNSP.001.2023')->first();
                if (!$schema) {
                    throw new \Exception('Schema Pemrograman Junior tidak ditemukan. Jalankan seeder terlebih dahulu.');
                }
                $units = Unit::with('elements.kriteriaUntukKerja')->where('schema_id', $schema->id)->get();
                foreach ($units as $unit) {
                    $observationUnit = ObservationUnit::create([
                        'observation_group_id' => $group->id,
                        'unit_id' => $unit->id
                    ]);

                    // Auto-seed elemen untuk setiap unit
                    foreach ($unit->elements as $element) {
                        $observationElement = ObservationElement::create([
                            'observation_unit_id' => $observationUnit->id,
                            'element_id' => $element->id
                        ]);

                        // Auto-seed KUK untuk setiap elemen
                        foreach ($element->kriteriaUntukKerja as $kuk) {
                            ObservationKuk::create([
                                'observation_element_id' => $observationElement->id,
                                'kriteria_untuk_kerja_id' => $kuk->id,
                                'ya' => false,
                                'tidak' => false
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sesi asesmen FR.IA.01 berhasil dibuat',
                'data' => $this->getAssessmentSessionData($assessmentSession->id)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat sesi asesmen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan data lengkap sesi asesmen
     */
    public function show($id)
    {
        $assessmentSession = AssessmentSession::with([
            'assesor',
            'assesi',
            'observationGroups.observationUnits.unit',
            'observationGroups.observationUnits.observationElements.element',
            'observationGroups.observationUnits.observationElements.observationKuks.kriteriaUntukKerja'
        ])->find($id);

        if (!$assessmentSession) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi asesmen tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatAssessmentSessionData($assessmentSession)
        ]);
    }

    /**
     * Update penilaian KUK (Ya/Tidak, catatan, dll)
     */
    public function updateKuk(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ya' => 'boolean',
            'tidak' => 'boolean',
            'standar_industri' => 'nullable|string',
            'penilaian_lanjut' => 'nullable|string',
            'catatan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validasi: tidak boleh pilih Ya dan Tidak sekaligus
        if ($request->has('ya') && $request->has('tidak') && $request->ya && $request->tidak) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak boleh memilih Ya dan Tidak sekaligus'
            ], 422);
        }

        $observationKuk = ObservationKuk::find($id);
        if (!$observationKuk) {
            return response()->json([
                'success' => false,
                'message' => 'KUK tidak ditemukan'
            ], 404);
        }

        $observationKuk->update($request->only([
            'ya',
            'tidak',
            'standar_industri',
            'penilaian_lanjut',
            'catatan'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Penilaian KUK berhasil diupdate',
            'data' => $observationKuk->load('kriteriaUntukKerja')
        ]);
    }

    /**
     * Update umpan balik untuk kelompok
     */
    public function updateGroupFeedback(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'umpan_balik' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = ObservationGroup::find($id);
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Kelompok tidak ditemukan'
            ], 404);
        }

        $group->update(['umpan_balik' => $request->umpan_balik]);

        return response()->json([
            'success' => true,
            'message' => 'Umpan balik berhasil diupdate',
            'data' => $group
        ]);
    }

    /**
     * Update hasil asesmen final
     */
    public function updateAssessmentResult(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'hasil_asesmen' => 'required|in:kompeten,belum_kompeten',
            'catatan_asesor' => 'nullable|string',
            'status' => 'required|in:draft,in_progress,completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $assessmentSession = AssessmentSession::find($id);
        if (!$assessmentSession) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi asesmen tidak ditemukan'
            ], 404);
        }

        $assessmentSession->update($request->only([
            'hasil_asesmen',
            'catatan_asesor',
            'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Hasil asesmen berhasil diupdate',
            'data' => $this->getAssessmentSessionData($assessmentSession->id)
        ]);
    }

    /**
     * Menghapus sesi asesmen
     */
    public function destroy($id)
    {
        $assessmentSession = AssessmentSession::find($id);
        if (!$assessmentSession) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi asesmen tidak ditemukan'
            ], 404);
        }

        $assessmentSession->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesi asesmen berhasil dihapus'
        ]);
    }

    /**
     * Helper method untuk mendapatkan data sesi asesmen
     */
    private function getAssessmentSessionData($id)
    {
        return AssessmentSession::with([
            'assesor',
            'assesi',
            'observationGroups.observationUnits.unit',
            'observationGroups.observationUnits.observationElements.element',
            'observationGroups.observationUnits.observationElements.observationKuks.kriteriaUntukKerja'
        ])->find($id);
    }

    /**
     * Helper method untuk format data sesi asesmen
     */
    private function formatAssessmentSessionData($assessmentSession)
    {
        return [
            'id' => $assessmentSession->id,
            'judul_skema' => $assessmentSession->judul_skema,
            'nomor_skema' => $assessmentSession->nomor_skema,
            'tuk' => $assessmentSession->tuk,
            'tanggal_asesmen' => $assessmentSession->tanggal_asesmen->format('Y-m-d'),
            'hasil_asesmen' => $assessmentSession->hasil_asesmen,
            'catatan_asesor' => $assessmentSession->catatan_asesor,
            'status' => $assessmentSession->status,
            'assesor' => [
                'id' => $assessmentSession->assesor->id,
                'nama' => $assessmentSession->assesor->nama_lengkap
            ],
            'assesi' => [
                'id' => $assessmentSession->assesi->id,
                'nama' => $assessmentSession->assesi->nama_lengkap
            ],
            'kelompok_pekerjaan' => $assessmentSession->observationGroups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'nama_kelompok' => $group->nama_kelompok,
                    'umpan_balik' => $group->umpan_balik,
                    'unit_kompetensi' => $group->observationUnits->map(function ($unit) {
                        return [
                            'id' => $unit->id,
                            'kode_unit' => $unit->unit->kode_unit,
                            'judul_unit' => $unit->unit->judul_unit,
                            'elemen' => $unit->observationElements->map(function ($element) {
                                return [
                                    'id' => $element->id,
                                    'nama_elemen' => $element->element->nama_elemen,
                                    'kriteria_unjuk_kerja' => $element->observationKuks->map(function ($kuk) {
                                        return [
                                            'id' => $kuk->id,
                                            'deskripsi_kuk' => $kuk->kriteriaUntukKerja->deskripsi_kuk,
                                            'ya' => $kuk->ya,
                                            'tidak' => $kuk->tidak,
                                            'standar_industri' => $kuk->standar_industri,
                                            'penilaian_lanjut' => $kuk->penilaian_lanjut,
                                            'catatan' => $kuk->catatan
                                        ];
                                    })
                                ];
                            })
                        ];
                    })
                ];
            })
        ];
    }
}
