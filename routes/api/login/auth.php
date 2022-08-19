<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\Shopify\ShopifyController;
use Illuminate\Support\Facades\Route;

//Input Name Shop

Route::any('/login', [ShopifyController::class, 'login'])->name('login');
Route::any('/authen', [ShopifyController::class, 'authen'])->name('authen');


Route::get('/store', [LoginController::class, 'store'])->middleware('CheckAuthenticate');


// Route::post('/login', [LoginController::class, 'login'])->name('login');
