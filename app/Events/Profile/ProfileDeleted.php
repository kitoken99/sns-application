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

class ProfileDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Create a new event instance.
     */

    public $profile;
    public $user_id;
    public $profile_id;
    public $main_profile_id;
    public function __construct( $profile)
    {
        $profile->getDefaultProfile();
        $this->profile = $profile;
        $this->user_id = $profile->user_id;
        $this->profile_id = $profile->id;
        $this->main_profile_id = User::find($this->user_id)->profiles()->whereIsMain(true)->first()->id;
    }

    public function broadcastWith(): array
    {
    return [
        'profile' => $this->profile,
        'user_id' => $this->user_id,
        'profile_id' => $this->profile_id,
        'main_profile_id' => $this->main_profile_id,
    ];
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $user_ids = [];
        $channels = [];
        $friendships = User::find($this->profile->user_id)->friendships()->get();
        array_push($user_ids, $this->user_id);
        foreach ($friendships as $friendship){
            foreach($friendship->permittingProfiles() as $profile){
                if($profile->id == $this->profile_id){
                    array_push($user_ids, $friendship->friend_user_id);
                }
            }
        }
        $groups = $this->profile->groups()->get();
        foreach ($groups as $group){
            $profiles= $group->profiles();
            foreach ($profiles as $profile){
                if($profile->id != $this->profile_id && !in_array($user_ids, [$profile->user_id])){
                    array_push($user_ids, $profile->user_id);
                }
            }

        }
        foreach($user_ids as $user_id){
            array_push($channels, new Channel('user-'.$user_id, $this->profile->getDefaultProfile()));
        }
        return $channels;
    }
}
