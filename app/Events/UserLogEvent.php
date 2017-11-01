<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserLogEvent
{
    use InteractsWithSockets, SerializesModels;

    public $action;
    public $refer_id;
    public $refer_type;

    /**
     * Create a new event instance.
     *
     * @param $action
     * @param int $refer_id
     * @param string $refer_type
     */
    public function __construct($action, $refer_id = 0, $refer_type = '')
    {
        $this->action = $action;
        $this->refer_id = $refer_id;
        $this->refer_type = $refer_type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
