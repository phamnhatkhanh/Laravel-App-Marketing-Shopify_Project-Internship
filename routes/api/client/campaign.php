<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\Client\CampaignController;
use App\Http\Controllers\LoginController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;

// Route::middleware("CheckAuthenticate")->prefix('campaign')->group(function (){
Route::prefix('campaign')->group(function (){


    Route::get('/filterCampaign', [CampaignController::class, 'searchFilterCampaign']);

    Route::get('/getCampaign', [CampaignController::class, 'getCampaign']);

    Route::post('/save-campaign', [CampaignController::class, 'saveCampaign']);

    Route::get('/get-campaigns-process',[CampaignController::class, 'getCampaignProceess']);

    Route::post('/send-email-preview',[CampaignController::class, 'sendEmailPreview']);

    Route::post('/send-email',[CampaignController::class, 'sendEmail']);
});

Route::apiResource('/campaign',CampaignController::class);
