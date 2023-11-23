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

class GroupCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Create a new event instance.
     */

    public $profiles;
    public $group;
    public $user_id;
    public function __construct($group, $user_id)
    {
        Log::debug("hehe");
        $this->profiles = [];
        $profiles= $group->profiles()->get();
        foreach ($profiles as $profile){
            $profile->getBirthday();
            array_push($this->profiles, $profile);
        }
        $this->user_id = $user_id;
        $this->group = $group->getGroup($this->user_id, false);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastWith(): array
{
    return [
        'profiles' => $this->profiles,
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
