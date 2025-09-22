<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assesment_Asesi extends Model
{
    protected $table = 'assesment_asesi';
    protected $fillable = [
        'assesment_id',
        'assesi_id',
        'status'
    ];

    /**
     * Relasi ke Assesment
     */
    public function assesment(): BelongsTo
    {
        return $this->belongsTo(Assesment::class, 'assesment_id');
    }

    /**
     * Relasi ke Asesi (peserta)
     */
    public function assesi(): BelongsTo
    {
        return $this->belongsTo(Assesi::class, 'assesi_id', 'id');
    }

    /**
     * Relasi ke Form APL02
     */
    public function formApl02(): HasOne
    {
        return $this->hasOne(FormApl02Submission::class, 'assesment_asesi_id');
    }

    /**
     * Relasi ke Form AK01
     */
    public function formAk01(): HasOne
    {
        return $this->hasOne(FormAk01Submission::class, 'assesment_asesi_id');
    }

    /**
     * Relasi ke Form IA01 (bisa banyak submission)
     */
    public function ia01Submissions(): HasMany
    {
        return $this->hasMany(FormIa01Submission::class, 'assesment_asesi_id');
    }
}
