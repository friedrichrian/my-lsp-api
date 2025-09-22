<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Assesi;
use App\Models\BuktiDokumenAssesi;

class BuktiDokumenAsesiRpl1Seeder extends Seeder
{
    /**
     * Seed bukti dokumen for a specific asesi account.
     */
    public function run(): void
    {
        $email = 'asesi.rpl1@student.smkn24.ac.id';

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->command?->warn("User with email {$email} not found. Skipping BuktiDokumenAsesiRpl1Seeder.");
            return;
        }

        $asesi = Assesi::where('user_id', $user->id)->first();
        if (!$asesi) {
            $this->command?->warn("Assesi record for {$email} not found. Make sure AssesiSeeder has run. Skipping.");
            return;
        }

        $bukti = [
            [
                'nama_dokumen' => 'KTP - '.$asesi->nama_lengkap,
                'description'  => 'KTP sebagai bukti identitas '.$asesi->nama_lengkap,
                'file_path'    => 'bukti_dokumen/'.$asesi->id.'/ktp.pdf',
            ],
            [
                'nama_dokumen' => 'Ijazah Terakhir - '.$asesi->nama_lengkap,
                'description'  => 'Ijazah terakhir sebagai bukti pendidikan '.$asesi->nama_lengkap,
                'file_path'    => 'bukti_dokumen/'.$asesi->id.'/ijazah_terakhir.pdf',
            ],
            [
                'nama_dokumen' => 'Sertifikat Pelatihan - '.$asesi->nama_lengkap,
                'description'  => 'Sertifikat pelatihan terkait kompetensi',
                'file_path'    => 'bukti_dokumen/'.$asesi->id.'/sertifikat_pelatihan.pdf',
            ],
            [
                'nama_dokumen' => 'CV/Resume - '.$asesi->nama_lengkap,
                'description'  => 'CV/Resume '.$asesi->nama_lengkap,
                'file_path'    => 'bukti_dokumen/'.$asesi->id.'/cv_resume.pdf',
            ],
        ];

        foreach ($bukti as $item) {
            BuktiDokumenAssesi::firstOrCreate(
                [
                    'assesi_id'   => $asesi->id,
                    'description' => $item['description'],
                ],
                [
                    'nama_dokumen' => $item['nama_dokumen'],
                    'file_path'    => $item['file_path'],
                ]
            );
        }

        $this->command?->info("Seeded bukti dokumen for {$email} (assesi_id: {$asesi->id}).");
    }
}
