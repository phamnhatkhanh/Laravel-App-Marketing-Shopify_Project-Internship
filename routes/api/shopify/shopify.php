<?php

use App\Http\Controllers\Client\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Input Name Shop
Route::any('/login', [\App\Http\Controllers\Shopify\ShopifyController::class, 'login'])->name('login');



//Register link Create,Update,Delete Webhook
Route::post('/webhook', [\App\Http\Controllers\Shopify\WebHookController::class , 'webhook'] )
    ->name('shopify.webhook');

Route::get('/showCustomer', [CustomerController::class, 'showCustomer']);
Route::post('/searchCustomer', [CustomerController::class, 'searchCustomer']);
Route::post('/createDate', [CustomerController::class, 'createDate']);
Route::post('/totalSpent', [CustomerController::class, 'totalSpent']);
Route::post('/totalOrder', [CustomerController::class, 'totalOrder']);


