<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\CampaignController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Shopify\ShopifyController;
use Illuminate\Support\Facades\Cache;
use App\Models\Store;

use Illuminate\Http\Request;

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

Route::get('/mail', [CampaignController::class, 'sendEmailCampaign']);
Route::get('/test-mail', [CampaignController::class, 'sendEmailCampaign']);

Route::get('/', function () {
    return view('showNotification');
    // return view('welcome');
});


Route::get('getPusher', function () {
    return view('form_pusher');
});

Route::get('/pusher', function (Illuminate\Http\Request $request) {
    event(new App\Events\HelloPusherEvent($request));
    return redirect('getPusher');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

