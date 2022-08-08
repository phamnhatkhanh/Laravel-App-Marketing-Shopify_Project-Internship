<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;


Route::prefix('customer')->group(function (){
    Route::get('/', [CustomerController::class, 'index']);
    Route::get('sync', [CustomerController::class, 'syncCutomerFromShopify']);

    Route::post('/filterCustomer', [CustomerController::class, 'searchFilterCustomer']);
    Route::get('/export',[CustomerController::class,'exportCustomerCSV'])
        ->name('customer.export');

    Route::get('/export',[CustomerController::class,'exportIDCustomerCSV'])
        ->name('customerID.export');
});
