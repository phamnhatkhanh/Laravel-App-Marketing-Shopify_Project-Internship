<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\Shopify\ShopifyController;
use App\Models\DbStatus;
use App\Models\Customer;
use Illuminate\Support\Facades\Schema;
use Tymon\JWTAuth\Facades\JWTAuth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/getCustomer', [CustomerController::class, 'getCustomer']);
Route::get('/getStore', [ShopifyController::class, 'getStore']);

Route::get('/', function () {
    return view('showNotification');
});


// Auth::routes();


// Route::get('/home', [HomeController::class, 'index'])->name('home');


