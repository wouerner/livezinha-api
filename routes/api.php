<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong',
        'status' => 'connected',
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
    ]);
});

// Public endpoints
Route::get('/lives/active', [App\Http\Controllers\LiveStreamController::class, 'activeLive']);
Route::get('/lives/active/question', [App\Http\Controllers\QuestionController::class, 'activeQuestion']);
Route::get('/lives/{liveStream}/questions/public', [App\Http\Controllers\QuestionController::class, 'publicQuestions']);
Route::post('/questions/{question}/vote', [App\Http\Controllers\QuestionController::class, 'vote']);

// Admin & Moderation endpoints (standard resources)
Route::apiResource('lives', App\Http\Controllers\LiveStreamController::class)->parameters(['lives' => 'liveStream']);
Route::apiResource('questions', App\Http\Controllers\QuestionController::class);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/user', [App\Http\Controllers\AuthController::class, 'user']);
});

