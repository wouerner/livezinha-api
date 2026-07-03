<?php

use App\Models\LiveStream;

describe('GET /api/lives/active', function () {
    it('returns the active live when one exists', function () {
        $live = LiveStream::factory()->active()->create(['title' => 'Minha Live Ativa']);

        $this->getJson('/api/lives/active')
            ->assertOk()
            ->assertJsonPath('id', $live->id)
            ->assertJsonPath('title', 'Minha Live Ativa')
            ->assertJsonPath('status', 'active');
    });

    it('returns the next scheduled live when no active live exists', function () {
        LiveStream::factory()->create(['title' => 'Live Futura', 'status' => 'scheduled', 'scheduled_at' => now()->addHour()]);

        $this->getJson('/api/lives/active')
            ->assertOk()
            ->assertJsonPath('title', 'Live Futura')
            ->assertJsonPath('status', 'scheduled');
    });

    it('prefers active over scheduled when both exist', function () {
        LiveStream::factory()->active()->create(['title' => 'Live Ativa']);
        LiveStream::factory()->create(['title' => 'Live Agendada', 'status' => 'scheduled', 'scheduled_at' => now()->addHour()]);

        $this->getJson('/api/lives/active')
            ->assertOk()
            ->assertJsonPath('title', 'Live Ativa');
    });
});
