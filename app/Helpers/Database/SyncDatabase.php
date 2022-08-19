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
        $dbNames = DbStatus::where('model_name', '=', $model->getTable())->get();
        // info( json_encode($dbNames,true) );
        // dd($dataUpdateModel);
        $dataCreatedModel = $model->toArray();
        // info("SyncDatabaseAfterCreatedModel: ".json_encode($dataCreatedModel));
        $dataCreatedModel['created_at'] =  Carbon::parse($dataCreatedModel['created_at'])->format('Y-m-d H:i:s');
        $dataCreatedModel['updated_at'] =  Carbon::parse($dataCreatedModel['updated_at'])->format('Y-m-d H:i:s');
        // dd($dataCreatedModel);
        // info("SyncDatabaseAfterCreatedModel: ".json_encode($dataCreatedModel));
        foreach ($dbNames as $dbName) {
            $dbName = $dbName->name;
            try {
                if($dbName == $db_server){continue;}
                info("SyncDatabaseAfterCreatedModel: prepare insert data to db");
                if(DB::connection($dbName)->getPdo()){

                    Schema::connection($dbName)->disableForeignKeyConstraints();

                    // $campaignProcess = $this->campaignProcess->create($request->all());
                        $model::on($dbName)->create($dataCreatedModel);
                    Schema::connection($dbName)->enableForeignKeyConstraints();
                    info("SyncDatabaseAfterCreatedModel: insert doneeee");
                }
                //  DB::connection($dbName)
                //     ->table($model->getTable())
                //     ->insert($dataCreatedModel);

            } catch (\Throwable $th) {
                // info("SyncDatabaseAfterCreatedModel:" .$th);
                // info("DB not connect Create: ".$model->getTable()."  ".$model->id );
                $dataObserveModel = [
                    "database" => $dbName,
                    "table" => $model->getTable(),
                    "id_row" => $model->id,
                    "action" => "create"
                ];

                DbStatus::where('name',$dbName)->update([ "status" =>"disconnected"]);
                ObserveModel::where('id_row',$model->id)->updateOrCreate($dataObserveModel);

                continue;
            }
        }

    }
}

if (!function_exists('SyncDatabaseAfterUpdatedModel')) {
    function SyncDatabaseAfterUpdatedModel($db_server,$model){
        //  info("show log in function created: ");

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
                    // info("SyncDatabaseAfterUpdatedModel: prepare update ".$model->id);
                    if(DB::connection($dbName)->getPdo()){
                        $dbConnect = DbStatus::where('name',$dbName)->first();
                        // if($dbConnect->status == 'actived'){
                            if($dbName == $db_server){continue;}
                            $model::on($dbName)->where('id',$model->id)->update($dataUpdateModel);
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
                        "database" => $dbName,
                        "table" => $model->getTable(),
                        "id_row" => $model->id,
                        "action" => "update"
                    ];
                    // info("Event updateproudct: change status ".$dbName);
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
        // info("hhre event deleted");
        $dbNames = DbStatus::where('model_name', '=', $model->getTable())->get();
        // dd($dbNames);
        if(!empty($model)){
            // info("delte item exist");
            foreach ($dbNames as $dbName) {
                $dbName = $dbName->name;
                // if($dbName == $event->db_server){continue;}
                try {
                    // info("SyncDatabaseAfterdletedModel: prepare delete ".$model->id);
                    if(DB::connection($dbName)->getPdo()){
                        // dd("dlete model in otehr database where");
                        $model::on($dbName)->where('id',$model->id)->delete();
                        // info("SyncDatabaseAfterdeletedModel: prepare delete ".$model->id);
                    }
                } catch (\Throwable $th) {
                    // info("DB not connect Delete: ".$model->getTable()."  ".$model->id );
                    $dataObserveModel = [
                        "database" => $dbName,
                        "table" => $model->getTable(),
                        "id_row" => $model->id,
                        "action" => "delete"
                    ];

                    DbStatus::where('name',$dbName)->update([ "status" =>"disconnected"]);
                    ObserveModel::where('id_row', $model->id)->updateOrCreate($dataObserveModel);
                    continue;
                }
            }
        }

    }
}

// if (!function_exists('SyncDatabaseAfterDisconnect')) {
//     function SyncDatabaseAfterDisconnect(){
//         info("access function show log and run in queue...");
//     }
// }

