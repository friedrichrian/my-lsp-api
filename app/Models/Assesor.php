<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AssesorAttachment;
use App\Models\User;

class Assesor extends Model
{
    protected $table = 'assesor';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'no_registrasi',
        'jenis_kelamin',
        'email',
        'no_telepon',
        'kompetensi'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(AssesorAttachment::class);
    }
}
