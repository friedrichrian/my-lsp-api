<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ia03Submission extends Model
{
    use HasFactory;

    protected $table = 'ia03_submissions';

    protected $fillable = [
        'assesment_asesi_id',
        'skema_id',
        'submission_date',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(Ia03SubmissionDetail::class, 'submission_id');
    }

    public function assesmentAsesi()
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }
}
