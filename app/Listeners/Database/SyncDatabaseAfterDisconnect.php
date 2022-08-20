<?php

namespace App\Listeners\Database;

use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\ObserveModel;
use App\Models\DbStatus;
use DB;
use Throwable;
class SyncDatabaseAfterDisconnect  implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // info("all disconnect ".$event->databaseChooseSync);
        $listDatabaseModelConnect = DbStatus::where('model_name', '=', $event->model)->get();
        $listDataNeedSync = ObserveModel::where('database',$event->databaseSync)->get();
        info("SyncDatabaseAfterDisconnect list item sync: ".json_encode($listDataNeedSync));
        foreach ($listDatabaseModelConnect as $dbModel) {
             info("find db active: ".$dbModel->name. " " .$dbModel->status);
            try {
                $dbModelActived = DbStatus::where("name",$dbModel->name)->first();

                if(  !empty($event->databaseChooseSync) || DB::connection($dbModel->name)->getPdo() ){
                    // info("SyncDatabaseAfterDisconnect: choose sync ". $dbModel->name);
                    // info("SyncDatabaseAfterDisconnect: find db connect ".$dbModel);
                    if(( !empty($event->databaseChooseSync)||$dbModelActived->status == "actived" ) ){
                        // info("SyncDatabaseAfterDisconnect: choose sync ". $dbModel->name);
                    // if DB status db_sync is just it -> change status to actived.
                        // && ($dbModel != $event->databaseSync)
                        info("SyncDatabaseAfterDisconnect: get DB is active in DB ".$dbModel->name);
                        if(!empty($event->databaseChooseSync)){
                            $dbModelActived->name = $event->databaseChooseSync;
                            info("SyncDatabaseAfterDisconnect: all db nto connect choose sync ". $dbModelActived->name);
                        }
                        foreach ($listDataNeedSync as $dataNeedSync) {
                            if($dataNeedSync->action  == "delete") {
                                //row not exist.
                                info("SyncDatabaseAfterDisconnect: delete product ".$dataNeedSync->id_row);
                                DB::connection($event->databaseSync)
                                    ->table($dataNeedSync->table)
                                    ->where('id', $dataNeedSync->id_row)
                                    ->delete();
                            }else{
                                // have exist row in DB
                                // get row_data in DB connect
                                $latestData = DB::connection($dbModelActived->name)
                                ->table($dataNeedSync->table)
                                ->where('id', $dataNeedSync->id_row)
                                ->first();

                                info("SyncDatabaseAfterDisconnect data: ".json_encode($latestData));
                                if(!is_null($latestData)){
                                    $data = json_decode(json_encode($latestData), true);
                                    if($dataNeedSync->action  == "update") {
                                        info("SyncDatabaseAfterDisconnect: ".$dataNeedSync->action." product ".$dataNeedSync->id_row);
                                        DB::connection($event->databaseSync)
                                        ->table($dataNeedSync->table)
                                        ->where('id', $dataNeedSync->id_row)
                                        ->update($data);
                                    }else{
                                        info("SyncDatabaseAfterDisconnect: ".$dataNeedSync->action." product ".$dataNeedSync->id_row);
                                        Schema::connection($event->databaseSync)->disableForeignKeyConstraints();

                                            DB::connection($event->databaseSync)
                                            ->table($dataNeedSync->table)
                                            ->insert($data);
                                        Schema::connection($event->databaseSync)->enableForeignKeyConstraints();
                                    }
                                }
                            }
                            $dataNeedSync->delete();
                        }

                        info("SyncDatabaseAfterDisconnect: update stattus and delte observer");
                        DbStatus::where('name',$event->databaseSync)->update([ "status" =>"actived"]);
                        break;
                    }
                }
            } catch (Throwable $th ) {
                info($th);
                info("SyncDatabaseAfterDisconnect: try other db active ". $dbModel->name);
                continue;
            }
        }

    }
}


// 64: if sync_db not connect -> cant not get Data lasted.
