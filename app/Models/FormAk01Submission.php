<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormAk01Submission extends Model
{
    use HasFactory;

    protected $table = 'form_ak01_submissions';

    protected $fillable = [
        'assesment_asesi_id',
        'submission_date',
        'ttd_assesor',
        'status'
    ];


    /**
     * Relasi ke Skema (schemas table)
     */
    public function skema()
    {
        return $this->belongsTo(Schema::class, 'skema_id');
    }

    public function assesmentAsesi(){
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }


    /**
     * Relasi ke attachments (ak01_attachments)
     */
    public function attachments()
    {
        return $this->hasMany(FormAk01Attachment::class, 'submission_id');
    }
}
