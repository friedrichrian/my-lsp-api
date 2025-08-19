<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asesmen extends Model
{
    use HasFactory;

    protected $table = 'asesmen';

    protected $fillable = [
        'schema_id',
        'asesor_id',
        'asesi_id',
        'tanggal_asesmen',
        'waktu_asesmen',
        'tuk',
        'lokasi_tuk',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'tanggal_asesmen' => 'date',
    ];

    public function schema(): BelongsTo
    {
        return $this->belongsTo(Schema::class);
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function asesi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesi_id');
    }

    public function formAK01(): HasOne
    {
        return $this->hasOne(FormAK01::class);
    }

    public function formAK02(): HasOne
    {
        return $this->hasOne(FormAK02::class);
    }

    public function formAK03(): HasOne
    {
        return $this->hasOne(FormAK03::class);
    }

    public function formAK05(): HasOne
    {
        return $this->hasOne(FormAK05::class);
    }

    public function formIA05(): HasOne
    {
        return $this->hasOne(FormIA05::class);
    }
}