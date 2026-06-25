<?php

namespace Tests\Feature;

use App\Models\LiveStream;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionStackTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_endpoint_returns_empty_array_if_no_active_live()
    {
        $response = $this->getJson('/api/lives/active/question');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_active_endpoint_returns_up_to_three_active_non_hidden_questions()
    {
        $live = LiveStream::create([
            'title' => 'Test Live',
            'scheduled_at' => now(),
            'status' => 'active',
        ]);

        // Create 4 questions: 3 active, 1 hidden active
        $q1 = Question::create([
            'live_stream_id' => $live->id,
            'name' => 'User 1',
            'question_text' => 'Question 1',
            'status' => 'active',
            'is_hidden' => false,
            'displayed_at' => now()->subMinutes(10),
            'passcode' => 'q1-pass',
        ]);

        $q2 = Question::create([
            'live_stream_id' => $live->id,
            'name' => 'User 2',
            'question_text' => 'Question 2',
            'status' => 'active',
            'is_hidden' => false,
            'displayed_at' => now()->subMinutes(5),
            'passcode' => 'q2-pass',
        ]);

        $q3 = Question::create([
            'live_stream_id' => $live->id,
            'name' => 'User 3',
            'question_text' => 'Question 3',
            'status' => 'active',
            'is_hidden' => false,
            'displayed_at' => now()->subMinutes(2),
            'passcode' => 'q3-pass',
        ]);

        $q4 = Question::create([
            'live_stream_id' => $live->id,
            'name' => 'User 4',
            'question_text' => 'Question 4',
            'status' => 'active',
            'is_hidden' => true,
            'displayed_at' => now(),
            'passcode' => 'q4-pass',
        ]);

        $response = $this->getJson('/api/lives/active/question');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonPath('0.id', $q1->id);
        $response->assertJsonPath('1.id', $q2->id);
        $response->assertJsonPath('2.id', $q3->id);
    }

    public function test_activating_fourth_question_archives_oldest()
    {
        $live = LiveStream::create([
            'title' => 'Test Live',
            'scheduled_at' => now(),
            'status' => 'active',
        ]);

        // Create 3 active questions with different displayed_at
        $q1 = Question::create([
            'live_stream_id' => $live->id,
            'name' => 'User 1',
            'question_text' => 'Question 1',
            'status' => 'active',
            'displayed_at' => now()->subMinutes(15),
            'passcode' => 'q1-pass',
        ]);

        $q2 = Question::create([
            'live_stream_id' => $live->id,
            'name' => 'User 2',
            'question_text' => 'Question 2',
            'status' => 'active',
            'displayed_at' => now()->subMinutes(10),
            'passcode' => 'q2-pass',
        ]);

        $q3 = Question::create([
            'live_stream_id' => $live->id,
            'name' => 'User 3',
            'question_text' => 'Question 3',
            'status' => 'active',
            'displayed_at' => now()->subMinutes(5),
            'passcode' => 'q3-pass',
        ]);

        // Create a fourth approved question
        $q4 = Question::create([
            'live_stream_id' => $live->id,
            'name' => 'User 4',
            'question_text' => 'Question 4',
            'status' => 'approved',
            'passcode' => 'q4-pass',
        ]);

        // Put Q4 as active
        $response = $this->putJson("/api/questions/{$q4->id}", [
            'status' => 'active',
        ]);

        $response->assertStatus(200);

        // Q1 (the oldest active) should now be archived
        $q1->refresh();
        $this->assertEquals('archived', $q1->status);
        $this->assertNotNull($q1->removed_at);
        $this->assertNotNull($q1->duration_seconds);

        // Q2, Q3, Q4 should be active
        $q2->refresh();
        $q3->refresh();
        $q4->refresh();

        $this->assertEquals('active', $q2->status);
        $this->assertEquals('active', $q3->status);
        $this->assertEquals('active', $q4->status);

        // Active questions endpoint should return Q2, Q3, Q4 in order
        $response = $this->getJson('/api/lives/active/question');
        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonPath('0.id', $q2->id);
        $response->assertJsonPath('1.id', $q3->id);
        $response->assertJsonPath('2.id', $q4->id);
    }
}
