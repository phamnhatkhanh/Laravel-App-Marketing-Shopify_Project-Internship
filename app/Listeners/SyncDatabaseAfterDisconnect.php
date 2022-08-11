<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\ObserveModel;
use App\Models\DbStatus;
use DB;
use Throwable;
class SyncDatabaseAfterDisconnect
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
        // dd($event->model);
        $dbNames = DbStatus::where('model_name', '=', $event->model)->get();

        $listDataNeedSync = ObserveModel::where('database',$event->databaseSync)->get();
        info("SyncDatabaseAfterDisconnect list item sync: ".json_encode($listDataNeedSync));
        foreach ($dbNames as $dbName) {
            $dbName = $dbName->name;
            info("DB: ".$dbName);
            // info("SyncDatabaseAfterDisconnect: find DB activing base on sync ");
            try {
                // info("find db active: ".$dbName);
                if(DB::connection($dbName)->getPdo()){
                    $dbConnect = DbStatus::where('name',$dbName)->first();
                    // info("SyncDatabaseAfterDisconnect: find db connect ".$dbName);
                    if(
                        ($dbConnect->status == 'actived')){
                        info("SyncDatabaseAfterDisconnect: get DB is active in DB ".$dbName);
                        // info($listDataNeedSync);

                        foreach ($listDataNeedSync as $dataNeedSync) {
                            info($dataNeedSync);
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
                                    info("do action ");
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
                }else{
                    info("SyncDatabaseAfterDisconnect: can not connect".$dbName);
                }
            } catch (Throwable $th ) {
                // info($th);
                info("SyncDatabaseAfterDisconnect: try other db active");
                continue;
            }
        }

    }
}
