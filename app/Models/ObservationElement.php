<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObservationElement extends Model
{
    protected $fillable = [
        'observation_unit_id',
        'element_id'
    ];

    public function observationUnit(): BelongsTo
    {
        return $this->belongsTo(ObservationUnit::class);
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(Element::class);
    }

    public function observationKuks(): HasMany
    {
        return $this->hasMany(ObservationKuk::class);
    }
}
