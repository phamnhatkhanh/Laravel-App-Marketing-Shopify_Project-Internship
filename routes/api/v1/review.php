<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;

Route::prefix("products")->group(function(){
    Route::apiResource('/{product}/reviews',ReviewController::class);
});
