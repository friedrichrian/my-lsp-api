<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assesment;
use App\Models\Assesment_Asesi;
use App\Models\Admin;
use App\Models\Assesor;
use App\Models\Assesi;

class AssesmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin and asesor
        $admin = Admin::first();
        $asesorRpl = Assesor::where('no_registrasi', 'ASR-RPL-001')->first();
        $asesorTbg = Assesor::where('no_registrasi', 'ASR-TBG-001')->first();
        $asesorPh = Assesor::where('no_registrasi', 'ASR-PH-001')->first();
        $asesorBsn = Assesor::where('no_registrasi', 'ASR-BSN-001')->first();
        $asesorUlw = Assesor::where('no_registrasi', 'ASR-ULW-001')->first();

        // Check if required data exists
        if (!$admin) {
            $this->command->error('No admin found. Please run AdminSeeder first.');
            return;
        }

        if (!$asesorRpl || !$asesorTbg || !$asesorPh || !$asesorBsn || !$asesorUlw) {
            $this->command->error('Some asesor not found. Please run AssesorSeeder first.');
            return;
        }

        // Create assessments
        $assessments = [
            [
                'skema_id' => 1, // RPL Schema
                'admin_id' => $admin->id_admin,
                'assesor_id' => $asesorRpl->id,
                'tanggal_assesment' => '2024-12-15',
                'status' => 'active',
                'tuk' => 'SMK Negeri 24 Jakarta',
                'tanggal_mulai' => '2024-12-15 08:00:00',
                'tanggal_selesai' => '2024-12-15 16:00:00'
            ],
            [
                'skema_id' => 2, // TBG Schema
                'admin_id' => $admin->id_admin,
                'assesor_id' => $asesorTbg->id,
                'tanggal_assesment' => '2024-12-16',
                'status' => 'active',
                'tuk' => 'SMK Negeri 24 Jakarta',
                'tanggal_mulai' => '2024-12-16 08:00:00',
                'tanggal_selesai' => '2024-12-16 16:00:00'
            ],
            [
                'skema_id' => 3, // PH Schema
                'admin_id' => $admin->id_admin,
                'assesor_id' => $asesorPh->id,
                'tanggal_assesment' => '2024-12-17',
                'status' => 'active',
                'tuk' => 'SMK Negeri 24 Jakarta',
                'tanggal_mulai' => '2024-12-17 08:00:00',
                'tanggal_selesai' => '2024-12-17 16:00:00'
            ],
            [
                'skema_id' => 4, // BSN Schema
                'admin_id' => $admin->id_admin,
                'assesor_id' => $asesorBsn->id,
                'tanggal_assesment' => '2024-12-18',
                'status' => 'active',
                'tuk' => 'SMK Negeri 24 Jakarta',
                'tanggal_mulai' => '2024-12-18 08:00:00',
                'tanggal_selesai' => '2024-12-18 16:00:00'
            ],
            [
                'skema_id' => 5, // ULW Schema
                'admin_id' => $admin->id_admin,
                'assesor_id' => $asesorUlw->id,
                'tanggal_assesment' => '2024-12-19',
                'status' => 'active',
                'tuk' => 'SMK Negeri 24 Jakarta',
                'tanggal_mulai' => '2024-12-19 08:00:00',
                'tanggal_selesai' => '2024-12-19 16:00:00'
            ]
        ];

        foreach ($assessments as $assessmentData) {
            $this->command->info("Creating assessment for skema_id: {$assessmentData['skema_id']}");
            
            $assessment = Assesment::create($assessmentData);

            // Load the schema relationship
            $assessment->load('schema');
            
            // Check if schema exists
            if (!$assessment->schema) {
                $this->command->error("Schema not found for assessment with skema_id: {$assessmentData['skema_id']}");
                $this->command->info("Available schemas: " . \App\Models\Schema::pluck('id', 'judul_skema')->toJson());
                continue;
            }

            $this->command->info("Found schema: {$assessment->schema->judul_skema} with jurusan_id: {$assessment->schema->jurusan_id}");

            // Add asesi to assessments based on their jurusan
            $asesiList = Assesi::where('jurusan_id', $assessment->schema->jurusan_id)->get();
            
            $this->command->info("Adding {$asesiList->count()} asesi to assessment for jurusan_id: {$assessment->schema->jurusan_id}");
            
            foreach ($asesiList as $asesi) {
                Assesment_Asesi::create([
                    'assesment_id' => $assessment->id,
                    'assesi_id' => $asesi->id,
                    'status' => 'belum'
                ]);
            }
        }
    }
}
