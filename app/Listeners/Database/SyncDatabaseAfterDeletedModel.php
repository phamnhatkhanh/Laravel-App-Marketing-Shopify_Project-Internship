<?php

namespace App\Listeners\Database;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncDatabaseAfterDeletedModel implements ShouldQueue
{
    /**
     * Delete and synchronize data in the database model cluster.
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
        SyncDatabaseAfterDeletedModel($event->dbConnectName,$event->model);
    }
}
