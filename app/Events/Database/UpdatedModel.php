<?php

namespace App\Events\Database;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdatedModel
// UpdatedProduct
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $db_server;

    public $model;

    public function __construct($db_server,$model)
    {
        $this->db_server = $db_server;
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
