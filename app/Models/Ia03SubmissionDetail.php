<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ia03SubmissionDetail extends Model
{
    use HasFactory;

    protected $table = 'ia03_submission_details';

    protected $fillable = [
        'submission_id',
        'question_id',
        'selected_option', // 'ya' or 'tidak'
        'response_text',
    ];

    public function submission()
    {
        return $this->belongsTo(Ia03Submission::class, 'submission_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
