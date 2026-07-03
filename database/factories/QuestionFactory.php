<?php

namespace Database\Factories;

use App\Models\LiveStream;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'live_stream_id' => LiveStream::factory(),
            'name' => fake()->name(),
            'tiktok_handle' => '@' . fake()->userName(),
            'question_text' => fake()->sentence() . '?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'pending',
            'is_tagged' => false,
            'is_hidden' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'displayed_at' => now(),
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hidden' => true,
        ]);
    }
}
