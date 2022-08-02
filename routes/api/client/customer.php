<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Routes\RouteHelper;
<<<<<<< HEAD

=======
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\ExportController;
>>>>>>> 63dd166df9a3d4298aa3036daa2dc9661568b46b
use App\Http\Controllers\JwtAuthController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;


//Get, Search, Sort Customer 
Route::get('/getCustomer', [CustomerController::class, 'index']);
Route::post('/searchCustomer', [CustomerController::class, 'searchCustomer']);
Route::post('/createDate', [CustomerController::class, 'createDate']);
Route::post('/totalSpent', [CustomerController::class, 'totalSpent']);
Route::post('/totalOrder', [CustomerController::class, 'totalOrder']);
Route::post('/sortCustomer', [CustomerController::class, 'sortCustomer']);