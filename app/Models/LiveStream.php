<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    protected $fillable = ['title', 'scheduled_at', 'status'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
