<?php

use Illuminate\Support\Facades\DB;
use App\Models\DbStatus;
use App\Events\Database\SyncDatabase;

if (!function_exists('getConnectDatabaseActived')) {
    function getConnectDatabaseActived($model){

        info("IsConnect: prepare switcher db backup");
        $dbNames = DbStatus::where('model_name', '=', $model->getTable())->get();
        $table_model = $model->getTable();

            //  dd($dbNames);
        $isSelectedDatabaseToConnect = "not_selected";
        foreach ($dbNames as  $db) {
            $db = $db->name;

            try {
                info("IsConnect: connect_to_backup_database ". $db);
                if(DB::connection($db)->getPdo()){
                    info("IsConnect: repo connect: " . $db);
                    $dbConnect = DbStatus::where('name',$db)->first();
                    if($dbConnect->status == 'actived' ){
                        //  info("IsConnect: repo connect succes in: " . $db);
                        if($isSelectedDatabaseToConnect == "not_selected"){
                            info("IsConnect: choose database to connect to: " . $db);
                            $model = $model::on($db);
                            $isSelectedDatabaseToConnect="selected";
                            continue;
                        }
                    }else{
                        //syncing +
                        // dd($table_model);
                        info("IsConnect: Repo syncing db: ".$db);
                        DbStatus::where('name',$db)->update([ 'status' => 'syncing']);
                        event(new SyncDatabase($db,$table_model));
                        continue;
                    }
                }
            } catch (\Throwable $e) {
                // info("IsConnect: Repo_db_not connect ". $db);
                // info($e);
                DbStatus::where('name',$db)->update([ "status" =>"disconnected" ]);
                continue;
            }

        }
        // dd( $model);
        return $model;
    }

}
