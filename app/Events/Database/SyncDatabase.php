<?php

namespace App\Events\Database;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SyncDatabase
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The current connection of model
     *
     * @var string
     */
    public $dbModelConnect;

    /**
     * Get the last database connection name of the model before the model can't connect to the database
     *
     * @var  string|null
     */
    public $dbLastedActivedModelConnect;

    /**
     * * The table model connect in database.
     *
     * @var string
     */
    public $tableModel;

    public function __construct($dbModelConnect,$tableModel,$dbLastedActivedModelConnect=null)
    {
        $this->dbModelConnect = $dbModelConnect;
        $this->tableModel = $tableModel;
        $this->dbLastedActivedModelConnect = $dbLastedActivedModelConnect;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
