<?php

use App\Http\Controllers\JwtAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;

Route::post('/login', [JwtAuthController::class, 'login'])->name("login");
Route::get('/getUser', [JwtAuthController::class, 'user'])->middleware('CheckAuthenticate');