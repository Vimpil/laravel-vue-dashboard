<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BetController;
use App\Http\Controllers\EventController;

// НЕ добавляем Route::prefix('api') — Laravel уже подставляет /api

Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'throttle:logout']);
Route::get('/user', function() {
    return auth()->user();
})->middleware('auth:sanctum');

// Middleware только для /bets
Route::post('/bets', [BetController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:bets']);

// New route for internal bets
Route::post('/internal-bets', [BetController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:bets']);

Route::get('/events', [EventController::class, 'index']);

// Added a route to fetch all bets
Route::get('/bets', [BetController::class, 'index'])->middleware(['auth:sanctum']);
