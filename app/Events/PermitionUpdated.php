<?php

namespace App\Events;

use App\Models\User;
use App\Models\Profile;
use App\Models\ProfileGroup;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PermitionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Create a new event instance.
     */

    public $profiles;
    public $user_id;
    public $friend_id;
    public function __construct($friendship)
    {
        $this->user_id = $friendship->user_id;
        $this->friend_id = $friendship->friend_user_id;
        $profiles = $friendship->permittingProfiles();
        foreach($profiles as $profile){
            $profile->getBirthday();
            $this->profiles[$profile->id] = $profile;
        }
        $groups = User::find($this->friend_id)->groups()->get();
        foreach ($groups as $group){
            $group_profiles= ProfileGroup::whereGroupId($group->id)->get();
            foreach ($group_profiles as $group_profile){
                $profile = Profile::find($group_profile->profile_id);
                if($this->user_id == $profile->user_id)
                    $this->profiles[$profile->id] = $profile;
            }
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('user-'.$this->friend_id),
        ];
    }
}
