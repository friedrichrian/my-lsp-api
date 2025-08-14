<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormApl02Attachments extends Model
{
    protected $table = 'apl02_attachments';
    protected $fillable = [
        'submission_id',
        'bukti_id',
    ];

    public function submission()
    {
        return $this->belongsTo(FormApl02Submission::class, 'submission_id');
    }

    public function bukti()
    {
        return $this->belongsTo(BuktiDokumenAssesi::class, 'bukti_id');
    }
}
