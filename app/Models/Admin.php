<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    //
    protected $table = 'admin';
    protected $primaryKey = 'id_admin';
    protected $fillable = [
        'nama_lengkap',
        'user_id',
        'email',
        'no_hp',
        'role',
        'status',
        'created_at',
        'updated_at',
    ];
}
