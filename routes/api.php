<?php

use App\Http\Controllers\Api\JobController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;


Route::prefix('auth')->group(function () {
    // API routes will go here
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
Route::prefix('jobs')->group(function () {
        Route::get('list',[JobController::class, 'index']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::Post('create', [JobController::class, 'store']);
    });
});

