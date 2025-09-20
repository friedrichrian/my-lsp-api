<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ak05Submission extends Model
{
    protected $table = 'ak05_submissions';

    protected $fillable = [
        'assesment_asesi_id',
        'keputusan',
        'keterangan',
        'aspek_positif',
        'aspek_negatif',
        'penolakan_hasil',
        'saran_perbaikan',
        'ttd_asesor',
    ];

    public function assesmentAsesi()
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }
}
