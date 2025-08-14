<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormApl01Attachments extends Model
{
    protected $table = 'bukti_dokumen_formapl01';

    protected $fillable = [
        'form_apl01_id',
        'nama_dokumen',
        'file_path',
        'description',
    ];

    public function formApl01()
    {
        return $this->belongsTo(FormApl01::class, 'form_apl01_id');
    }
}
