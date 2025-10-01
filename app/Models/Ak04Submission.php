<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ak04Submission extends Model
{
    use HasFactory;

    protected $table = 'ak04_submissions';

    protected $fillable = [
        'assesment_asesi_id',
        'alasan_banding',
    ];


    public function assesmentAsesi()
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }

    public function questions()
    {
        return $this->belongsToMany(Ak04Question::class, 'ak04_question_submissions', 'ak04_submission_id', 'ak04_question_id')
            ->withPivot('selected_option')
            ->withTimestamps();
    }

    public function questionSubmissions()
    {
        return $this->hasMany(Ak04QuestionSubmission::class, 'ak04_submission_id');
    }
}
