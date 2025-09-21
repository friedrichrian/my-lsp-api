<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jurusan;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusans = [
            [
                'kode_jurusan' => 'RPL',
                'nama_jurusan' => 'Rekayasa Perangkat Lunak',
                'jenjang' => 'SMK',
                'deskripsi' => 'Program keahlian yang mempelajari pengembangan perangkat lunak dan aplikasi komputer'
            ],
            [
                'kode_jurusan' => 'TBG',
                'nama_jurusan' => 'Tata Boga',
                'jenjang' => 'SMK',
                'deskripsi' => 'Program keahlian yang mempelajari pengolahan makanan dan minuman'
            ],
            [
                'kode_jurusan' => 'PH',
                'nama_jurusan' => 'Perhotelan',
                'jenjang' => 'SMK',
                'deskripsi' => 'Program keahlian yang mempelajari manajemen dan operasional perhotelan'
            ],
            [
                'kode_jurusan' => 'BSN',
                'nama_jurusan' => 'Busana',
                'jenjang' => 'SMK',
                'deskripsi' => 'Program keahlian yang mempelajari desain dan pembuatan busana'
            ],
            [
                'kode_jurusan' => 'ULW',
                'nama_jurusan' => 'Usaha Layanan Wisata',
                'jenjang' => 'SMK',
                'deskripsi' => 'Program keahlian yang mempelajari manajemen dan layanan pariwisata'
            ]
        ];

        foreach ($jurusans as $jurusan) {
            Jurusan::create($jurusan);
        }
    }
}
