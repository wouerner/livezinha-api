<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->middleware('throttle:login');

Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong',
        'status' => 'connected',
    ]);
});

// Public endpoints (lives list, active live, active question, public questions, vote)
Route::get('/lives/active', [App\Http\Controllers\LiveStreamController::class, 'activeLive']);
Route::get('/lives/active/question', [App\Http\Controllers\QuestionController::class, 'activeQuestion']);
Route::get('/lives/{liveStream}/questions/public', [App\Http\Controllers\QuestionController::class, 'publicQuestions']);
Route::post('/questions/{question}/vote', [App\Http\Controllers\QuestionController::class, 'vote']);

// Public endpoints
Route::get('/lives', [App\Http\Controllers\LiveStreamController::class, 'publicIndex']);

// Public store for questions (spectator submission)
Route::post('/questions', [App\Http\Controllers\QuestionController::class, 'store']);

// Authenticated admin routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/user', [App\Http\Controllers\AuthController::class, 'user']);

    // Admin CRUD for lives (index is public, defined above)
    Route::post('/lives', [App\Http\Controllers\LiveStreamController::class, 'store']);
    Route::get('/lives/{liveStream}', [App\Http\Controllers\LiveStreamController::class, 'show']);
    Route::put('/lives/{liveStream}', [App\Http\Controllers\LiveStreamController::class, 'update']);
    Route::delete('/lives/{liveStream}', [App\Http\Controllers\LiveStreamController::class, 'destroy']);

    // Admin CRUD for questions (excluding store, which is public)
    Route::get('/questions', [App\Http\Controllers\QuestionController::class, 'index']);
    Route::get('/questions/{question}', [App\Http\Controllers\QuestionController::class, 'show']);
    Route::put('/questions/{question}', [App\Http\Controllers\QuestionController::class, 'update']);
    Route::delete('/questions/{question}', [App\Http\Controllers\QuestionController::class, 'destroy']);
});

