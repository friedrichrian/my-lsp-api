<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAK03 extends Model
{
    use HasFactory;

    protected $table = 'form_ak03';

    protected $fillable = [
        'asesmen_id',
        'penjelasan_proses',
        'kesempatan_belajar',
        'diskusi_metoda',
        'penggalian_bukti',
        'demonstrasi_kompetensi',
        'penjelasan_keputusan',
        'umpan_balik',
        'studi_dokumen',
        'jaminan_kerahasiaan',
        'komunikasi_efektif',
        'catatan_tambahan'
    ];

    protected $casts = [
        'penjelasan_proses' => 'boolean',
        'kesempatan_belajar' => 'boolean',
        'diskusi_metoda' => 'boolean',
        'penggalian_bukti' => 'boolean',
        'demonstrasi_kompetensi' => 'boolean',
        'penjelasan_keputusan' => 'boolean',
        'umpan_balik' => 'boolean',
        'studi_dokumen' => 'boolean',
        'jaminan_kerahasiaan' => 'boolean',
        'komunikasi_efektif' => 'boolean'
    ];

    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class);
    }
}