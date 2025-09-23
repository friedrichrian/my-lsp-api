<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assesi;
use App\Models\User;

class AssesiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get asesi users (role value is 'assesi' as defined in UserSeeder)
        $asesiUsers = User::where('role', 'assesi')->get();

        $asesiData = [
            [
                'username' => 'asesi_ph1',
                'nama_lengkap' => 'Dewi Kartika',
                'no_ktp' => '3201234567890126',
                'tempat_lahir' => 'Yogyakarta',
                'tanggal_lahir' => '2005-05-18',
                'jenis_kelamin' => 'Perempuan',
                'alamat' => 'Jl. Malioboro No. 321, Yogyakarta',
                'kode_pos' => '55113',
                'no_telepon' => '081234567893',
                'kualifikasi_pendidikan' => 'SMK'
            ],
            [
                'username' => 'asesi_bsn1',
                'nama_lengkap' => 'Rina Sari',
                'no_ktp' => '3201234567890127',
                'tempat_lahir' => 'Medan',
                'tanggal_lahir' => '2005-09-25',
                'jenis_kelamin' => 'Perempuan',
                'alamat' => 'Jl. Gatot Subroto No. 654, Medan',
                'kode_pos' => '20112',
                'no_telepon' => '081234567894',
                'kualifikasi_pendidikan' => 'SMK'
            ],
            [
                'username' => 'asesi_ulw1',
                'nama_lengkap' => 'Andi Wijaya',
                'no_ktp' => '3201234567890128',
                'tempat_lahir' => 'Bali',
                'tanggal_lahir' => '2005-11-30',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Jl. Kuta No. 987, Bali',
                'kode_pos' => '80361',
                'no_telepon' => '081234567895',
                'kualifikasi_pendidikan' => 'SMK'
            ]
        ];

        foreach ($asesiUsers as $index => $user) {
            if (isset($asesiData[$index])) {
                Assesi::create([
                    'user_id' => $user->id,
                    'jurusan_id' => $user->jurusan_id,
                    'nama_lengkap' => $asesiData[$index]['nama_lengkap'],
                    'no_ktp' => $asesiData[$index]['no_ktp'],
                    'tempat_lahir' => $asesiData[$index]['tempat_lahir'],
                    'tanggal_lahir' => $asesiData[$index]['tanggal_lahir'],
                    'jenis_kelamin' => $asesiData[$index]['jenis_kelamin'],
                    'alamat' => $asesiData[$index]['alamat'],
                    'kode_pos' => $asesiData[$index]['kode_pos'],
                    'no_telepon' => $asesiData[$index]['no_telepon'],
                    'kualifikasi_pendidikan' => $asesiData[$index]['kualifikasi_pendidikan']
                ]);
            }
        }
    }
}
