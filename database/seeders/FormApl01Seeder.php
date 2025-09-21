<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormApl01;
use App\Models\FormApl01SertificationData;
use App\Models\Assesi;

class FormApl01Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some asesi
        $asesiList = Assesi::with('user')->take(3)->get();

        foreach ($asesiList as $asesi) {
            // Create Form APL01
            $formApl01 = FormApl01::create([
                'user_id' => $asesi->user_id,
                'nama_lengkap' => $asesi->nama_lengkap,
                'no_ktp' => $asesi->no_ktp,
                'tanggal_lahir' => $asesi->tanggal_lahir,
                'tempat_lahir' => $asesi->tempat_lahir,
                'jenis_kelamin' => $asesi->jenis_kelamin,
                'kebangsaan' => 'Indonesia',
                'alamat_rumah' => $asesi->alamat,
                'kode_pos' => $asesi->kode_pos,
                'no_telepon_rumah' => $asesi->no_telepon,
                'no_telepon_kantor' => null,
                'no_telepon' => $asesi->no_telepon,
                'email' => $asesi->user->email,
                'kualifikasi_pendidikan' => $asesi->kualifikasi_pendidikan,
                'nama_institusi' => 'SMK Negeri 24 Jakarta',
                'jabatan' => 'Siswa',
                'alamat_kantor' => null,
                'kode_pos_kantor' => null,
                'fax_kantor' => null,
                'email_kantor' => null,
                'status' => 'pending'
            ]);

            // Create certification data
            FormApl01SertificationData::create([
                'form_apl01_id' => $formApl01->id,
                'schema_id' => $asesi->jurusan_id, // Assuming schema_id matches jurusan_id
                'tujuan_asesmen' => 'Mendapatkan sertifikat kompetensi sesuai dengan skema yang dipilih'
            ]);
        }
    }
}
