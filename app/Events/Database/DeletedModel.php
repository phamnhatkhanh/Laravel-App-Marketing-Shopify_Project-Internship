<?php

namespace App\Events\Database;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeletedModel
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The current connection of model
     *
     * @var string
     */
    public $dbConnectName;

    /**
     * * The model being created.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    public function __construct($dbConnectName,$model)
    {
        $this->dbConnectName = $dbConnectName;
        $this->model = $model;
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
