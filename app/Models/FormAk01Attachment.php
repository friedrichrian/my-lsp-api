<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormAk01Attachment extends Model
{
    use HasFactory;

    protected $table = 'ak01_attachments';

    protected $fillable = [
        'submission_id',
        'description',
    ];

    /**
     * Relasi ke Form AK01 Submission
     */
    public function submission()
    {
        return $this->belongsTo(FormAk01Submission::class, 'submission_id');
    }

}
