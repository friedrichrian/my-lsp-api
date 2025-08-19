<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAK01 extends Model
{
    use HasFactory;

    protected $table = 'form_ak01';

    protected $fillable = [
        'asesmen_id',
        'verifikasi_portofolio',
        'reviu_produk',
        'observasi_langsung',
        'kegiatan_terstruktur',
        'pertanyaan_lisan',
        'pertanyaan_tertulis',
        'wawancara',
        'metode_lainnya',
        'pernyataan_asesi',
        'pernyataan_asesor',
        'persetujuan_asesi',
        'tanggal_tanda_tangan_asesor',
        'tanggal_tanda_tangan_asesi'
    ];

    protected $casts = [
        'verifikasi_portofolio' => 'boolean',
        'reviu_produk' => 'boolean',
        'observasi_langsung' => 'boolean',
        'kegiatan_terstruktur' => 'boolean',
        'pertanyaan_lisan' => 'boolean',
        'pertanyaan_tertulis' => 'boolean',
        'wawancara' => 'boolean',
        'tanggal_tanda_tangan_asesor' => 'datetime',
        'tanggal_tanda_tangan_asesi' => 'datetime'
    ];

    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class);
    }
}