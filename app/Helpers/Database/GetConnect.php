<?php
// use App\Models\Product;
// use App\Models\User;
// use App\Models\Review;
use App\Models\DbStatus;
// use Symfony\Component\HttpFoundation\Response;
// use Throwable;
// use Illuminate\Support\Facades\Throwable;
use Illuminate\Support\Facades\DB;
use App\Events\SyncDatabase;

if (!function_exists('isConnect')) {
    function isConnect($model){
        info("IsConnect: prepare switcher db backup");
        $dbNames = DbStatus::where('model_name', '=', $model->getTable())->get();
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
                        info("IsConnect: Repo syncing db: ".$db);
                        DbStatus::where('name',$db)->update([ 'status' => 'syncing']);
                        // event(done -> chane status) +
                        event(new SyncDatabase($db));
                        continue;
                        // switch other db.
                    }
                }
            } catch (\Throwable $e) {
                info("IsConnect: Repo_db_not connect ". $db);
                info($e);
                DbStatus::where('name',$db)->update([ "status" =>"disconnected" ]);
                continue;
            }
            // return "cannot connect to database";
        }
        return $model;
    }

}
