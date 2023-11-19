<?php

namespace App\Events\Group;

use App\Models\Group;
use App\Events\Group\MemberUpdated;
use App\Events\Group\GroupCreated;

class GroupEvent
{
    public function __construct($event, $group_id, $pusher_id)
    {
        $group = Group::find($group_id);
        $users = $group->users()->get();
        foreach ($users as $user){
            if($user->id == $pusher_id) continue;
            if($event == "MemberUpdated") broadcast(new MemberUpdated($group, $pusher_id, $user->id));
            if($event == "GroupCreated") broadcast(new GroupCreated($group, $user->id));
        }
    }
}
