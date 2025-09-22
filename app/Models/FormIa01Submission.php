<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormIa01Submission extends Model
{
    use HasFactory;

    protected $table = 'form_ia01_submissions'; // 👈 konsistensi nama tabel (hapus underscore setelah ia)
    protected $fillable = [
        'assesment_asesi_id',
        'submission_date'
    ];

    protected $casts = [
        'submission_date' => 'datetime'
    ];

    /**
     * Relasi ke Assesment_Asesi
     */
    public function assesmentAsesi(): BelongsTo
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }

    /**
     * Relasi ke detail IA01
     */
    public function details(): HasMany
    {
        return $this->hasMany(FormIa01SubmissionDetail::class, 'submission_id');
    }
}
