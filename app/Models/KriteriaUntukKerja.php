<?php

// app/Models/KriteriaUntukKerja.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KriteriaUntukKerja extends Model
{
    protected $fillable = ['element_id',  'urutan', 'deskripsi_kuk'];
    protected $table = 'kriteria_untuk_kerja';

    public function element(): BelongsTo
    {
        return $this->belongsTo(Element::class);
    }
}