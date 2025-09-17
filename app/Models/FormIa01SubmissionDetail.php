<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormIa01SubmissionDetail extends Model
{
    use HasFactory;

    protected $table = 'ia_01_submission_details';
    protected $fillable = [
        'submission_id',
        'unit_ke',
        'kode_unit',
        'elemen_id',
        'kuk_id', // Tambahkan kolom ini
        'skkni'
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormIa01Submission::class, 'submission_id');
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(Element::class, 'elemen_id');
    }

    public function kuk(): BelongsTo
    {
        return $this->belongsTo(KukSkkni::class, 'kuk_id');
    }

    public function penilaianLanjut(): HasMany
    {
        return $this->hasMany(FormIa01PenilaianLanjut::class, 'submission_detail_id');
    }
}