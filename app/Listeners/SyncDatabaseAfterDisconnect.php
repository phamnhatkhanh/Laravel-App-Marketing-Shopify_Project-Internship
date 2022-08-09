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
        // info("DB: syncing database: ".$event->databaseSync);
        // $dbNames = ['mysql_products','mysql_products_backup', 'mysql_products_backup_2'];
        //use name model get list driver.
        // $dbNames = ['mysql_reviews','mysql_reviews_backup'];
        $dbNames = DbStatus::where('model_name', '=', $event->model->getTable())->get();
        $listDataNeedSync = ObserveModel::where('database',$event->databaseSync)->get();

        foreach ($dbNames as $dbName) {
            $dbName = $dbName->name;
            // info("SyncDatabaseAfterDisconnect: find DB activing base on sync ");
            try {
                // info("find db active: ".$dbName);
                if(DB::connection($dbName)->getPdo()){
                    $dbConnect = DbStatus::where('name',$dbName)->first();
                    // info("SyncDatabaseAfterDisconnect: find db connect ".$dbName);
                    if(
                        ($dbConnect->status == 'actived') // not get DB sync

                    ){
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

                                $data = json_decode(json_encode($latestData), true); // true with have data.

                                // info("SyncDatabaseAfterDisconnect:". $data );
                                DB::connection($event->databaseSync)
                                    ->table($dataNeedSync->table)
                                    ->where('id', $dataNeedSync->id_row)
                                    ->updateOrInsert($data);

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
                info("SyncDatabaseAfterDisconnect: try other db active");
                info($th);
                continue;
            }
        }
    }
}
