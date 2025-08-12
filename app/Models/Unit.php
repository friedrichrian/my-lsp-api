<?php

// app/Models/Unit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['schema_id',  'unit_ke', 'kode_unit', 'judul_unit'];

    public function schema()
    {
        return $this->belongsTo(Schema::class);
    }

    public function elements()
    {
        return $this->hasMany(Element::class);
    }
}