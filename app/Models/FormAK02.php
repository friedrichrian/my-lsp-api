<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormAK02 extends Model
{
    use HasFactory;

    protected $table = 'form_ak02';
    
    protected $fillable = [
        'asesmen_id',
        'rekomendasi',
        'tindak_lanjut',
        'komentar_asesor',
        'tanggal_tanda_tangan_asesor',
        'tanggal_tanda_tangan_asesi'
    ];

    protected $casts = [
        'tanggal_tanda_tangan_asesor' => 'datetime',
        'tanggal_tanda_tangan_asesi' => 'datetime'
    ];

    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class);
    }

    public function bukti(): HasMany
    {
        return $this->hasMany(FormAK02Bukti::class);
    }
}