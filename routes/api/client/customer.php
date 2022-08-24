<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\LoginController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;


Route::middleware("CheckAuthenticate")->prefix('customer')->group(function () {
// Route::prefix('customer')->group(function () {

    //Sync Customer on Shopify back to Database.

    Route::get('sync', [CustomerController::class, 'syncCutomerFromShopify']);

    //Export CSV information Customer and send email to Shop owner.
    Route::get('export', [CustomerController::class, 'exportCustomerCSV']);
});

//Page Home Customer to see list Customers.
Route::apiResource('/customer', CustomerController::class)
->middleware("CheckAuthenticate");



