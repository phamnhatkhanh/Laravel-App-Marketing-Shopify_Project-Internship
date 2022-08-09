<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Events\UpdatedModel;
use App\Listeners\SyncDatabaseAfterUpdatedModel;
use App\Events\DeletedModel;
use App\Listeners\SyncDatabaseAfterDeletedModel;
use App\Events\CreatedModel;
use App\Listeners\SyncDatabaseAfterCreatedModel;
use App\Events\SyncDatabase;
use App\Listeners\SyncDatabaseAfterDisconnect;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UpdatedModel::class => [
            SyncDatabaseAfterUpdatedModel::class
        ],
        DeletedModel::class => [
            SyncDatabaseAfterDeletedModel::class
        ],
        CreatedModel::class => [
            SyncDatabaseAfterCreatedModel::class
        ],
        SyncDatabase::class => [
            SyncDatabaseAfterDisconnect::class
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
