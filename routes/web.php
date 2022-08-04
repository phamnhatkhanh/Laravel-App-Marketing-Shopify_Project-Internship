<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\CampaignController;
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

Route::get('/', function () {
    return view('showNotification');
    // return view('welcome');
});


Route::get('getPusher', function (){
   return view('form_pusher');
});

Route::get('/pusher', function(Illuminate\Http\Request $request) {
    event(new App\Events\HelloPusherEvent($request));
    return redirect('getPusher');
});

Auth::routes();

Route::get('/home', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');





