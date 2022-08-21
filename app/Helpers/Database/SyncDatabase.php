<?php

use Illuminate\Support\Facades\DB;
use App\Models\ObserveModel;
use App\Models\DbStatus;
// use Throwable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

if (!function_exists('SyncDatabaseAfterCreatedModel')) {
    function SyncDatabaseAfterCreatedModel($db_server,$model){
        info("SyncDatabaseAfterCreatedModel: created product listener ....". json_encode($model,true));
        $listDatabaseModel = DbStatus::where('model_name', '=', $model->getTable())->get();

        $dataCreatedModel = $model->toArray();
        $dataCreatedModel['created_at'] =  Carbon::parse($dataCreatedModel['created_at'])->format('Y-m-d H:i:s');
        $dataCreatedModel['updated_at'] =  Carbon::parse($dataCreatedModel['updated_at'])->format('Y-m-d H:i:s');
        // info("SyncDatabaseAfterCreatedModel: ".json_encode($dataCreatedModel));

        foreach ($listDatabaseModel as $dbModel) {
            try {
                if($dbModel->name == $db_server){continue;}
                info("SyncDatabaseAfterCreatedModel: prepare insert data to db");
                if(DB::connection($dbModel->name)->getPdo()){
                    Schema::connection($dbModel->name)->disableForeignKeyConstraints();
                        $model::on($dbModel->name)->create($dataCreatedModel);
                    Schema::connection($dbModel->name)->enableForeignKeyConstraints();
                    info("SyncDatabaseAfterCreatedModel: insert doneeee");
                }

            } catch (\Throwable $th) {
                // info("SyncDatabaseAfterCreatedModel:" .$th);
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
    function SyncDatabaseAfterUpdatedModel($db_server,$model){

        $listDatabaseModel = DbStatus::where('model_name', '=', $model->getTable())->get();
        if(!empty($model)){
            //  dd($model);
            $dataUpdateModel = $model->toArray();
            unset($dataUpdateModel['created_at']);
            unset($dataUpdateModel['updated_at']);
            foreach ($listDatabaseModel as $dbModel) {
                // $dbModel = $dbModel->name;
                try {
                    // info("SyncDatabaseAfterUpdatedModel: prepare update ".$model->id);
                    if(DB::connection($dbModel->name)->getPdo()){
                        $dbConnect = DbStatus::where('name',$dbModel->name)->first();
                        // if($dbConnect->status == 'actived'){
                            // if($dbModel->name == $db_server){continue;}
                            $model::on($dbModel->name)->where('id',$model->id)->update($dataUpdateModel);
                            // info("SyncDatabaseAfterUpdatedModel: update done".$model->id);
                        // }else{
                        //     // syncing or retry connect but status still disconnected
                        //     // throw new Throwable(); // not do sync update.
                        //     continue;
                        // }
                    }
                } catch (Throwable $th ) {
                    // info("DB not connect Update: ".$model->getTable()."  ".$model->id );
                    $dataObserveModel = [
                        "database" => $dbModel->name,
                        "table" => $model->getTable(),
                        "id_row" => $model->id,
                        "action" => "update"
                    ];
                    // info("Event updateproudct: change status ".$dbModel->name);
                    DbStatus::where('name',$dbModel->name)->update([ "status" =>"disconnected"]);
                    ObserveModel::where('id_row', $model->id)->updateOrCreate($dataObserveModel);
                    // info($model->id . " Listener db not connnect ".$dbModel->name);
                    continue;
                }
            }
        }
    }
}

if (!function_exists('SyncDatabaseAfterDeletedModel')) {
    function SyncDatabaseAfterDeletedModel($db_server,$model){
        $listDatabaseModel = DbStatus::where('model_name', '=', $model->getTable())->get();
        if(!empty($model)){
            foreach ($listDatabaseModel as $dbModel) {
                // if($dbModel->name == $event->db_server){continue;}
                try {
                    // info("SyncDatabaseAfterdletedModel: prepare delete ".$model->id);
                    if(DB::connection($dbModel->name)->getPdo()){
                        info("SyncDatabaseAfterdeletedModel: prepare delete... ".$model->id);
                        $model::on($dbModel->name)->where('id',$model->id)->delete();
                        info("SyncDatabaseAfterdeletedModel: doneeee ".$model->id);
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



