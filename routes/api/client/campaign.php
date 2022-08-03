<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Helpers\Routes\RouteHelper;
use App\Http\Controllers\Client\CampaignController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProductRequest;

Route::post('/searchCampaign', [CampaignController::class, 'searchCampaign']);
Route::post('/sortCampaign', [CampaignController::class, 'sortCampaign']);
