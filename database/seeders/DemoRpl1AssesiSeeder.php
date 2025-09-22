<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Assesi;
use App\Models\Schema as Skema;
use App\Models\Unit;
use App\Models\Assesment;
use App\Models\Admin;
use App\Models\Assesor;
use App\Models\Assesment_Asesi;
use Carbon\Carbon;

class DemoRpl1AssesiSeeder extends Seeder
{
    /**
     * Ensure the account asesi.rpl1@student.smkn24.ac.id has a full assessment relation
     * with an active assessment and valid skema_id, so the frontend can derive
     * assesment_asesi_id and skema_id.
     */
    public function run(): void
    {
        $email = 'asesi.rpl1@student.smkn24.ac.id';

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->command?->warn("User {$email} not found. Run UserSeeder first.");
            return;
        }

        // Ensure ASSESI profile exists for this user (jurusan 1 = RPL)
        $assesi = Assesi::firstOrCreate(
            ['user_id' => $user->id],
            [
                'jurusan_id' => 1,
                'fullname' => $user->username ?? 'Asesi RPL 1',
                'alamat' => 'Jl. Demo RPL 1',
                'no_telp' => '0800000001',
            ]
        );

        // Ensure there is a Skema for RPL jurusan=1
        $skema = Skema::firstOrCreate(
            [
                'jurusan_id' => 1,
                'judul_skema' => 'Pemrogram Junior',
            ],
            [
                'nomor_skema' => 'SKM.001.01.01.01',
            ]
        );

        // Ensure at least one unit under skema (for APL/IA flows)
        Unit::firstOrCreate(
            [
                'schema_id' => $skema->id,
                'unit_ke' => 1,
            ],
            [
                'kode_unit' => 'J.001.00.00.00',
                'judul_unit' => 'Menggunakan Struktur Data',
            ]
        );

        // Ensure an ACTIVE assessment linked to that skema
        $assessment = Assesment::where('skema_id', $skema->id)
            ->where('status', 'active')
            ->first();

        if (!$assessment) {
            // Resolve admin and asesor (pick any available; prefer jurusan 1 for asesor)
            $admin = Admin::first();
            $asesor = Assesor::where('jurusan_id', 1)->first();

            $assessment = Assesment::create([
                'skema_id' => $skema->id,
                'admin_id' => $admin?->id_admin,
                'assesor_id' => $asesor?->id,
                'tanggal_assesment' => Carbon::now()->toDateString(),
                'status' => 'active',
                'tuk' => 'LSP SMKN 24',
                'tanggal_mulai' => Carbon::now()->toDateTimeString(),
                'tanggal_selesai' => Carbon::now()->addDays(7)->toDateTimeString(),
            ]);
        }

        // Link the asesi to the active assessment
        Assesment_Asesi::firstOrCreate(
            [
                'assesment_id' => $assessment->id,
                'assesi_id' => $assesi->id,
            ],
            [
                'status' => 'active', // or 'scheduled'
            ]
        );

        $this->command?->info("DemoRpl1AssesiSeeder: ensured active assessment link for {$email} (skema_id={$skema->id}).");
    }
}
