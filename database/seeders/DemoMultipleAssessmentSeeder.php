<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Assesment;
use App\Models\Assesment_Asesi;
use App\Models\Admin;
use App\Models\Assesor;
use App\Models\Assesi;
use App\Models\Schema;
use App\Models\User;

class DemoMultipleAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get required models
        $assesor = Assesor::first();
        $admin = Admin::first();
        $schemas = Schema::take(3)->get(); // Get up to 3 schemas
        
        if (!$assesor || !$admin || $schemas->isEmpty()) {
            $this->command->warn('DemoMultipleAssessmentSeeder skipped: missing Assesor/Admin/Schema.');
            return;
        }

        // Get demo asesi
        $demoUser = User::where('email', 'asesi.rpl1@student.smkn24.ac.id')->first();
        $assesi = $demoUser?->assesi ?: Assesi::first();
        
        if (!$assesi) {
            $this->command->warn('No Assesi found for DemoMultipleAssessmentSeeder.');
            return;
        }

        $assessmentData = [
            [
                'status' => 'active', // Valid enum: expired, active
                'tuk' => 'SMK Negeri 24 Jakarta - Lab Komputer 1',
                'tanggal_mulai' => now()->startOfDay()->addHours(8),
                'tanggal_selesai' => now()->startOfDay()->addHours(16),
                'assesi_status' => 'mengerjakan' // Valid enum: belum, mengerjakan, selesai
            ],
            [
                'status' => 'active', // Valid enum: expired, active (no 'scheduled')
                'tuk' => 'SMK Negeri 24 Jakarta - Lab Komputer 2',
                'tanggal_mulai' => now()->addDays(3)->startOfDay()->addHours(9),
                'tanggal_selesai' => now()->addDays(3)->startOfDay()->addHours(17),
                'assesi_status' => 'belum' // Valid enum: belum, mengerjakan, selesai
            ],
            [
                'status' => 'expired', // Valid enum: expired, active (no 'completed')
                'tuk' => 'SMK Negeri 24 Jakarta - Lab Multimedia',
                'tanggal_mulai' => now()->subDays(7)->startOfDay()->addHours(8),
                'tanggal_selesai' => now()->subDays(7)->startOfDay()->addHours(16),
                'assesi_status' => 'selesai' // Valid enum: belum, mengerjakan, selesai
            ]
        ];

        foreach ($assessmentData as $index => $data) {
            $schema = $schemas[$index % $schemas->count()];
            
            // Create assessment
            $assessment = Assesment::updateOrCreate(
                [
                    'skema_id' => $schema->id,
                    'status' => $data['status'],
                ],
                [
                    'admin_id' => $admin->id_admin,
                    'assesor_id' => $assesor->id,
                    'tanggal_assesment' => $data['tanggal_mulai']->toDateString(),
                    'tuk' => $data['tuk'],
                    'tanggal_mulai' => $data['tanggal_mulai'],
                    'tanggal_selesai' => $data['tanggal_selesai'],
                ]
            );

            // Link with assesment_asesi
            Assesment_Asesi::updateOrCreate(
                [
                    'assesment_id' => $assessment->id,
                    'assesi_id' => $assesi->id,
                ],
                [
                    'status' => $data['assesi_status'],
                ]
            );
        }

        $this->command->info('DemoMultipleAssessmentSeeder: Created multiple assessments with different statuses.');
    }
}
