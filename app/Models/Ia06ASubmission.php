<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ia06ASubmission extends Model
{
    use HasFactory;

    protected $table = 'ia06a_submissions';

    protected $fillable = [
        'assesment_asesi_id',
        'skema_id',
        'catatan',
        'ttd_asesi',
        'ttd_asesor',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function assesmentAsesi()
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }

    public function schema()
    {
        return $this->belongsTo(Schema::class, 'skema_id');
    }
}
