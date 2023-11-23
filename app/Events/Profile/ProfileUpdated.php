<?php

namespace App\Events\Profile;

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

class ProfileUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Create a new event instance.
     */

    public $profile;
    public function __construct( $profile)
    {
        $this->profile = $profile;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $this->profile->getBirthday();
        $user_ids = [];
        $channels = [];
        $friendships = User::find($this->profile->user_id)->friendships()->get();
        foreach ($friendships as $friendship){
            foreach($friendship->permittingProfiles() as $profile){
                if($profile->id == $this->profile->id){
                    array_push($user_ids, $friendship->friend_user_id);
                }
            }
        }
        $groups = $this->profile->groups()->get();
        foreach ($groups as $group){
            $profiles= $group->profiles()->get();
            foreach ($profiles as $profile){
                if($profile->id != $this->profile->id && !in_array($user_ids, [$profile->user_id])){
                    array_push($user_ids, $profile->user_id);
                }
            }

        }
        foreach($user_ids as $user_id){
            array_push($channels, new Channel('user-'.$user_id, $this->profile));
        }
        return $channels;
    }
}
