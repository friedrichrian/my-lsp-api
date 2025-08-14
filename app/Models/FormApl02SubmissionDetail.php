<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormApl02SubmissionDetail extends Model
{
    protected $table = 'apl02_submission_details';

    protected $fillable = [
        'submission_id',
        'unit_ke',
        'kode_unit',
        'elemen_id',
        'kompetensinitas',
    ];

    public function submission()
    {
        return $this->belongsTo(FormApl02Submission::class, 'submission_id');
    }

    public function element()
    {
        return $this->belongsTo(Element::class, 'elemen_id');
    }

    public function attachments()
    {
        return $this->hasMany(FormApl02Attachments::class, 'submission_detail_id');
    }
}
