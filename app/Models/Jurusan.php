<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $table = 'jurusan';

    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
        'jenjang',
        'deskripsi',
        'status',
    ];

    public function assesi()
    {
        return $this->hasMany(Assesi::class);
    }

    public function schemas()
    {
        return $this->hasMany(Schema::class);
    }
}
