<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionVote extends Model
{
    protected $fillable = [
        'question_id',
        'vote',
        'voter_ip',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
