<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\Client\CampaignController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;

Route::prefix('campaign')->group(function (){
    Route::get('/getCampaign', [CampaignController::class, 'getCampaign']);
    Route::post('/filterCampaign', [CampaignController::class, 'searchFilterCampaign']);
    Route::post('/save-campaign', [CampaignController::class, 'saveCampaign']);
    Route::get('/get-campaigns-process',[CampaignController::class, 'getCampaignProceess']);
});

Route::apiResource('/campaign',CampaignController::class);
