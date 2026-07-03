<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionVote;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionVoteFactory extends Factory
{
    protected $model = QuestionVote::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'vote' => fake()->randomElement(['like', 'dislike']),
            'voter_ip' => fake()->ipv4(),
        ];
    }
}
