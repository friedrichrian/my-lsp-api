<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ak03Submission extends Model
{
    protected $fillable = ['assesment_asesi_id','catatan_tambahan'];

    public function assesmentAsesi()
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }

    public function details()
    {
        return $this->hasMany(Ak03SubmissionDetail::class, 'ak03_submission_id');
    }
}
