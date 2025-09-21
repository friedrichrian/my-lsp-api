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
        // Get asesi users
        $asesiUsers = User::where('role', 'asesi')->get();

        $asesiData = [
            [
                'username' => 'asesi_rpl1',
                'nama_lengkap' => 'Ahmad Rizki Pratama',
                'no_ktp' => '3201234567890123',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2005-03-15',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'kode_pos' => '10110',
                'no_telepon' => '081234567890',
                'kualifikasi_pendidikan' => 'SMK'
            ],
            [
                'username' => 'asesi_rpl2',
                'nama_lengkap' => 'Siti Nurhaliza',
                'no_ktp' => '3201234567890124',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2005-07-22',
                'jenis_kelamin' => 'Perempuan',
                'alamat' => 'Jl. Asia Afrika No. 456, Bandung',
                'kode_pos' => '40111',
                'no_telepon' => '081234567891',
                'kualifikasi_pendidikan' => 'SMK'
            ],
            [
                'username' => 'asesi_tbg1',
                'nama_lengkap' => 'Budi Santoso',
                'no_ktp' => '3201234567890125',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => '2005-01-10',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Jl. Diponegoro No. 789, Surabaya',
                'kode_pos' => '60241',
                'no_telepon' => '081234567892',
                'kualifikasi_pendidikan' => 'SMK'
            ],
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
