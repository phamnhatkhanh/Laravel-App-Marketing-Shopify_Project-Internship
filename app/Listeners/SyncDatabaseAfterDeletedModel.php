<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Product;
use App\Models\ObserveModel;
use App\Models\DbStatus;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncDatabaseAfterDeletedModel
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
        info("hhre event deleted");
        // $dbNames = ['mysql_products','mysql_products_backup', 'mysql_products_backup_2'];
        // $dbNames = ['mysql_reviews','mysql_reviews_backup'];
        $dbNames = DbStatus::where('model_name', '=', $event->model->getTable())->get();
        if(!empty($event->model)){
            info("delte item exist");
            foreach ($dbNames as $dbName) {
                $dbName = $dbName->name;
                // // if($dbName == $event->db_server){continue;}
                try {
                    $event->model::on($dbName)->where('id',$event->model->id)->delete();
                } catch (\Throwable $th) {
                    $dataObserveModel = [
                        "database" => $dbName,
                        "table" => $event->model->getTable(),
                        "id_row" => $event->model->id,
                        "action" => "delete"
                    ];
                    info("Event updateproudct: change status ".$dbName);
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
