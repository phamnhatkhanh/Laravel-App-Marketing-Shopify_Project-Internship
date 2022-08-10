<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Product;
use App\Models\ObserveModel;
use App\Models\DbStatus;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncDatabaseAfterUpdatedModel
// UpdatedProductListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */

    public function handle($event)
    {

        $dbNames = DbStatus::where('model_name', '=', $event->model->getTable())->get();
        // dd($dbNames);
        if(!empty($event->model)){
            //  dd($event->model);
            $dataUpdateModel = $event->model->toArray();
            unset($dataUpdateModel['created_at']);
            unset($dataUpdateModel['updated_at']);
            // info($dataUpdateModel);

            foreach ($dbNames as $dbName) {
                $dbName = $dbName->name;
                try {
                    // dd($dbName);
                    if(DB::connection($dbName)->getPdo()){
                        $dbConnect = DbStatus::where('name',$dbName)->first();
                        // if($dbConnect->status == 'actived'){
                            if($dbName == $event->db_server){continue;}
                            $event->model::on($dbName)->where('id',$event->model->id)->update($dataUpdateModel);
                        // }else{
                        //     // syncing or retry connect but status still disconnected
                        //     // throw new Throwable(); // not do sync update.
                        //     continue;
                        // }
                    }
                } catch (Throwable $th ) {

                    $dataObserveModel = [
                        "database" => $dbName,
                        "table" => $event->model->getTable(),
                        "id_row" => $event->model->id,
                        "action" => "update"
                    ];
                    info("Event updateproudct: change status ".$dbName);
                    DbStatus::where('name',$dbName)->update([ "status" =>"disconnected"]);
                    ObserveModel::where('id_row', $event->model->id)->updateOrCreate($dataObserveModel);
                    // info($event->model->id . " Listener db not connnect ".$dbName);
                    continue;
                }
            }
        }
    }
}

