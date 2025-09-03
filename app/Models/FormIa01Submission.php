<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormIa01Submission extends Model
{
    use HasFactory;

    protected $table = 'form_ia_01_submissions';
    protected $fillable = [
        'assesi_id',
        'skema_id',
        'assesor_id',
        'submission_date',
        'assesment_asesi_id'
    ];

    protected $casts = [
        'submission_date' => 'datetime'
    ];

    public function assesi(): BelongsTo
    {
        return $this->belongsTo(Assesi::class);
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Schema::class, 'skema_id');
    }

    public function assesor(): BelongsTo
    {
        return $this->belongsTo(Assesor::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(FormIa01SubmissionDetail::class, 'submission_id');
    }
}