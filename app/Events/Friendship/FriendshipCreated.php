<?php

namespace App\Events\Friendship;

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

    public $user_id;
    public $profiles;
    public $friendship;
    public function __construct($friendship)
    {
        $this->user_id = $friendship->user_id;
        $profiles = [];
        foreach($friendship->permittedProfiles() as $profile){
            $profile->getBirthday();
            array_push($profiles, $profile);
        }
        $this->profiles = $profiles;
        $this->friendship = $friendship->getFriendship();
    }
    public function broadcastWith(): array
    {
        return [
            'profiles' => $this->profiles,
            'friendship' => $this->friendship,
        ];
    }
    public function broadcastOn(): array
    {
        Log::debug(new Channel('user-'.$this->friendship["user_id"]));
        return [
            new Channel('user-'.$this->user_id),
        ];
    }
}
