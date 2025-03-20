<?php

use App\Http\Controllers\API\ApplicationController;
use App\Http\Controllers\Api\InterviewController;
use App\Http\Controllers\Api\JobAlertController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;


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

Route::prefix('messages')->group(function () {
   Route::middleware(['auth:sanctum','throttle:60,1'])->group(function () {
       Route::post('send/message',[MessageController::class,'sendMessage']);
       Route::get('get/messages/history/{application}',[MessageController::class,'getMessages']);
   }) ;
});

Route::prefix('interviews')->group(function () {
    Route::middleware(['auth:sanctum','throttle:60,1'])->group(function () {
        Route::post('interview/sent',[InterviewController::class,'store']);
    });
});



