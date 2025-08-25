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

    public function schema()
    {
        return $this->belongsTo(Schema::class, 'schema_id'); 
        // sesuaikan foreign key dan model
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id'); 
        // sesuaikan foreign key dan model
    }
    public function assesor()
    {
        return $this->belongsTo(Assesor::class, 'assesor_id'); 
        // sesuaikan foreign key dan model
    }
}
