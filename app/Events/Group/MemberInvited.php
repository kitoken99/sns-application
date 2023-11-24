<?php

namespace App\Events\Group;

use App\Models\Profile;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MemberInvited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_id;
    public $group_id;
    public $profiles;
    public $members;
    public function __construct($group, $user_id, $ids)
    {
        $this->user_id = $user_id;
        $this->group_id = $group->id;

        $this->members = [];
        $profiles = $group->profiles();
        foreach ($profiles as $profile){
            $this->members[$profile->user_id] = $profile->id;
        }

        $this->profiles = [];
        foreach($ids as $user_id){
            $profile = Profile::whereUserId($user_id)->whereIsMain(true)->first();
            $profile->getBirthday();
            array_push($this->profiles, $profile);
        }
    }

    public function broadcastWith(): array
    {
        return [
            'group_id' => $this->group_id,
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

