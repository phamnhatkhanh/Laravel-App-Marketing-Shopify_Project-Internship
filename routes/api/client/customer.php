<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;


//Get, Search, Sort Customer
Route::get('/getCustomer', [CustomerController::class, 'index']);
Route::post('/filterCustomer', [CustomerController::class, 'searchFilterCustomer']);

//ExportCSV All Customer
Route::get('/export',[CustomerController::class,'exportCustomerCSV'])
    ->name('customer.export');

Route::get('/export-customers',[CustomerController::class,'exportIDCustomerCSV'])
    ->name('customerID.export');
