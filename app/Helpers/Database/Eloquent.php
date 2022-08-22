<?php

use Illuminate\Support\Facades\DB;
use App\Models\DbStatus;
use App\Events\Database\SyncDatabase;

if (!function_exists('getConnectDatabaseActived')) {
    function getConnectDatabaseActived($model){
        $listDatabaseModel = DbStatus::where('model_name', '=', $model->getTable())->get();
        $tableModel = $model->getTable();
        $isSelectedDatabaseConnect = "not_selected";

        foreach ($listDatabaseModel as  $dbModel) {
            try {

                if(DB::connection($dbModel->name)->getPdo()){

                    $dbModelConnect = DbStatus::where('name',$dbModel->name)->first();
                    if($dbModelConnect->status == 'actived' ){

                        if($isSelectedDatabaseConnect == "not_selected"){
                            $model = $model::on($dbModel->name);
                            $isSelectedDatabaseConnect="selected";
                            continue;
                        }
                    }else{
                        $dbActivedModel = DbStatus::where('model_name',$tableModel)
                            ->where('status',"actived")->first();
                        if($dbActivedModel){ // normal sync
                            DbStatus::where('name',$dbModel->name)->update([ 'status' => 'syncing']);
                            event(new SyncDatabase($dbModel->name, $tableModel));
                            continue;
                        }else{ // disconnected all db.
                            $dbLastActiveModel =  DbStatus::where('model_name', $tableModel)
                            ->orderBy('updated_at','DESC')
                            ->first();
                            $dbLastActiveModel->update([ "status" =>"sync_lasted_db" ]);
                            $model = $model::on($dbModel->name);
                            event(new SyncDatabase($dbModel->name, $tableModel, $dbLastActiveModel->name));
                            continue;
                        }
                    }
                }
            } catch (\Throwable $e) {

                if($dbModel->status != "disconnected"){
                    DbStatus::where('name',$dbModel->name)->update([ "status" =>"disconnected" ]);
                }
                continue;
            }
        }
        return $model;
    }
}

if (!function_exists('getRandomModelId')) {
    function getRandomModelId(string $model){
        // get model count
        $count = $model::query()->count();
        // $count = $model::all()->random()->id;
        if($count === 0){
            // if model count is 0
            // we should create a new record and retrieve the record id
            return $model::factory()->create()->id;
        }else{
            // generate random number between 1 and model count
            return $model::all()->random()->id;
        }
    }
}

if (!function_exists('getListModels')) {
    function getListModels($path){
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

                    $out[] = $model;
                }
            }
            return $out;
    }
}

if (!function_exists('getDiverDafault')) {
    function getDiverDafault($model){
        $diverCurrent = $model->getConnection()->getName();
        if(strpos($diverCurrent,"_backup")){
            $diverCurrent =substr($diverCurrent,0,strpos($diverCurrent,"_backup"));
        }

        return $diverCurrent;
    }
}
