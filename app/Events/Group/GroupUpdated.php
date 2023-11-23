<?php

namespace App\Events\Group;

use App\Models\ProfileGroup;
use App\Models\Profile;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GroupUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Create a new event instance.
     */

    public $user_id;
    public $group;
    public function __construct($group, $user_id)
    {
        $this->group = $group->getGroup($user_id, false);
        $this->user_id = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastWith(): array
{
    return [
        'group' => $this->group,
    ];
}
    public function broadcastOn(): array
    {
        return [
            new Channel('user-'.$this->user_id),
        ];
    }
}
