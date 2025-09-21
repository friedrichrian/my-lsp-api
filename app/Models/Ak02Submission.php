<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ak02Submission extends Model
{
    protected $table = 'ak02_submissions';

    protected $fillable = [
        'assesment_asesi_id',
        'ttd_asesi',
        'ttd_asesor',
    ];

    public function assesmentAsesi()
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }

    public function details()
    {
        return $this->hasMany(Ak02SubmissionDetail::class, 'ak02_submission_id');
    }
}
