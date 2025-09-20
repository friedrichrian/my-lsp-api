<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormApl01 extends Model
{
    protected $table = 'form_apl01';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'no_ktp',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
        'kebangsaan',
        'alamat_rumah',
        'kode_pos',
        'no_telepon_rumah',
        'no_telepon_kantor',
        'no_telepon',
        'email',
        'kualifikasi_pendidikan',
        'nama_institusi',
        'jabatan',
        'alamat_kantor',
        'kode_pos_kantor',
        'fax_kantor',
        'email_kantor',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(FormApl01Attachments::class, 'form_apl01_id');
    }

    public function sertificationData()
    {
        return $this->hasOne(FormApl01SertificationData::class, 'form_apl01_id');
    }
}
