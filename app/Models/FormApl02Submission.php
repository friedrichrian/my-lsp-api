<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormApl02Submission extends Model
{
    
    protected $fillable = [
        'ttd_asesi',
        'ttd_assesor',
        'submission_date',
        'assesi_id',
        'assesment_asesi_id',
        'ttd_assesor'
    ];

    public function details(){
        return $this->hasMany(FormApl02SubmissionDetail::class, 'submission_id')
                    ->with('attachments.bukti'); // langsung eager load
    }

    

    public function assesment_asesi()
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }

}
