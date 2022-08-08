<?php

use App\Http\Controllers\Client\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Input Name Shop
Route::any('/login', [\App\Http\Controllers\Shopify\ShopifyController::class, 'login'])->name('login');



//Register link Create,Update,Delete Webhook
Route::post('/webhook', [\App\Http\Controllers\Shopify\WebHookController::class , 'webhook'] )
    ->name('shopify.webhook');



