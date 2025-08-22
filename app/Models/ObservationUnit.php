<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObservationUnit extends Model
{
    protected $fillable = [
        'observation_group_id',
        'unit_id'
    ];

    public function observationGroup(): BelongsTo
    {
        return $this->belongsTo(ObservationGroup::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function observationElements(): HasMany
    {
        return $this->hasMany(ObservationElement::class);
    }
}
