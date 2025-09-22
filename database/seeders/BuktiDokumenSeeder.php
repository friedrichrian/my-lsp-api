<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BuktiDokumenAssesi;
use App\Models\Assesi;

class BuktiDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all asesi
        $asesiList = Assesi::all();

        $buktiTypes = [
            'KTP',
            'Ijazah Terakhir',
            'Sertifikat Pelatihan',
            'Portfolio/Karya',
            'Surat Keterangan Kerja',
            'Sertifikat Kompetensi Lainnya',
            'CV/Resume',
            'Pas Foto'
        ];

        foreach ($asesiList as $asesi) {
            foreach ($buktiTypes as $type) {
                $desc = $type . ' sebagai bukti kompetensi untuk ' . $asesi->nama_lengkap;
                $file = 'bukti_dokumen/' . $asesi->id . '/' . strtolower(str_replace(' ', '_', $type)) . '.pdf';

                BuktiDokumenAssesi::firstOrCreate(
                    [
                        'assesi_id' => $asesi->id,
                        'description' => $desc,
                    ],
                    [
                        'nama_dokumen' => $type . ' - ' . $asesi->nama_lengkap,
                        'file_path' => $file,
                    ]
                );
            }
        }
    }
}
