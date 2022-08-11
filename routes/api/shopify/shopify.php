<?php

use App\Http\Controllers\Shopify\ShopifyController;
use \App\Http\Controllers\Shopify\WebHookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//Register link Create,Update,Delete Webhook
Route::post('/webhook', [\App\Http\Controllers\Shopify\WebHookController::class , 'webhook'] )->name('shopify.webhook');

Route::apiResource('/store',ShopifyController::class);

