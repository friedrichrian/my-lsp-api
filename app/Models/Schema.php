<?php

// app/Models/Schema.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Schema extends Model
{
    protected $fillable = ['judul_skema', 'nomor_skema', 'jurusan_id', 'tanggal_mulai', 'tanggal_selesai'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function countTotalKuk()
    {
        return KriteriaUntukKerja::whereHas('element.unit', function($query) {
            $query->where('schema_id', $this->id);
        })->count();
    }
}