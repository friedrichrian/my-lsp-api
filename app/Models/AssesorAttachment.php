<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssesorAttachment extends Model
{
    protected $table = 'bukti_dokumen_assesor';

    protected $fillable = [
        'assesor_id',
        'nama_dokumen',
        'file_path',
    ];

    public function assesor()
    {
        return $this->belongsTo(Assesor::class, 'assesor_id');
    }    
}
