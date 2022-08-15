<?php

use Illuminate\Support\Facades\DB;
use App\Models\DbStatus;
use App\Events\Database\SyncDatabase;

if (!function_exists('getConnectDatabaseActived')) {
    function getConnectDatabaseActived($model){
        info("IsConnect: prepare switcher db backup");
        $dbNames = DbStatus::where('model_name', '=', $model->getTable())->get();
        $table_model = $model->getTable();
        $isSelectedDatabaseToConnect = "not_selected";
        try{
            foreach ($dbNames as  $db) {
                $db = $db->name;
                try {
                    info("IsConnect:  try connect  ". $db);
                    if(DB::connection($db)->getPdo()){
                        info("IsConnect: can connect " . $db);
                        $dbConnect = DbStatus::where('name',$db)->first();
                        if($dbConnect->status == 'actived' ){
                            //  info("IsConnect: repo connect succes in: " . $db);
                            if($isSelectedDatabaseToConnect == "not_selected"){
                                info("IsConnect: connect sucsses to: " . $db);
                                $model = $model::on($db);
                                $isSelectedDatabaseToConnect="selected";
                                continue;
                            }
                        }else{
                            info("IsConnect: Repo syncing db: ".$db);
                            $dbActived = DbStatus::where('model_name',$table_model)
                                ->where('status',"actived")->first();
                            if($dbActived){ // normal sync
                                info("IsConnect: normal sync");
                                DbStatus::where('name',$db)->update([ 'status' => 'syncing']);
                                event(new SyncDatabase($db,$table_model));
                                continue;
                            }else{ // disconnected all db.
                                $dbLasted =  DbStatus::where('model_name',"customers")
                                ->orderBy('updated_at','DESC')
                                ->first();
                                info("IsConnect: sync DB in case all DB not connect ".$dbLasted->name);
                                $dbLasted->update([ 'status' => 'db_sync']);
                                $model = $model::on($db);
                                event(new SyncDatabase($db,$table_model));
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    DbStatus::where('name',$db)->update([ "status" =>"disconnected" ]);
                    continue;
                }
            }
        }catch(Throwable $e){
            info("getConnectDatabaseActived; all db not connet ");
        }
        // dd( $model);
        return $model;
    }
}

if (!function_exists('getRandomModelId')) {
    function getRandomModelId(string $model)
    {
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
