<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormIa01PenilaianLanjut extends Model
{
    use HasFactory;

    protected $table = 'ia_01_penilaian_lanjut';
    protected $fillable = [
        'submission_detail_id',
        'teks_penilaian'
    ];

    public function submissionDetail(): BelongsTo
    {
        return $this->belongsTo(FormIa01SubmissionDetail::class, 'submission_detail_id');
    }
}