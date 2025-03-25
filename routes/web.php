<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/migrate', function () {
    \Artisan::call('migrate', ['--force' => true]);
    return 'Database migrated successfully!';
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});


