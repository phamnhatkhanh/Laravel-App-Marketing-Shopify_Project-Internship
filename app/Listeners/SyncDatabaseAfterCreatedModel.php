<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

use Throwable;
use Carbon\Carbon;

use App\Models\Product;
use App\Models\ObserveModel;
use App\Models\DbStatus;


class SyncDatabaseAfterCreatedModel
implements ShouldQueue
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
        SyncDatabaseAfterCreatedModel($event->db_server,$event->model);
    }
}
