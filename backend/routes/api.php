<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BetController;
use App\Http\Middleware\VerifySignature;

// НЕ добавляем Route::prefix('api') — Laravel уже подставляет /api

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', function() {
    return auth()->user();
})->middleware('auth:sanctum');

// Middleware только для /bets
Route::post('/bets', [BetController::class, 'store'])
    ->middleware(VerifySignature::class);

// Temporary route to test logging
Route::get('/log-test', function () {
    \Log::info('Test log entry from /log-test route.');
    return response()->json(['message' => 'Log entry created.']);
});
