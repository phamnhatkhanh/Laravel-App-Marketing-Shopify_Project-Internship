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

class SyncDatabaseAfterCreatedModel
// implements ShouldQueue
// CreatedProductListener

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
        $dbNames = DbStatus::where('model_name', '=', $event->model->getTable())->get();
        $dataCreatedModel = $event->model->toArray();
        // dd($event->model);
        info("SyncDatabaseAfterCreatedModel: ".json_encode($dataCreatedModel));

        $dataCreatedModel['created_at'] =  Carbon::parse($dataCreatedModel['created_at'])->format('Y-m-d H:i:s');
        $dataCreatedModel['updated_at'] =  Carbon::parse($dataCreatedModel['updated_at'])->format('Y-m-d H:i:s');
        info("SyncDatabaseAfterCreatedModel: ".json_encode($dataCreatedModel));
        foreach ($dbNames as $dbName) {
            $dbName = $dbName->name;
            try {
                 if($dbName == $event->db_server){continue;}
                 DB::connection($dbName)
                    ->table($event->model->getTable())
                    ->insert($dataCreatedModel);

            } catch (\Throwable $th) {
                // info("SyncDatabaseAfterCreatedModel:" .$th);
                $dataObserveModel = [
                    "database" => $dbName,
                    "table" => $event->model->getTable(),
                    "id_row" => $event->model->id,
                    "action" => "create"
                ];

                DbStatus::where('name',$dbName)->update([ "status" =>"disconnected"]);
                ObserveModel::where('id_row', $event->model->id)->updateOrCreate($dataObserveModel);

                continue;
            }
        }

        // info($event->db_server);
        // info($event->product);
    }
}
