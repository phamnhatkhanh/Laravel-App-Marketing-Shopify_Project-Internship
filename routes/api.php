<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\Client\CampaignController;
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
Route::post('/save-campaign', [CampaignController::class, 'saveCampaign']);

Route::prefix('customer')->group(function() {
    Route::get('sync', [CustomerController::class, 'syncCutomerFromShopify']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::apiResource('products',ProductController::class);
Route::prefix('auth')->group(function (){
        RouteHelper::includeRouteFiles(__DIR__ . '/api/jwt');
        RouteHelper::includeRouteFiles(__DIR__ . '/api/client');
});

RouteHelper::includeRouteFiles(__DIR__ . '/api/shopify');
//Export CSV
Route::get('/export', [ExportController::class, 'export']);
Route::any('dashboard',function (){
    return 'done get data from shopify';

});

//Register link Create,Update,Delete Webhook
Route::post('/shopify/webhook', [\App\Http\Controllers\Shopify\WebHookController::class , 'webhook'] )
    ->name('shopify.webhook');

//Send mail
Route::get('/email', [\App\Http\Controllers\SendMailController::class,'email']);

//Get Acess_Token and handle next
Route::any('/authen', [\App\Http\Controllers\Shopify\ShopifyController::class, 'authen'])->name('authen');

//Export CSV
Route::get('/export-customers',[\App\Http\Controllers\ExportCSVController::class, 'export'])
    ->name('customer.export');

Route::get('/export-customers/exportFile',[\App\Http\Controllers\ExportCSVController::class,'exportFileDownload'])
    ->name('customer.exportFile');

//Export CSV
Route::get('/export', [ExportController::class, 'export']);
