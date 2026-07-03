<?php

namespace Database\Factories;

use App\Models\LiveStream;
use Illuminate\Database\Eloquent\Factories\Factory;

class LiveStreamFactory extends Factory
{
    protected $model = LiveStream::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'streamer_name' => fake()->name(),
            'live_url' => fake()->url(),
            'scheduled_at' => fake()->dateTimeBetween('-1 day', '+1 day'),
            'status' => 'scheduled',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'started_at' => now()->subHour(),
        ]);
    }
}
