<?php

use Illuminate\Support\Facades\Route;
use App\Models\Customer;
use App\Helpers\Database\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
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
Route::get('/db', function () {
    return Customer::where('store_id',65147142383)->get();
});






