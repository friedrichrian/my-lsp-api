<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormIA05 extends Model
{
    use HasFactory;

    protected $table = 'form_ia05';

    protected $fillable = [
        'asesmen_id',
        'umpan_balik',
        'no_reg_asesor',
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

    public function jawaban(): HasMany
    {
        return $this->hasMany(FormIA05Jawaban::class);
    }
}