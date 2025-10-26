<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(10)->by(
                $request->ip() . '|' . $request->input('email')
            )->response(function () use ($request) {
                \App\Models\FraudLog::create([
                    'ip' => $request->ip(),
                    'user_id' => null,
                    'reason' => 'Rate limit hit: login',
                    'payload' => json_encode(['email' => $request->input('email')]),
                ]);

                return response()->json([
                    'message' => 'Too many login attempts. Please try again in 1 minute.'
                ], 429);
            });
        });

        RateLimiter::for('logout', function ($request) {
            return Limit::perMinute(10)->by($request->ip())->response(function () use ($request) {
                \App\Models\FraudLog::create([
                    'ip' => $request->ip(),
                    'user_id' => null,
                    'reason' => 'Rate limit hit: logout',
                    'payload' => null,
                ]);

                return response()->json([
                    'message' => 'Too many logout attempts. Please try again later.'
                ], 429);
            });
        });

        RateLimiter::for('bets', function ($request) {
            return Limit::perMinute(20)->by($request->ip())->response(function () use ($request) {
                \App\Models\FraudLog::create([
                    'ip' => $request->ip(),
                    'user_id' => null,
                    'reason' => 'Rate limit hit: bets',
                    'payload' => null,
                ]);

                return response()->json([
                    'message' => 'Too many bet requests. Please try again later.'
                ], 429);
            });
        });
    }
}
