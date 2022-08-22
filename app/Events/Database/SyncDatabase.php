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
     * Create a new event instance.
     *
     * @return void
     */
    public $dbModelConnect;
    public $tableModel;
    public $dbLastedActivedModelConnect;
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
