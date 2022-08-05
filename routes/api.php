<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\JwtAuthController;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\Client\CampaignController;
use App\Http\Controllers\Shopify\ShopifyController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/get-campaigns-process',[CampaignController::class, 'getCampaignProceess']);

Route::post('/save-campaign', [CampaignController::class, 'saveCampaign']);

Route::prefix('customer')->group(function() {
    Route::get('sync', [CustomerController::class, 'syncCutomerFromShopify']);
    Route::get('/get-all', [CustomerController::class, 'index']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::apiResource('products',ProductController::class);
Route::prefix('auth')->group(function (){
        RouteHelper::includeRouteFiles(__DIR__ . '/api/jwt');
});

Route::prefix('customer')->group(function (){
    RouteHelper::includeRouteFiles(__DIR__ . '/api/client');
});

Route::prefix('shopify')->group(function (){
    RouteHelper::includeRouteFiles(__DIR__ . '/api/shopify');
});


//Get Acess_Token and handle next
Route::any('/authen', [\App\Http\Controllers\Shopify\ShopifyController::class, 'authen'])->name('authen');

Route::any('/authen', [ShopifyController::class, 'authen'])->name('authen');
