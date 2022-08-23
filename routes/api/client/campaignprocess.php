<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\CampaignProcessController;

Route::apiResource('/campaign-process',CampaignProcessController::class)->middleware("CheckAuthenticate");


