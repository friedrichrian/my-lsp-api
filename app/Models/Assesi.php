<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assesi extends Model
{
    protected $table = 'assesi';

    protected $fillable = [
        'user_id',
        'jurusan_id',
        'nama_lengkap',
        'no_ktp',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'kode_pos',
        'no_telepon',
        'email',
        'kualifikasi_pendidikan',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
