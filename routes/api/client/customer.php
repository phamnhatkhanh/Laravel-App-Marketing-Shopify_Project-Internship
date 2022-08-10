<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;




Route::prefix('customer')->group(function (){
    // Route::get('/getCustomer', [CustomerController::class, 'getCustomer']);

    Route::get('sync', [CustomerController::class, 'syncCutomerFromShopify']);

    Route::get('/filterCustomer', [CustomerController::class, 'searchFilterCustomer']);

    Route::get('/export',[CustomerController::class,'exportCustomerCSV']);
    Route::get('/export-selected',[CustomerController::class,'exportSelectCustomerCSV']);


});


Route::apiResource('/customer',CustomerController::class);
