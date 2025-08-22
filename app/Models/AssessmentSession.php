<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentSession extends Model
{
    protected $fillable = [
        'judul_skema',
        'nomor_skema',
        'tuk',
        'assesor_id',
        'assesi_id',
        'tanggal_asesmen',
        'hasil_asesmen',
        'catatan_asesor',
        'status'
    ];

    protected $casts = [
        'tanggal_asesmen' => 'date'
    ];

    public function assesor(): BelongsTo
    {
        return $this->belongsTo(Assesor::class);
    }

    public function assesi(): BelongsTo
    {
        return $this->belongsTo(Assesi::class);
    }

    public function observationGroups(): HasMany
    {
        return $this->hasMany(ObservationGroup::class);
    }
}
