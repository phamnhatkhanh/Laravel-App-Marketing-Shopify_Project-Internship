<?php

use App\Http\Controllers\JwtAuthController;
use App\Http\Controllers\Shopify\ShopifyController;
use Illuminate\Support\Facades\Route;



//Input Name Shop
Route::any('/login', [ShopifyController::class, 'login'])->name('login');
Route::any('/authen', [ShopifyController::class, 'authen'])->name('authen');

Route::get('/getUser', [JwtAuthController::class, 'user'])->middleware('CheckAuthenticate');