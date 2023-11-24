<?php

namespace App\Events\Group;

use App\Models\User;
use App\Models\Group;
use App\Models\ProfileGroup;
use App\Models\Friendship;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MemberUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Create a new event instance.
     */

    public $user_id;
    public $pusher_id;
    public $group_id;
    public $room_id;
    public $profiles;
    public $members;
    public function __construct($group, $pusher_id, $user_id)
    {
        $this->user_id = $user_id;
        $this->pusher_id = $pusher_id;
        $this->group_id = $group->id;
        $this->room_id = $group->room_id;

        $this->members = [];
        $profiles = $group->profiles();
        foreach ($profiles as $profile){
            $this->members[$profile->user_id] = $profile->id;
        }

        $this->profiles = [];
        $friendship = Friendship::whereUserId($user_id)->whereFriendUserId($pusher_id)->first();
        if($friendship){
            $profiles = $friendship->permittedProfiles();
            foreach($profiles as $profile){
                $profile->getBirthday();
                $this->profiles[$profile->id] = $profile;
            }
        }
        $groups = User::find($user_id)->groups()->get();
        foreach ($groups as $group){
            $profile_group = ProfileGroup::whereGroupId($group->id)->whereUserId($pusher_id)->first();
            if($profile_group){
                $profile = $profile_group->profile()->first();
                $profile->getBirthday();
                $this->profiles[$profile->id] = $profile;
            }
        }
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->pusher_id,
            'group_id' => $this->group_id,
            'room_id' => $this->room_id,
            'profiles' => $this->profiles,
            'members' => $this->members,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('user-'.$this->user_id),
        ];
    }
}
