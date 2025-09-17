<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/me', [AuthController::class, 'me']);

    // Task routes
    Route::apiResource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete']);
    Route::patch('/tasks/{task}/incomplete', [TaskController::class, 'incomplete']);

    // Import routes
    Route::post('/import/tasks', [ImportController::class, 'importTasks']);
    Route::get('/import/template', [ImportController::class, 'getTemplate']);
});

// Fallback for authenticated user (legacy)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');