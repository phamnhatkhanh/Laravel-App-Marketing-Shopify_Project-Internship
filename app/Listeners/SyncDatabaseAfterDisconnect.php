<?php

namespace App\Listeners;

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

        info("all disconnect ".$event->databaseChooseSync);
        $dbNames = DbStatus::where('model_name', '=', $event->model)->get();
        $listDataNeedSync = ObserveModel::where('database',$event->databaseSync)->get();
        info("SyncDatabaseAfterDisconnect list item sync: ".json_encode($listDataNeedSync));
        foreach ($dbNames as $dbName) {
             info("find db active: ".$dbName->name. " " .$dbName->status);
            // $dbName = $dbName->name;
            // info("SyncDatabaseAfterDisconnect: find DB activing base on sync ");
            try {
                $dbConnect = DbStatus::where("name",$dbName->name)->first();

                if(  !empty($event->databaseChooseSync) || DB::connection($dbName->name)->getPdo() ){
                    // info("SyncDatabaseAfterDisconnect: choose sync ". $dbName->name);

                    // info("SyncDatabaseAfterDisconnect: find db connect ".$dbName);
                    if(( !empty($event->databaseChooseSync)||$dbConnect->status == "actived" ) ){
                        info("SyncDatabaseAfterDisconnect: choose sync ". $dbName->name);
                    // if DB status db_sync is just it -> change status to actived.
                        // && ($dbName != $event->databaseSync)
                        info("SyncDatabaseAfterDisconnect: get DB is active in DB ".$dbName->name);
                        // info($listDataNeedSync);
                        if(!empty($event->databaseChooseSync)){
                                    $dbConnect->name = $event->databaseChooseSync;
                                     info("SyncDatabaseAfterDisconnect: all db nto connect choose sync ". $dbConnect->name);
                            }

                        foreach ($listDataNeedSync as $dataNeedSync) {
                            // info($dataNeedSync);
                            if($dataNeedSync->action  == "delete") {
                                //row not exist.
                                info("SyncDatabaseAfterDisconnect: delete product ".$dataNeedSync->id_row);
                                DB::connection($event->databaseSync)
                                    ->table($dataNeedSync->table)
                                    ->where('id', $dataNeedSync->id_row)
                                    ->delete();
                            }else{
                                // have exist row in DB
                                info("SyncDatabaseAfterDisconnect: ".$dataNeedSync->action." product ".$dataNeedSync->id_row);
                                // get row_data in DB connect


                                $latestData = DB::connection($dbConnect->name)
                                    ->table($dataNeedSync->table)
                                    ->where('id', $dataNeedSync->id_row)
                                    ->first();

                                    info("SyncDatabaseAfterDisconnect data: ".json_encode($latestData));
                                if(!is_null($latestData)){
                                    // info("do action ");
                                    $data = json_decode(json_encode($latestData), true);
                                    if($dataNeedSync->action  == "update") {
                                        DB::connection($event->databaseSync)
                                            ->table($dataNeedSync->table)
                                            ->where('id', $dataNeedSync->id_row)
                                            ->update($data);
                                    }else{
                                        DB::connection($event->databaseSync)
                                        ->table($dataNeedSync->table)
                                        // ->where('id', $dataNeedSync->id_row)
                                        ->insert($data);
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
                // info($th);
                info("SyncDatabaseAfterDisconnect: try other db active ". $dbName->name);
                continue;
            }
        }

    }
}
