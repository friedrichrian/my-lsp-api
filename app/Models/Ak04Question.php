<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ak04Question extends Model
{
    protected $table = 'ak04_question';

    protected $fillable = [
        'question',
    ];

    public function submissions()
    {
        return $this->belongsToMany(Ak04Submission::class, 'ak04_question_submissions', 'ak04_question_id', 'ak04_submission_id')
            ->withPivot('selected_option')
            ->withTimestamps();
    }

    public function questionSubmissions()
    {
        return $this->hasMany(Ak04QuestionSubmission::class, 'ak04_question_id');
    }
}
