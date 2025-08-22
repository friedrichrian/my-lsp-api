<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assesment extends Model
{
    //
    protected $table = 'assesments';
    protected $fillable = [
        'skema_id',
        'admin_id',
        'assesor_id',
        'tanggal_assessment',
        'status',
        'tuk',
    ];  
}
