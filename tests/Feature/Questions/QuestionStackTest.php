<?php

use App\Models\LiveStream;
use App\Models\Question;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('GET /api/lives/active/question', function () {
    it('returns empty array when no active live exists', function () {
        $this->getJson('/api/lives/active/question')
            ->assertOk()
            ->assertJson([]);
    });

    it('returns up to three active non-hidden questions ordered by displayed_at', function () {
        $live = LiveStream::factory()->active()->create();

        $q1 = Question::factory()->active()->create([
            'live_stream_id' => $live->id,
            'displayed_at' => now()->subMinutes(10),
        ]);

        $q2 = Question::factory()->active()->create([
            'live_stream_id' => $live->id,
            'displayed_at' => now()->subMinutes(5),
        ]);

        $q3 = Question::factory()->active()->create([
            'live_stream_id' => $live->id,
            'displayed_at' => now()->subMinutes(2),
        ]);

        Question::factory()->active()->hidden()->create([
            'live_stream_id' => $live->id,
        ]);

        $response = $this->getJson('/api/lives/active/question');

        $response->assertOk();
        $response->assertJsonCount(3);
        $response->assertJsonPath('0.id', $q1->id);
        $response->assertJsonPath('1.id', $q2->id);
        $response->assertJsonPath('2.id', $q3->id);
    });
});

describe('PUT /api/questions/{id}', function () {
    it('archives the oldest active question when activating a fourth', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $live = LiveStream::factory()->active()->create();

        $q1 = Question::factory()->active()->create([
            'live_stream_id' => $live->id,
            'displayed_at' => now()->subMinutes(15),
        ]);

        $q2 = Question::factory()->active()->create([
            'live_stream_id' => $live->id,
            'displayed_at' => now()->subMinutes(10),
        ]);

        $q3 = Question::factory()->active()->create([
            'live_stream_id' => $live->id,
            'displayed_at' => now()->subMinutes(5),
        ]);

        $q4 = Question::factory()->create([
            'live_stream_id' => $live->id,
            'status' => 'approved',
        ]);

        $this->putJson("/api/questions/{$q4->id}", ['status' => 'active'])
            ->assertOk();

        $q1->refresh();
        expect($q1->status)->toBe('archived');
        expect($q1->removed_at)->not->toBeNull();
        expect($q1->duration_seconds)->not->toBeNull();

        $q2->refresh();
        $q3->refresh();
        $q4->refresh();
        expect($q2->status)->toBe('active');
        expect($q3->status)->toBe('active');
        expect($q4->status)->toBe('active');

        $response = $this->getJson('/api/lives/active/question');
        $response->assertOk();
        $response->assertJsonCount(3);
        $response->assertJsonPath('0.id', $q2->id);
        $response->assertJsonPath('1.id', $q3->id);
        $response->assertJsonPath('2.id', $q4->id);
    });
});
