<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model
{
    protected $table = 'question_answers';
    protected $fillable = [
        'question_id',
        'assesment_asesi_id',
        'selected_option'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function assesmentAsesi()
    {
        return $this->belongsTo(Assesment_Asesi::class, 'assesment_asesi_id');
    }
}
