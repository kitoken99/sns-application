<?php

namespace App\Events\Group;

use App\Models\Group;


class GroupEvent
{
    public function __construct($event, $group_id, $pusher_id, $ids=[])
    {
        $group = Group::find($group_id);
        $users = $group->users()->get();
        foreach ($users as $user){
            if($user->id == $pusher_id) continue;
            if($event == "MemberUpdated") broadcast(new MemberUpdated($group, $pusher_id, $user->id));
            if($event == "GroupCreated") broadcast(new GroupCreated($group, $user->id));
            if($event == "GroupUpdated") broadcast(new GroupUpdated($group, $user->id));
            if($event == "MemberInvited" && in_array($user->id, $ids)) broadcast(new MemberInvited($group, $user->id, $ids));
            if($event == "MemberInvited" && !in_array($user->id, $ids)) broadcast(new GroupCreated($group, $user->id));
        }
    }
}
