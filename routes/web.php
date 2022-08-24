<?php

use App\Services\Customers\CustomerService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\Shopify\ShopifyController;
use App\Models\DbStatus;
use App\Models\Customer;
use App\Models\Campaign;
use App\Models\CampaignProcess;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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

//Get all Customer display the interface
Route::get('/getCustomer', [CustomerController::class, 'getCustomer']);

//Get Store display the interface
Route::get('/getStore', [ShopifyController::class, 'getStore']);

Route::get('/', function () {
    return view('showNotification');
});

// Auth::routes();


// Route::get('/home', [HomeController::class, 'index'])->name('home');


