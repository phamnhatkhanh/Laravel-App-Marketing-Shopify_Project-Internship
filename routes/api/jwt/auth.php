<?php

use App\Http\Controllers\JwtAuthController;
use Illuminate\Support\Facades\Route;

//Login
Route::post('/login', [JwtAuthController::class, 'login'])->name("login");
Route::get('/getUser', [JwtAuthController::class, 'user'])->middleware('CheckAuthenticate');