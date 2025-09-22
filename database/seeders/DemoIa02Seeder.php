<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Assesment_Asesi;
use App\Models\Assesment;
use App\Models\Schema;
use App\Models\Ia02Submission;
use App\Models\Assesi;

class DemoIa02Seeder extends Seeder
{
    public function run(): void
    {
        // Pick an active assesment_asesi; if none, try any
        $aa = Assesment_Asesi::where('status', 'active')->first() ?: Assesment_Asesi::first();
        if (!$aa) {
            $this->command->warn('DemoIa02Seeder skipped: no assesment_asesi found.');
            return;
        }

        $assesment = Assesment::find($aa->assesment_id);
        $schema = $assesment?->schema ?: Schema::first();
        $assesi = Assesi::find($aa->assesi_id);

        $judulUnit = optional($schema?->units()->first())->judul_unit ?? null;
        $kodeUnit = optional($schema?->units()->first())->kode_unit ?? null;

        Ia02Submission::updateOrCreate(
            [
                'assesment_asesi_id' => $aa->id,
            ],
            [
                'skema_id' => $schema?->id,
                'skema_sertifikasi' => $schema?->judul_skema,
                'judul_unit' => $judulUnit,
                'kode_unit' => $kodeUnit,
                'tuk' => $assesment?->tuk,
                'nama_asesor' => optional($assesment?->assesor)->nama_lengkap,
                'nama_asesi' => optional($assesi)->nama_lengkap,
                'tanggal_asesmen' => $assesment?->tanggal_assesment,
                'extra' => [
                    'note' => 'Demo IA-02 seeded',
                ],
            ]
        );

        $this->command->info('DemoIa02Seeder: Seeded a demo IA-02 submission.');
    }
}
