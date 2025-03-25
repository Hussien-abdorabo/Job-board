<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/debug-swagger-config', function () {
//    dd(config('l5-swagger.defaults.paths'));
//});


