<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChangeStatutAgentEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;

    public $oldStatus;

    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $oldStatus, $status)
    {
        $this->user = $user;
        $this->oldStatus = $oldStatus;
        $this->status = $status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
