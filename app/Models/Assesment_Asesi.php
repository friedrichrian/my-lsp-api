<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assesment_Asesi extends Model
{
    //
    protected $table = 'assesment_asesi';
    protected $fillable = [
        'assesment_id',
        'assesi_id',
        'status'
    ];
}
