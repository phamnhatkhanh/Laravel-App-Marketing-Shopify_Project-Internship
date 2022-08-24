<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\Shopify\ShopifyController;
use App\Services\Customers\CustomerService;
use App\Models\DbStatus;
use App\Models\Customer;
use App\Models\Campaign;
use App\Models\CampaignProcess;


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

Route::get('/', function () {
    return view('showNotification');
});




