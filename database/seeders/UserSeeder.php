<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin users
        $adminUsers = [
            [
                'username' => 'baradika',
                'email' => 'baradika@lsp-smkn24.com',
                'password' => Hash::make('1sampai3'),
                'role' => 'admin',
                'jurusan_id' => null
            ],
            [
                'username' => 'admin1',
                'email' => 'admin1@lsp-smkn24.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'jurusan_id' => null
            ]
        ];

        // Asesi users
        $asesiUsers = [
            [
                'username' => 'asesi_rpl1',
                'email' => 'asesi.rpl1@student.smkn24.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'assesi',
                'jurusan_id' => 1 // RPL
            ],
            [
                'username' => 'asesi_rpl2',
                'email' => 'asesi.rpl2@student.smkn24.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'assesi',
                'jurusan_id' => 1 // RPL
            ],
            [
                'username' => 'asesi_tbg1',
                'email' => 'assesi.tbg1@student.smkn24.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'assesi',
                'jurusan_id' => 2 // TBG
            ],
            [
                'username' => 'assesi_ph1',
                'email' => 'assesi.ph1@student.smkn24.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'assesi',
                'jurusan_id' => 3 // PH
            ],
            [
                'username' => 'assesi_bsn1',
                'email' => 'assesi.bsn1@student.smkn24.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'assesi',
                'jurusan_id' => 4 // BSN
            ],
            [
                'username' => 'assesi_ulw1',
                'email' => 'assesi.ulw1@student.smkn24.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'assesi',
                'jurusan_id' => 5 // ULW
            ]
        ];

        // Asesor users
        $asesorUsers = [
            [
                'username' => 'asesor_rpl1',
                'email' => 'asesor.rpl1@lsp-smkn24.com',
                'password' => Hash::make('password123'),
                'role' => 'asesor',
                'jurusan_id' => 1 // RPL
            ],
            [
                'username' => 'asesor_tbg1',
                'email' => 'asesor.tbg1@lsp-smkn24.com',
                'password' => Hash::make('password123'),
                'role' => 'asesor',
                'jurusan_id' => 2 // TBG
            ],
            [
                'username' => 'asesor_ph1',
                'email' => 'asesor.ph1@lsp-smkn24.com',
                'password' => Hash::make('password123'),
                'role' => 'asesor',
                'jurusan_id' => 3 // PH
            ],
            [
                'username' => 'asesor_bsn1',
                'email' => 'asesor.bsn1@lsp-smkn24.com',
                'password' => Hash::make('password123'),
                'role' => 'asesor',
                'jurusan_id' => 4 // BSN
            ],
            [
                'username' => 'asesor_ulw1',
                'email' => 'asesor.ulw1@lsp-smkn24.com',
                'password' => Hash::make('password123'),
                'role' => 'asesor',
                'jurusan_id' => 5 // ULW
            ]
        ];

        // Create all users
        foreach ($adminUsers as $user) {
            User::create($user);
        }

        foreach ($asesiUsers as $user) {
            User::create($user);
        }

        foreach ($asesorUsers as $user) {
            User::create($user);
        }
    }
}
