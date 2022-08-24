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

    //Save Campaign and send email to Customers.
    Route::post('/save-campaign', [CampaignController::class, 'saveCampaign']);

    //Send Test Email to chosen one.
    Route::post('/send-test',[CampaignController::class, 'sendEmail']);
});

//Page Home Campaign to see list Campaigns.
Route::apiResource('/campaign',CampaignController::class)->middleware("CheckAuthenticate");
