<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ak02SubmissionDetail extends Model
{
    protected $table = 'ak02_submission_details';

    protected $fillable = [
        'ak02_submission_id',
        'unit_id',
        'rekomendasi_hasil',
        'tindak_lanjut',
        'komentar_asesor',
    ];

    public function submission()
    {
        return $this->belongsTo(Ak02Submission::class, 'ak02_submission_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function bukti()
    {
        return $this->hasMany(Ak02DetailBukti::class, 'ak02_detail_id');
    }
}
