<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\ObserveModel;
use App\Models\DbStatus;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncDatabaseAfterDeletedModel implements ShouldQueue
// implements ShouldQueue
// DeletedProductListener
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
        info("hhre event deleted");
        $dbNames = DbStatus::where('model_name', '=', $event->model->getTable())->get();
        // dd($dbNames);
        if(!empty($event->model)){
            info("delte item exist");
            foreach ($dbNames as $dbName) {
                $dbName = $dbName->name;
                // if($dbName == $event->db_server){continue;}
                try {
                    // dd("dlete model in otehr database where");
                    $event->model::on($dbName)->where('id',$event->model->id)->delete();
                } catch (\Throwable $th) {
                    $dataObserveModel = [
                        "database" => $dbName,
                        "table" => $event->model->getTable(),
                        "id_row" => $event->model->id,
                        "action" => "delete"
                    ];

                    // info("Event updateproudct: change status ".$dbName);
                    DbStatus::where('name',$dbName)->update([ "status" =>"disconnected"]);
                    ObserveModel::where('id_row', $event->model->id)->updateOrCreate($dataObserveModel);
                    continue;
                }
            }
        }
        // info($event->db_server);
        // info($event->product);
        info("excute delete event done");
    }
}
