<?php

// app/Models/Apl02SimpleItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apl02SimpleItem extends Model
{
    protected $fillable = [
        'judul_skema',
        'nomor_skema',
        'unit_ke',
        'kode_unit',
        'judul_unit',
        'elemen_index',
        'elemen_text',
        'sub_index',
        'sub_text',
    ];
}
