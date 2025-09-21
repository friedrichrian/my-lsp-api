<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ak03SubmissionDetail extends Model
{
    protected $fillable = ['ak03_submission_id','komponen_id','hasil','catatan_asesi'];

    public function submission()
    {
        return $this->belongsTo(Ak03Submission::class, 'ak03_submission_id');
    }

    public function komponen()
    {
        return $this->belongsTo(Komponen::class, 'komponen_id');
    }
}
