<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assesor;
use App\Models\User;

class AssesorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get asesor users
        $asesorUsers = User::where('role', 'asesor')->get();

        $asesorData = [
            [
                'username' => 'asesor_rpl1',
                'nama_lengkap' => 'Prof. Dr. Ir. Bambang Sutrisno, M.T.',
                'no_registrasi' => 'ASR-RPL-001',
                'jenis_kelamin' => 'Laki-laki',
                'no_telepon' => '081234567900',
                'kompetensi' => 'Rekayasa Perangkat Lunak, Database Management, Web Development'
            ],
            [
                'username' => 'asesor_tbg1',
                'nama_lengkap' => 'Chef. Dr. Sari Dewi, S.Pd., M.Pd.',
                'no_registrasi' => 'ASR-TBG-001',
                'jenis_kelamin' => 'Perempuan',
                'no_telepon' => '081234567901',
                'kompetensi' => 'Tata Boga, Kuliner Indonesia, Food Safety, Menu Planning'
            ],
            [
                'username' => 'asesor_ph1',
                'nama_lengkap' => 'Dr. Andi Wijaya, S.Pd., M.Par.',
                'no_registrasi' => 'ASR-PH-001',
                'jenis_kelamin' => 'Laki-laki',
                'no_telepon' => '081234567902',
                'kompetensi' => 'Manajemen Perhotelan, Front Office, Housekeeping, Food & Beverage'
            ],
            [
                'username' => 'asesor_bsn1',
                'nama_lengkap' => 'Ir. Rina Sari, S.T., M.Ds.',
                'no_registrasi' => 'ASR-BSN-001',
                'jenis_kelamin' => 'Perempuan',
                'no_telepon' => '081234567903',
                'kompetensi' => 'Desain Busana, Pattern Making, Fashion Design, Textile Technology'
            ],
            [
                'username' => 'asesor_ulw1',
                'nama_lengkap' => 'Dr. Budi Santoso, S.Pd., M.Par.',
                'no_registrasi' => 'ASR-ULW-001',
                'jenis_kelamin' => 'Laki-laki',
                'no_telepon' => '081234567904',
                'kompetensi' => 'Manajemen Pariwisata, Tour Guide, Event Management, Customer Service'
            ]
        ];

        foreach ($asesorUsers as $index => $user) {
            if (isset($asesorData[$index])) {
                Assesor::create([
                    'user_id' => $user->id,
                    'nama_lengkap' => $asesorData[$index]['nama_lengkap'],
                    'no_registrasi' => $asesorData[$index]['no_registrasi'],
                    'jenis_kelamin' => $asesorData[$index]['jenis_kelamin'],
                    'email' => $user->email,
                    'no_telepon' => $asesorData[$index]['no_telepon'],
                    'kompetensi' => $asesorData[$index]['kompetensi']
                ]);
            }
        }
    }
}
