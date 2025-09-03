<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormAk01Submission extends Model
{
    use HasFactory;

    protected $table = 'form_ak01_submissions';

    protected $fillable = [
        'assesment_id',
        'assesi_id',
        'skema_id',
        'assesor_id',
        'submission_date',
    ];

    /**
     * Relasi ke Assesi
     */
    public function assesi()
    {
        return $this->belongsTo(Assesi::class, 'assesi_id');
    }

    /**
     * Relasi ke Skema (schemas table)
     */
    public function skema()
    {
        return $this->belongsTo(Schema::class, 'skema_id');
    }

    /**
     * Relasi ke Assesor
     */
    public function assesor()
    {
        return $this->belongsTo(Assesor::class, 'assesor_id');
    }

    /**
     * Relasi ke attachments (ak01_attachments)
     */
    public function attachments()
    {
        return $this->hasMany(FormAk01Attachment::class, 'submission_id');
    }
}
