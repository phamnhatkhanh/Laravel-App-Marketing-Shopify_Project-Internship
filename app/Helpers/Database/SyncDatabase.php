<?php

use Illuminate\Support\Facades\DB;
use App\Models\ObserveModel;
use App\Models\DbStatus;
// use Throwable;
use Carbon\Carbon;

if (!function_exists('showLog')) {
    function showLog(){
        info("access function show log and run in queue...");
    }
}

if (!function_exists('SyncDatabaseAfterCreatedModel')) {
    function SyncDatabaseAfterCreatedModel($db_server,$data,$model){
        info("SyncDatabaseAfterCreatedModel: created product listener");
        $dbNames = DbStatus::where('model_name', '=', $model)->get();
        // dd( $dbNames);
        // dd($dataUpdateModel);
        $dataCreatedModel = $data;
        // info("SyncDatabaseAfterCreatedModel: ".json_encode($dataCreatedModel));
        $dataCreatedModel['created_at'] =  Carbon::parse($dataCreatedModel['created_at'])->format('Y-m-d H:i:s');
        $dataCreatedModel['updated_at'] =  Carbon::parse($dataCreatedModel['updated_at'])->format('Y-m-d H:i:s');
        // dd($dataCreatedModel);
        info("SyncDatabaseAfterCreatedModel: ".json_encode($dataCreatedModel));
        foreach ($dbNames as $dbName) {
            $dbName = $dbName->name;
            try {
                // if($dbName == $event->db_server){continue;}
                // info("insert data to db");
                 DB::connection($dbName)
                    ->table($model)
                    ->insert($dataCreatedModel);

            } catch (\Throwable $th) {
                info("SyncDatabaseAfterCreatedModel:" .$th);
                $dataObserveModel = [
                    "database" => $dbName,
                    "table" => $model,
                    "id_row" => $dataCreatedModel['id'],
                    "action" => "create"
                ];
                // DbStatus::where('name',$dbName)->update([ "status" =>"disconnected"]);
                // ObserveModel::where('id_row', $dataUpdateModel['id'])->updateOrCreate($dataObserveModel);

                continue;
            }
        }
        // info($event->db_server);
        // info($event->product);
    }
}

if (!function_exists('SyncDatabaseAfterUpdatedModel')) {
    function SyncDatabaseAfterUpdatedModel($db_server,$model){
         info("show log in function created: ");
        showLog();
        $dbNames = DbStatus::where('model_name', '=', $model->getTable())->get();
        // dd($dbNames);
        if(!empty($model)){
            //  dd($model);
            $dataUpdateModel = $model->toArray();
            unset($dataUpdateModel['created_at']);
            unset($dataUpdateModel['updated_at']);
            // info($dataUpdateModel);

            foreach ($dbNames as $dbName) {
                $dbName = $dbName->name;
                try {
                    // dd($dbName);
                    if(DB::connection($dbName)->getPdo()){
                        $dbConnect = DbStatus::where('name',$dbName)->first();
                        // if($dbConnect->status == 'actived'){
                            if($dbName == $db_server){continue;}
                            $model::on($dbName)->where('id',$model->id)->update($dataUpdateModel);
                        // }else{
                        //     // syncing or retry connect but status still disconnected
                        //     // throw new Throwable(); // not do sync update.
                        //     continue;
                        // }
                    }
                } catch (Throwable $th ) {
                    $dataObserveModel = [
                        "database" => $dbName,
                        "table" => $model->getTable(),
                        "id_row" => $model->id,
                        "action" => "update"
                    ];
                    info("Event updateproudct: change status ".$dbName);
                    DbStatus::where('name',$dbName)->update([ "status" =>"disconnected"]);
                    ObserveModel::where('id_row', $model->id)->updateOrCreate($dataObserveModel);
                    // info($model->id . " Listener db not connnect ".$dbName);
                    continue;
                }
            }
        }
    }
}

if (!function_exists('SyncDatabaseAfterDeletedModel')) {
    function SyncDatabaseAfterDeletedModel($db_server,$model){
        // dd($model);
        info("hhre event deleted");
        $dbNames = DbStatus::where('model_name', '=', $model->getTable())->get();
        // dd($dbNames);
        if(!empty($model)){
            info("delte item exist");
            foreach ($dbNames as $dbName) {
                $dbName = $dbName->name;
                // if($dbName == $event->db_server){continue;}
                try {
                    // dd("dlete model in otehr database where");
                    $model::on($dbName)->where('id',$model->id)->delete();
                } catch (\Throwable $th) {
                    $dataObserveModel = [
                        "database" => $dbName,
                        "table" => $model->getTable(),
                        "id_row" => $model->id,
                        "action" => "delete"
                    ];
                    // info("Event updateproudct: change status ".$dbName);
                    DbStatus::where('name',$dbName)->update([ "status" =>"disconnected"]);
                    ObserveModel::where('id_row', $model->id)->updateOrCreate($dataObserveModel);
                    continue;
                }
            }
        }
        // info($event->db_server);
        // info($event->product);
        info("excute delete event done");
    }
}

if (!function_exists('SyncDatabaseAfterDisconnect')) {
    function SyncDatabaseAfterDisconnect(){
        info("access function show log and run in queue...");
    }
}

