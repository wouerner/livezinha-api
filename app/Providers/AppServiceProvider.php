<?php

namespace App\Providers;

use App\Models\LiveStream;
use App\Models\Question;
use App\Policies\LiveStreamPolicy;
use App\Policies\QuestionPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(LiveStream::class, LiveStreamPolicy::class);
        Gate::policy(Question::class, QuestionPolicy::class);

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by($request->input('email')));
    }
}
