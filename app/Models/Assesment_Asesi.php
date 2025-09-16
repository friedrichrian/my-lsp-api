<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assesment_Asesi extends Model
{
    //
    protected $table = 'assesment_asesi';
    protected $fillable = [
        'assesment_id',
        'assesi_id',
        'status'
    ];

    public function assesment()
    {
        return $this->belongsTo(Assesment::class, 'assesment_id');
    }

    public function form_apl_02()
    {
        return $this->hasOne(Form_Apl02_Submission::class, 'assesment_asesi_id');
    }

    public function asesi(){
        return $this->belongsTo(Assesi::class, 'assesi_id', 'id');
    }

    public function form_ak01_submissions()
    {
        return $this->hasOne(FormAk01Submission::class, 'assesment_asesi_id');
    }

}
