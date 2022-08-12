<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shopify\ShopifyController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

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

includeRouteFiles(__DIR__ . '/api/client');

Route::prefix('shopify')->group(function () {
    includeRouteFiles(__DIR__ . '/api/shopify');
});

Route::prefix('auth')->group(function () {
    includeRouteFiles(__DIR__ . '/api/login');
});


// ?list_customer=[3,6,8]
// ?except_customer=[2,6,3]&&get_quantify_customer = 3

Route::get('/redis', function (Request $request) {

    $redis = Redis::connection();
    $store = Store::all();
    $redis->set('store',$store);
    $name = $redis->get('store');
    echo $name;
});