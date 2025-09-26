<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ak04QuestionSubmission extends Model
{
    protected $table = 'ak04_question_submissions';

    protected $fillable = [
        'ak04_submission_id',
        'ak04_question_id',
        'selected_option',
    ];

    public function ak04Submission()
    {
        return $this->belongsTo(Ak04Submission::class, 'ak04_submission_id');
    }

    public function question()
    {
        return $this->belongsTo(Ak04Question::class, 'ak04_question_id');
    }
}
