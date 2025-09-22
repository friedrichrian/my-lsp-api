<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ak04Submission extends Model
{
    use HasFactory;

    protected $table = 'ak04_submissions';

    protected $fillable = [
        'assesment_asesi_id',
        'nama_asesor',
        'nama_asesi',
        'tanggal_asesmen',
        'skema_sertifikasi',
        'no_skema_sertifikasi',
        'alasan_banding',
        'tanggal_approve',
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
        'tanggal_asesmen' => 'date',
        'tanggal_approve' => 'date',
    ];

    public function assesmentAsesi()
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }
}
