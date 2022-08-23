<?php

namespace App\Listeners\Database;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\ObserveModel;
use App\Models\DbStatus;

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
     * Synchronize the data model after the model reconnects to the database.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $listDatabaseModelConnect = DbStatus::where('model_name', '=', $event->tableModel)->get();
        $listSyncModelRow = ObserveModel::where('database',$event->dbModelConnect)->get();
        foreach ($listDatabaseModelConnect as $dbModel) {
            try {
                $dbModelActived = DbStatus::where("name",$dbModel->name)->first();

                if(!empty($event->dbLastedActivedModelConnect) || DB::connection($dbModel->name)->getPdo() ){
                    if(( !empty($event->dbLastedActivedModelConnect)||$dbModelActived->status == "actived" ) ){
                    // if DB status db_sync is just it -> change status to actived.
                        // && ($dbModel != $event->dbModelConnect)
                        if(!empty($event->dbLastedActivedModelConnect)){
                            $dbModelActived->name = $event->dbLastedActivedModelConnect;
                        }
                        foreach ($listSyncModelRow as $syncModelRow) {
                            if($syncModelRow->action  == "delete") {
                                //row not exist.
                                DB::connection($event->dbModelConnect)
                                    ->table($syncModelRow->table)
                                    ->where('id', $syncModelRow->id_row)
                                    ->delete();
                            }else{
                                // have exist row in DB
                                // get row_data in DB connect
                                $latestDataModel = DB::connection($dbModelActived->name)
                                ->table($syncModelRow->table)
                                ->where('id', $syncModelRow->id_row)
                                ->first();

                                if(!is_null($latestDataModel)){
                                    $data = json_decode(json_encode($latestDataModel), true);
                                    if($syncModelRow->action  == "update") {
                                        DB::connection($event->dbModelConnect)
                                        ->table($syncModelRow->table)
                                        ->where('id', $syncModelRow->id_row)
                                        ->update($data);
                                    }else{
                                        Schema::connection($event->dbModelConnect)->disableForeignKeyConstraints();

                                            DB::connection($event->dbModelConnect)
                                            ->table($syncModelRow->table)
                                            ->insert($data);
                                        Schema::connection($event->dbModelConnect)->enableForeignKeyConstraints();
                                    }
                                }
                            }
                            $syncModelRow->delete();
                        }

                        DbStatus::where('name',$event->dbModelConnect)->update([ "status" =>"actived"]);
                        break;
                    }
                }
            } catch (Throwable $th ) {
                continue;
            }
        }

    }
}

