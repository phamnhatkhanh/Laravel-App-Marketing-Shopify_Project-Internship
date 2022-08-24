<?php

namespace App\Listeners\Database;

use Throwable;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

use App\Models\ObserveModel;
use App\Models\DbStatus;

class SyncDatabaseAfterUpdatedModel  implements ShouldQueue
{
    /**
     * Update and synchronize data in the database model cluster.
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
        SyncDatabaseAfterUpdatedModel($event->dbConnectName,$event->model);
    }
}

