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

class DemoActiveAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        // Pick or create an assessor
        $assesor = Assesor::first();
        $admin = Admin::first();
        $schema = Schema::first();
        if (!$assesor || !$admin || !$schema) {
            $this->command->warn('DemoActiveAssessmentSeeder skipped: missing Assesor/Admin/Schema. Run base seeders first.');
            return;
        }

        // Create an ACTIVE assessment for the first schema
        $assessment = Assesment::create([
            'skema_id' => $schema->id,
            'admin_id' => $admin->id_admin,
            'assesor_id' => $assesor->id,
            'tanggal_assesment' => now()->toDateString(),
            'status' => 'active',
            'tuk' => 'SMK Negeri 24 Jakarta',
            'tanggal_mulai' => now()->startOfDay()->addHours(8),
            'tanggal_selesai' => now()->startOfDay()->addHours(16),
        ]);

        // Attach a demo asesi to the assessment (prefer RPL1 demo asesi if exists)
        $demoUser = User::where('email', 'asesi.rpl1@student.smkn24.ac.id')->first();
        $assesi = $demoUser?->assesi ?: Assesi::first();
        if (!$assesi) {
            $this->command->warn('No Assesi found for DemoActiveAssessmentSeeder.');
            return;
        }

        // Link with assesment_asesi as active/scheduled
        Assesment_Asesi::updateOrCreate(
            [
                'assesment_id' => $assessment->id,
                'assesi_id' => $assesi->id,
            ],
            [
                'status' => 'active',
            ]
        );

        $this->command->info('DemoActiveAssessmentSeeder: Active assessment created and linked to a demo asesi.');
    }
}
