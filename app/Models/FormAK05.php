<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAK05 extends Model
{
    use HasFactory;

    protected $table = 'form_ak05';

    protected $fillable = [
        'asesmen_id',
        'rekomendasi',
        'keterangan',
        'aspek_negatif_positif',
        'pencatatan_penolakan',
        'saran_perbaikan',
        'no_reg_asesor',
        'tanggal_tanda_tangan_asesor'
    ];

    protected $casts = [
        'tanggal_tanda_tangan_asesor' => 'datetime'
    ];

    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class);
    }
}