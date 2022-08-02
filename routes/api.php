<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\JwtAuthController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::apiResource('products',ProductController::class);
Route::prefix('auth')->group(function (){
        RouteHelper::includeRouteFiles(__DIR__ . '/api/jwt');
        RouteHelper::includeRouteFiles(__DIR__ . '/api/client');
});


Route::any('dashboard',function (){
    return 'done get data from shopify';
});
//Input Name Shop
Route::any('/login', [\App\Http\Controllers\Shopify\ShopifyController::class, 'login'])->name('login');

//Get Acess_Token and handle next
Route::any('/authen', [\App\Http\Controllers\Shopify\ShopifyController::class, 'authen'])->name('authen');

//Register link Create,Update,Delete Webhook
Route::post('/shopify/webhook', [\App\Http\Controllers\Shopify\WebHookController::class , 'webhook'] )
    ->name('shopify.webhook');

//Export CSV
Route::get('/export', [ExportController::class, 'export']);
