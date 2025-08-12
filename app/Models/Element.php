<?php

// app/Models/Element.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Element extends Model
{
    protected $fillable = ['unit_id',  'elemen_index', 'nama_elemen'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function kriteriaUntukKerja()
    {
        return $this->hasMany(KriteriaUntukKerja::class);
    }
}