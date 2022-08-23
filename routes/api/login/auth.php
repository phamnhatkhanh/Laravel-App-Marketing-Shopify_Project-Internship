<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\Shopify\ShopifyController;
use Illuminate\Support\Facades\Route;

//Input Name Shop

//Page Login use to Login enter Website.
Route::any('/login', [ShopifyController::class, 'login'])->name('login');

//Check Role and login Shopify to install app Shopify.
Route::any('/authen', [ShopifyController::class, 'authen'])->name('authen');

//Get information Store if token already exist.
Route::get('/store', [LoginController::class, 'store'])->middleware('CheckAuthenticate');

