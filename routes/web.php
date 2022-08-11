<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\CampaignController;
use App\Http\Controllers\Client\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Shopify\ShopifyController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\DbStatus;
use App\Models\Store;
use App\Models\ObserveModel;
use App\Models\Review;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
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
Route::get('/data', function(){

});

Route::get('/set-db',function(){

    $listNameConnectionMysql = config('database.connections');
    foreach ($listNameConnectionMysql as $key => $value) {
        DbStatus::create(['name' => $key,'status' => 'actived']);
    }

    $path = app_path() . "/Models";
    function getModels($path){
        $out = [];
        $results = scandir($path);
        foreach ($results as $result) {
            if ($result === '.' or $result === '..') continue;
            $filename = $path . '/' . $result;
            if (is_dir($filename)) {
                $out = array_merge($out, getModels($filename));
            }else{
                $model  = str_replace(app_path(),"App",substr($filename,0,-4));
                $model  = str_replace("/","\\",$model );
                // dd(new $model());
                $out[] = $model;
                //hello
            }

        }
        return $out;
    }
    function getDiverDafault($model){
        $diverCurrent = $model->getConnection()->getName();
        if(strpos($diverCurrent,"_backup")){
            $diverCurrent =substr($diverCurrent,0,strpos($diverCurrent,"_backup"));
        }
        return $diverCurrent;
    }
    $listPathModel = getModels($path);
    // dd($listPathModel);
    foreach ($listPathModel as $pathModel) {
        $model = new $pathModel();
        // dd($model);
        $driverDefaultModel = getDiverDafault($model);
        if($driverDefaultModel!="mysql"){
            //  dd($driverDefaultModel);
            $get_list_driver =  DbStatus::where(function ($query) use ($driverDefaultModel){
                $query->where('name','like',$driverDefaultModel.'%')
                        ->where('model_name', '=', null);
            })->get();
            // dd($get_list_driver);
            foreach ($get_list_driver as $driver) {
                // info($driver->name);
                if(Schema::connection($driver->name)->hasTable($model->getTable())){
                    DbStatus::create(['name' => $driver->name,'status' => 'actived','model_name' => $model->getTable()]);
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

Route::get('/mail', [CampaignController::class, 'sendEmailCampaign']);
Route::get('/test-mail', [CampaignController::class, 'sendEmailCampaign']);

Route::get('/', function () {
    return view('showNotification');
    // return view('welcome');
});


Route::get('getPusher', function () {
    return view('form_pusher');
});


//Route::get('/pusher', function (Illuminate\Http\Request $request) {
//    event(new App\Events\HelloPusherEvent($request));
//    return redirect('getPusher');
//});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::get('/home', [HomeController::class, 'index'])->name('home');