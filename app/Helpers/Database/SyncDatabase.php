<?php

use Illuminate\Support\Facades\DB;
use App\Models\ObserveModel;
use App\Models\DbStatus;
// use Throwable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

if (!function_exists('SyncDatabaseAfterCreatedModel')) {
    function SyncDatabaseAfterCreatedModel($dbConnectName,$model){
        $listDatabaseModel = DbStatus::where('model_name', '=', $model->getTable())->get();

        $dataCreatedModel = $model->toArray();
        $dataCreatedModel['created_at'] =  Carbon::parse($dataCreatedModel['created_at'])->format('Y-m-d H:i:s');
        $dataCreatedModel['updated_at'] =  Carbon::parse($dataCreatedModel['updated_at'])->format('Y-m-d H:i:s');

        foreach ($listDatabaseModel as $dbModel) {
            try {
                if($dbModel->name == $dbConnectName){continue;}
                if(DB::connection($dbModel->name)->getPdo()){
                    Schema::connection($dbModel->name)->disableForeignKeyConstraints();
                        $model::on($dbModel->name)->create($dataCreatedModel);
                    Schema::connection($dbModel->name)->enableForeignKeyConstraints();
                }

            } catch (\Throwable $th) {
                $dataObserveModel = [
                    "database" => $dbModel->name,
                    "table" => $model->getTable(),
                    "id_row" => $model->id,
                    "action" => "create"
                ];

                DbStatus::where('name',$dbModel->name)->update([ "status" =>"disconnected"]);
                ObserveModel::where('id_row',$model->id)->updateOrCreate($dataObserveModel);
                continue;
            }
        }

    }
}

if (!function_exists('SyncDatabaseAfterUpdatedModel')) {
    function SyncDatabaseAfterUpdatedModel($dbConnectName,$model){

        $listDatabaseModel = DbStatus::where('model_name', '=', $model->getTable())->get();
        if(!empty($model)){
            $dataUpdateModel = $model->toArray();
            unset($dataUpdateModel['created_at']);
            unset($dataUpdateModel['updated_at']);
            foreach ($listDatabaseModel as $dbModel) {
                try {
                    if(DB::connection($dbModel->name)->getPdo()){
                        // $dbModelConnect = DbStatus::where('name',$dbModel->name)->first();
                        // if($dbModelConnect->status == 'actived'){
                            // if($dbModel->name == $dbConnectName){continue;}
                            $model::on($dbModel->name)->where('id',$model->id)->update($dataUpdateModel);
                        // }else{
                        //     // syncing or retry connect but status still disconnected
                        //     // throw new Throwable(); // not do sync update.
                        //     continue;
                        // }
                    }
                } catch (Throwable $th ) {
                    $dataObserveModel = [
                        "database" => $dbModel->name,
                        "table" => $model->getTable(),
                        "id_row" => $model->id,
                        "action" => "update"
                    ];
                    DbStatus::where('name',$dbModel->name)->update([ "status" =>"disconnected"]);
                    ObserveModel::where('id_row', $model->id)->updateOrCreate($dataObserveModel);
                    continue;
                }
            }
        }
    }
}

if (!function_exists('SyncDatabaseAfterDeletedModel')) {
    function SyncDatabaseAfterDeletedModel($dbConnectName,$model){
        $listDatabaseModel = DbStatus::where('model_name', '=', $model->getTable())->get();
        if(!empty($model)){
            foreach ($listDatabaseModel as $dbModel) {
                // if($dbModel->name == $event->dbConnectName){continue;}
                try {
                    if(DB::connection($dbModel->name)->getPdo()){
                        $model::on($dbModel->name)->where('id',$model->id)->delete();
                    }
                } catch (\Throwable $th) {
                    $dataObserveModel = [
                        "database" => $dbModel->name,
                        "table" => $model->getTable(),
                        "id_row" => $model->id,
                        "action" => "delete"
                    ];
                    DbStatus::where('name',$dbModel->name)->update([ "status" =>"disconnected"]);
                    ObserveModel::where('id_row', $model->id)->updateOrCreate($dataObserveModel);
                    continue;
                }
            }
        }

    }
}



