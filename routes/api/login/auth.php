<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\Shopify\ShopifyController;
use Illuminate\Support\Facades\Route;

//Input Name Shop
Route::middleware(['throttle:api'])->group(function () {
    Route::any('/login', [ShopifyController::class, 'login'])->name('login');
    Route::any('/authen', [ShopifyController::class, 'authen'])->name('authen');
});

//Get information Store if token already exist.
Route::get('/store', [LoginController::class, 'store'])->middleware('CheckAuthenticate');

//Refresh Token
Route::any('/refresh', [LoginController::class, 'refresh']);
