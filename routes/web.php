<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\Shopify\ShopifyController;
use App\Models\DbStatus;
use App\Models\Customer;
use Illuminate\Support\Facades\Schema;
use Tymon\JWTAuth\Facades\JWTAuth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/set-db',function(){
    $listNameConnectionMysql = config('database.connections');
    foreach ($listNameConnectionMysql as $key => $value) {
        DbStatus::create(['name' => $key, 'status' => 'actived']);
    }

    $path = app_path() . "/Models";
 
 
    $listPathModel = getListModels($path);
    // dd($listPathModel);
    foreach ($listPathModel as $pathModel) {
        $model = new $pathModel();
        // dd($model);
        $driverDefaultModel = getDiverDafault($model);
        if ($driverDefaultModel != "mysql") {
            //  dd($driverDefaultModel);
            $get_list_driver =  DbStatus::where(function ($query) use ($driverDefaultModel) {
                $query->where('name', 'like', $driverDefaultModel . '%')
                    ->where('model_name', '=', null);
            })->get();
            // dd($get_list_driver);
            foreach ($get_list_driver as $driver) {
                // info($driver->name);
                if (Schema::connection($driver->name)->hasTable($model->getTable())) {
                    DbStatus::create(['name' => $driver->name, 'status' => 'actived', 'model_name' => $model->getTable()]);
                }
            }
        }
    }

    DbStatus::where('model_name', '=', null)
        ->orWhereNull('model_name')->delete();
    //from model base on driver defautl -> get list driver -> check table -> add.

    return  "done set db";
    // return $listNameConnectionMysql;
});

Route::get('/getCustomer', [CustomerController::class, 'getCustomer']);
Route::get('/getStore', [ShopifyController::class, 'getStore']);

// Route::get('/mail', [CampaignController::class, 'sendEmailCampaign']);
// Route::get('/test-mail', [CampaignController::class, 'sendEmailCampaign']);

Route::get('/', function () {
    return view('showNotification');
    // return view('welcome');
});


// Auth::routes();

// Route::get('/home', [HomeController::class, 'index'])->name('home');


// Route::get('/home', [HomeController::class, 'index'])->name('home');


