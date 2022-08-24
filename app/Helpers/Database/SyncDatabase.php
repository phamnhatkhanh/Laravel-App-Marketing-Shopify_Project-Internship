<?php

use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\DbStatus;
use App\Models\ObserveModel;

if (!function_exists('SyncDatabaseAfterCreatedModel')) {

    /**
     * * Create and synchronize data in the database model cluster.
     *
     * @param string $dbConnectName
     * @param $model
     *
     * @return void
     */
    function SyncDatabaseAfterCreatedModel($dbConnectName,$model){

        info("--SyncDatabaseAfterCreatedModel: create model ".$model->getTable());
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
    /**
     * * Update and synchronize data in the database model cluster.
     *
     * @param string $dbConnectName
     * @param $model
     *
     * @return void
     */
    function SyncDatabaseAfterUpdatedModel($dbConnectName,$model){

        info("--SyncDatabaseAfterUpdatedModel: update model ".$model->getTable());
        $listDatabaseModel = DbStatus::where('model_name', '=', $model->getTable())->get();
        if(!empty($model)){
            $dataUpdateModel = $model->toArray();
            unset($dataUpdateModel['created_at']);
            unset($dataUpdateModel['updated_at']);
            foreach ($listDatabaseModel as $dbModel) {
                try {
                    if(DB::connection($dbModel->name)->getPdo()){
                        $model::on($dbModel->name)->where('id',$model->id)->update($dataUpdateModel);
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

    /**
     * * Delete and synchronize data in the database model cluster.
     *
     * @param string $dbConnectName
     * @param $model
     *
     * @return void
     */
    function SyncDatabaseAfterDeletedModel($dbConnectName,$model){
        info("--SyncDatabaseAfterDeletedModel: delete model ".$model->getTable());
        $listDatabaseModel = DbStatus::where('model_name', '=', $model->getTable())->get();
        if(!empty($model)){
            foreach ($listDatabaseModel as $dbModel) {
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



