<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Assesment_Asesi;
use App\Models\Assesment;
use App\Models\Assesi;

class AssesmentAsesiSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil assessment aktif (atau assessment pertama jika tidak ada yang aktif)
        $assessment = Assesment::where('status', 'active')->first();
        if (!$assessment) {
            $assessment = Assesment::first();
        }

        if (!$assessment) {
            $this->command?->warn('Tidak ada data assessment. Jalankan AssesmentSeeder terlebih dahulu.');
            return;
        }

        $asesees = Assesi::all();
        if ($asesees->isEmpty()) {
            $this->command?->warn('Tidak ada data asesi untuk ditautkan. Jalankan AssesiSeeder terlebih dahulu.');
            return;
        }

        $count = 0;
        foreach ($asesees as $as) {
            try {
                Assesment_Asesi::firstOrCreate(
                    [
                        'assesment_id' => $assessment->id,
                        'assesi_id' => $as->id,
                    ],
                    [
                        'status' => 'scheduled',
                    ]
                );
                $count++;
            } catch (\Throwable $e) {
                Log::warning('Skip link assesment_asesi for asesi_id='.$as->id.' error='.$e->getMessage());
                continue;
            }
        }

        $this->command?->info("Assesment_Asesi seeded/ensured: {$count} records for assessment ID {$assessment->id}");
    }
}
