<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\Client\CampaignController;
use App\Http\Controllers\LoginController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;

Route::middleware("CheckAuthenticate")->prefix('campaign')->group(function (){
// Route::prefix('campaign')->group(function (){
    Route::post('/save-campaign', [CampaignController::class, 'saveCampaign']);
    Route::post('/send-test',[CampaignController::class, 'sendEmail']);
});

Route::apiResource('/campaign',CampaignController::class)->middleware("CheckAuthenticate");
