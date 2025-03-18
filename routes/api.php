<?php

use App\Http\Controllers\Api\JobAlertController;
use App\Http\Controllers\Api\JobController;
use App\Models\JobAlert;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\API\ApplicationController;


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
    Route::middleware(['auth:sanctum','throttle:60,1'])->group(function () {
        Route::Post('create', [JobController::class, 'store']);
        Route::post('{job}/apply', [ApplicationController::class, 'store']);
        Route::get('{application}/status',[ApplicationController::class, 'show']);
        Route::patch('{application}/update/status',[ApplicationController::class, 'update']);
        Route::post('job-alert',[JobAlertController::class, 'subscribeToAlerts']);
    });
});

