<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionVote extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionVoteFactory> */
    use HasFactory;

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
