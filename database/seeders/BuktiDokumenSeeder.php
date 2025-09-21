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
            // Create 3-5 bukti dokumen per asesi
            $randomCount = rand(3, 5);
            $selectedBukti = array_rand($buktiTypes, $randomCount);
            
            if (!is_array($selectedBukti)) {
                $selectedBukti = [$selectedBukti];
            }

            foreach ($selectedBukti as $index) {
                BuktiDokumenAssesi::create([
                    'assesi_id' => $asesi->id,
                    'nama_dokumen' => $buktiTypes[$index] . ' - ' . $asesi->nama_lengkap,
                    'file_path' => 'bukti_dokumen/' . $asesi->id . '/' . strtolower(str_replace(' ', '_', $buktiTypes[$index])) . '.pdf',
                    'description' => $buktiTypes[$index] . ' sebagai bukti kompetensi untuk ' . $asesi->nama_lengkap
                ]);
            }
        }
    }
}
