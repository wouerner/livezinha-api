<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    /** @use HasFactory<\Database\Factories\LiveStreamFactory> */
    use HasFactory;

    protected $fillable = ['title', 'streamer_name', 'live_url', 'scheduled_at', 'status'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
