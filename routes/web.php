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


Route::get('/getCustomer', [CustomerController::class, 'getCustomer']);
Route::get('/getStore', [ShopifyController::class, 'getStore']);

Route::get('/', function () {
    return view('showNotification');
});
Route::get('/send-sms', function (){
    $account_sid = env('TWILIO_SID');
    $auth_token =env('TWILIO_AUTH_TOKEN');
    $twilio_phone_number = env('TWILIO_NUMBER');
    // $twilio_phone_number = env('TWILIO_NUMBER');
    $phone =env('NUMBER_PHONE');
    $client = new \Twilio\Rest\Client($account_sid, $auth_token);

    $client->messages->create(
        $phone,
        [
            "from" => $twilio_phone_number,
            "body" => "hello word..."
        ]
    );
    return 'sad';
});

// Auth::routes();


// Route::get('/home', [HomeController::class, 'index'])->name('home');


