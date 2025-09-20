<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormApl01SertificationData extends Model
{
    protected $table = 'form_apl01_sertification_data';

    protected $fillable = [
        'form_apl01_id',
        'schema_id',
        'tujuan_asesmen',
    ];

    public function formApl01()
    {
        return $this->belongsTo(FormApl01::class, 'form_apl01_id');
    }

    public function schema()
    {
        return $this->belongsTo(Schema::class, 'schema_id');
    }
}
