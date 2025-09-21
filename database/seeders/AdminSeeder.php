<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin users
        $superAdminUser = User::where('username', 'baradika')->first();
        $adminUser = User::where('username', 'admin1')->first();

        // Check if users exist
        if (!$superAdminUser) {
            $this->command->error('User "baradika" not found. Please run UserSeeder first.');
            return;
        }

        if (!$adminUser) {
            $this->command->error('User "admin1" not found. Please run UserSeeder first.');
            return;
        }

        $admins = [
            [
                'user_id' => $superAdminUser->id,
                'nama_lengkap' => 'Dr. Baradika',
                'email' => 'baradika@lsp-smkn24.com',
                'no_hp' => '081234567890',
                'role' => 'superadmin',
                'status' => 'aktif'
            ],
            [
                'user_id' => $adminUser->id,
                'nama_lengkap' => 'Admin Utama',
                'email' => 'admin1@lsp-smkn24.com',
                'no_hp' => '081234567891',
                'role' => 'admin',
                'status' => 'aktif'
            ]
        ];

        foreach ($admins as $admin) {
            Admin::create($admin);
        }
    }
}
