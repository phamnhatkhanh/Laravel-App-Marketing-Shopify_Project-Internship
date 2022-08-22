<?php

namespace App\Listeners\Database;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;


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
        SyncDatabaseAfterCreatedModel($event->dbConnectName,$event->model);
    }
}
