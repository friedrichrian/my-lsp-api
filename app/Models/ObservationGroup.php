<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObservationGroup extends Model
{
    protected $fillable = [
        'assessment_session_id',
        'nama_kelompok',
        'umpan_balik'
    ];

    public function assessmentSession(): BelongsTo
    {
        return $this->belongsTo(AssessmentSession::class);
    }

    public function observationUnits(): HasMany
    {
        return $this->hasMany(ObservationUnit::class);
    }
}
