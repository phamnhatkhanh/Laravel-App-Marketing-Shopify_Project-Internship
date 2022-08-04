<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Input Name Shop
Route::any('/login', [\App\Http\Controllers\Shopify\ShopifyController::class, 'login'])->name('login');



//Register link Create,Update,Delete Webhook
Route::post('/shopify/webhook', [\App\Http\Controllers\Shopify\WebHookController::class , 'webhook'] )
    ->name('shopify.webhook');

Route::get('/showCustomer', [\App\Http\Controllers\Shopify\CustomerController::class, 'showCustomer']);
Route::post('/searchCustomer', [\App\Http\Controllers\Shopify\CustomerController::class, 'searchCustomer']);
Route::post('/createDate', [\App\Http\Controllers\Shopify\CustomerController::class, 'createDate']);
Route::post('/totalSpent', [\App\Http\Controllers\Shopify\CustomerController::class, 'totalSpent']);
Route::post('/totalOrder', [\App\Http\Controllers\Shopify\CustomerController::class, 'totalOrder']);


