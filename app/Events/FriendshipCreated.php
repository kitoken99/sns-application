<?php

namespace App\Events;

use App\Models\User;
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

class FriendshipCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Create a new event instance.
     */

    public $profiles;
    public $friendship;
    public $room;
    public function __construct($friendship)
    {
        $profiles = [];
        foreach($friendship->permittedProfiles() as $profile){
            $profile->getBirthday();
            array_push($profiles, $profile);
        }
        $this->profiles = $profiles;
        $this->friendship = $friendship;
        $this->room = $friendship->getRoom(False);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('user-'.$this->friendship->user_id),
        ];
    }
}
