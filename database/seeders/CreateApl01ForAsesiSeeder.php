<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Assesi;
use App\Models\FormApl01;
use App\Models\Assesment_Asesi;

class CreateApl01ForAsesiSeeder extends Seeder
{
    public function run(): void
    {
        // Find the specific asesi
        $user = User::where('email', 'asesi.rpl1@student.smkn24.ac.id')->first();
        
        if (!$user) {
            $this->command->error('User asesi.rpl1@student.smkn24.ac.id not found!');
            return;
        }

        $this->command->info("Found user: {$user->name} (ID: {$user->id})");

        // Ensure the user has an assesi record
        $assesi = $user->assesi;
        if (!$assesi) {
            // Create assesi record if it doesn't exist
            $assesi = Assesi::create([
                'user_id' => $user->id,
                'nama_lengkap' => $user->name,
                'no_ktp' => '3171234567890123',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2000-01-01',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Raya Bogor KM 17, Cijantung, Jakarta Timur',
                'no_telepon' => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("Created assesi record for user (Assesi ID: {$assesi->id})");
        } else {
            $this->command->info("User already has assesi record (Assesi ID: {$assesi->id})");
        }

        // Check if APL-01 form already exists
        $existingApl01 = FormApl01::where('user_id', $user->id)->first();
        
        if ($existingApl01) {
            $this->command->info("APL-01 form already exists for this user (ID: {$existingApl01->id})");
            return;
        }

        // Get the user's active assessment (assesi variable already set above)

        $activeAssessment = Assesment_Asesi::where('assesi_id', $assesi->id)
            ->whereIn('status', ['mengerjakan', 'belum'])
            ->with('assesment.schema')
            ->first();

        if (!$activeAssessment) {
            $this->command->warn('No active assessment found for this asesi. Creating basic APL-01...');
        }

        // Create APL-01 form
        $apl01Data = [
            'user_id' => $user->id,
            'nama_lengkap' => $user->name,
            'no_ktp' => '3171234567890123',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'jenis_kelamin' => 'L',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Raya Bogor KM 17, Cijantung, Jakarta Timur',
            'kode_pos' => '13770',
            'no_telepon_rumah' => '021-87654321',
            'no_hp' => '081234567890',
            'kualifikasi_pendidikan' => 'SMK',
            'nama_institusi_pendidikan' => 'SMK Negeri 24 Jakarta',
            'bidang_studi' => 'Rekayasa Perangkat Lunak',
            'tahun_lulus' => '2024',
            'nama_perusahaan_instansi' => 'PT. Tech Solutions Indonesia',
            'jabatan' => 'Junior Developer',
            'alamat_perusahaan' => 'Jl. Sudirman No. 123, Jakarta Pusat',
            'kode_pos_perusahaan' => '10220',
            'no_telepon_perusahaan' => '021-12345678',
            'fax_perusahaan' => '021-12345679',
            'email_perusahaan' => 'hr@techsolutions.co.id',
        ];

        // Add schema-specific data if available
        if ($activeAssessment && $activeAssessment->assesment && $activeAssessment->assesment->schema) {
            $schema = $activeAssessment->assesment->schema;
            $apl01Data['skema_sertifikasi'] = $schema->nama_skema;
            $apl01Data['tujuan_asesmen'] = 'Sertifikasi Kompetensi';
        } else {
            $apl01Data['skema_sertifikasi'] = 'Rekayasa Perangkat Lunak';
            $apl01Data['tujuan_asesmen'] = 'Sertifikasi Kompetensi';
        }

        // Create the APL-01 record
        $formApl01 = FormApl01::create($apl01Data);

        // Create sample APL-01 attachments
        $attachments = [
            [
                'form_apl01_id' => $formApl01->id,
                'nama_dokumen' => 'foto_ktp.pdf',
                'file_path' => '/uploads/apl01/foto_ktp_' . $user->id . '.pdf',
                'description' => 'Foto KTP',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'form_apl01_id' => $formApl01->id,
                'nama_dokumen' => 'ijazah.pdf',
                'file_path' => '/uploads/apl01/ijazah_' . $user->id . '.pdf',
                'description' => 'Ijazah SMK',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'form_apl01_id' => $formApl01->id,
                'nama_dokumen' => 'sertifikat_keahlian.pdf',
                'file_path' => '/uploads/apl01/sertifikat_' . $user->id . '.pdf',
                'description' => 'Sertifikat Keahlian Programming',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'form_apl01_id' => $formApl01->id,
                'nama_dokumen' => 'cv.pdf',
                'file_path' => '/uploads/apl01/cv_' . $user->id . '.pdf',
                'description' => 'Curriculum Vitae',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'form_apl01_id' => $formApl01->id,
                'nama_dokumen' => 'portofolio.pdf',
                'file_path' => '/uploads/apl01/portofolio_' . $user->id . '.pdf',
                'description' => 'Portofolio Project Web',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // Insert attachments using DB facade to avoid model issues
        DB::table('bukti_dokumen_formapl01')->insert($attachments);

        $this->command->info("Created APL-01 form for user {$user->name} (Form ID: {$formApl01->id})");
        $this->command->info("Created " . count($attachments) . " APL-01 attachments");
        $this->command->info("APL-01 form is now available at /api/formApl01");
        $this->command->info("APL-01 attachments are now available at /api/formApl01/attachments-as-bukti");
    }
}
