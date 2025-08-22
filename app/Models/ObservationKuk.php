<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationKuk extends Model
{
    protected $fillable = [
        'observation_element_id',
        'kriteria_untuk_kerja_id',
        'ya',
        'tidak',
        'standar_industri',
        'penilaian_lanjut',
        'catatan'
    ];

    protected $casts = [
        'ya' => 'boolean',
        'tidak' => 'boolean'
    ];

    public function observationElement(): BelongsTo
    {
        return $this->belongsTo(ObservationElement::class);
    }

    public function kriteriaUntukKerja(): BelongsTo
    {
        return $this->belongsTo(KriteriaUntukKerja::class);
    }
}
