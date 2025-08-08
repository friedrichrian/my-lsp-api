<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assesor extends Model
{
    protected $table = 'assesor';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'no_registrasi',
        'email',
        'no_telepon',
        'kompetensi'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
