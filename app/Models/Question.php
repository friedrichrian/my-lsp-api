<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';
    protected $fillable = [
        'skema_id',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option'
    ];

    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class, 'question_id');
    }
}
