<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAK02Bukti extends Model
{
    use HasFactory;

    protected $table = 'form_ak02_bukti';
    
    protected $fillable = [
        'form_ak02_id',
        'unit_id',
        'observasi_demonstrasi',
        'portofolio',
        'pernyataan_pihak_ketiga',
        'pertanyaan_lisan',
        'pertanyaan_tertulis',
        'proyek_kerja',
        'lainnya',
        'keterangan_lainnya'
    ];

    protected $casts = [
        'observasi_demonstrasi' => 'boolean',
        'portofolio' => 'boolean',
        'pernyataan_pihak_ketiga' => 'boolean',
        'pertanyaan_lisan' => 'boolean',
        'pertanyaan_tertulis' => 'boolean',
        'proyek_kerja' => 'boolean',
        'lainnya' => 'boolean'
    ];

    public function formAK02(): BelongsTo
    {
        return $this->belongsTo(FormAK02::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}