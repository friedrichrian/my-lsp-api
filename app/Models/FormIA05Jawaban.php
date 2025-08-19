<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormIA05Jawaban extends Model
{
    use HasFactory;

    protected $table = 'form_ia05_jawaban';

    protected $fillable = [
        'form_ia05_id',
        'nomor_soal',
        'jawaban',
        'pencapaian'
    ];

    protected $casts = [
        'pencapaian' => 'boolean'
    ];

    public function formIA05(): BelongsTo
    {
        return $this->belongsTo(FormIA05::class);
    }
}