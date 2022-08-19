<?php

use Illuminate\Support\Facades\DB;
use App\Models\DbStatus;
use App\Events\Database\SyncDatabase;

if (!function_exists('getConnectDatabaseActived')) {
    function getConnectDatabaseActived($model){
//        info("IsConnect: prepare switcher db backup");
        $dbNames = DbStatus::where('model_name', '=', $model->getTable())->get();
        $table_model = $model->getTable();
        $isSelectedDatabaseToConnect = "not_selected";
        try{
            foreach ($dbNames as  $db) {
                $db = $db->name;
                try {
                    //// info("IsConnect:  try connect  ". $db);
                    if(DB::connection($db)->getPdo()){
                        //// info("IsConnect: can connect " . $db);
                        $dbConnect = DbStatus::where('name',$db)->first();
                        if($dbConnect->status == 'actived' ){
                             //info("IsConnect: repo connect succes in: " . $db);
                            if($isSelectedDatabaseToConnect == "not_selected"){
                                //// info("IsConnect: connect sucsses to: " . $db);
                                $model = $model::on($db);
                                $isSelectedDatabaseToConnect="selected";
                                continue;
                            }
                        }else{
                            //info("IsConnect: Repo syncing db: ".$db);
                            $dbActived = DbStatus::where('model_name',$table_model)
                                ->where('status',"actived")->first();
                            if($dbActived){ // normal sync
                                //info("IsConnect: normal sync");
                                DbStatus::where('name',$db)->update([ 'status' => 'syncing']);
                                event(new SyncDatabase($db,$table_model));
                                continue;

                            }else{ // disconnected all db.
                                info("All database not connected");
                                $dbLasted =  DbStatus::where('model_name',$table_model)
                                ->orderBy('updated_at','DESC')
                                ->first();
                                $dbLasted->update([ "status" =>"db_sync" ]);

                                info("IsConnect: all DB not connect choose DB sync is ".$dbLasted->name);
                                $model = $model::on($db);
                                // event(new SyncDatabase($db,$table_model));
                                event(new SyncDatabase($db,$table_model,$dbLasted->name));
                                continue;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // info($e);
                    DbStatus::where('name',$db)->update([ "status" =>"disconnected" ]);
                    continue;
                }
            }
        }catch(Throwable $e){
            //info("getConnectDatabaseActived; all db not connet ");
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
                    //hello
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
