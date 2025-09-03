<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormApl02Submission extends Model
{
    
    protected $fillable = [
        'skema_id',
        'submission_date',
        'assesi_id',
    ];

    public function assesi()
    {
        return $this->belongsTo(Assesi::class, 'assesi_id');
    }

    public function element(){
        return $this->belongsTo(Element::class, 'elements_id');
    }

    public function details(){
        return $this->hasMany(FormApl02SubmissionDetail::class, 'submission_id');
    }

    public function assesment_asesi()
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }

}
