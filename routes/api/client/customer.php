<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\LoginController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;


Route::middleware("CheckAuthenticate")->prefix('customer')->group(function () {

    Route::get('sync', [CustomerController::class, 'syncCutomerFromShopify']);

    Route::get('export', [CustomerController::class, 'exportCustomerCSV']);

    Route::get('export-selected', [CustomerController::class, 'exportSelectCustomerCSV']);
});

Route::apiResource('/customer', CustomerController::class)->middleware("CheckAuthenticate");

