<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Product;
use App\Models\ObserveModel;
use App\Models\DbStatus;
use Illuminate\Support\Facades\DB;
use Throwable;
use Carbon\Carbon;

class SyncDatabaseAfterCreatedModel implements ShouldQueue
// implements ShouldQueue


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

    public function handle($event)
    {
        info("SyncDatabaseAfterCreatedModel: created product listener");
        $dbNames = DbStatus::where('model_name', '=', $event->model)->get();
        // dd( $dbNames);
        // dd($event->data);
        $dataCreatedModel = $event->data;
        // info("SyncDatabaseAfterCreatedModel: ".json_encode($dataCreatedModel));
        $dataCreatedModel['created_at'] =  Carbon::parse($dataCreatedModel['created_at'])->format('Y-m-d H:i:s');
        $dataCreatedModel['updated_at'] =  Carbon::parse($dataCreatedModel['updated_at'])->format('Y-m-d H:i:s');
        // dd($dataCreatedModel);
        info("SyncDatabaseAfterCreatedModel: ".json_encode($dataCreatedModel));
        foreach ($dbNames as $dbName) {
            $dbName = $dbName->name;
            try {
                // if($dbName == $event->db_server){continue;}
                // info("insert data to db");
                 DB::connection($dbName)
                    ->table($event->model)
                    ->insert($dataCreatedModel);

            } catch (\Throwable $th) {
                info("SyncDatabaseAfterCreatedModel:" .$th);
                $dataObserveModel = [
                    "database" => $dbName,
                    "table" => $event->model,
                    "id_row" => $event->data['id'],
                    "action" => "create"
                ];
                DbStatus::where('name',$dbName)->update([ "status" =>"disconnected"]);
                ObserveModel::where('id_row', $event->data['id'])->updateOrCreate($dataObserveModel);

                continue;
            }
        }
        // info($event->db_server);
        // info($event->product);
    }
}
