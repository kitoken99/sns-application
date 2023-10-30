<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile_Room;
use App\Models\Room;
use App\Models\Group;
use App\Models\User;
use App\Models\Profile;
use App\Models\Message;
use App\Models\ProfileGroup;


class GroupController extends Controller
{

    public function myGroups(Request $request){
        $records = $request->user()->messages()->where('is_read', false);
        $groups = $request->user()->groups()->get();
        foreach($groups as $group){
            $group->toBase();
            $group['not_read'] = $records->whereRoomId($group->room_id)->count();
            $last_message = Room::find($group->room_id)->messages()->latest('created_at')->first();
            if($last_message)$last_message['name'] = $group->profiles()->whereUserId($last_message->user_id)->first()->name;
            $group['last_message'] = $last_message;
            $group["members"] = $group->profiles()->get()->pluck(null, "user_id");;
            foreach($group["members"] as $profile){
                $profile->toBase();
                if($profile->show_birthday)$profile['birthday'] = $profile->user->birthday;
            }
        }
        return $groups;
    }

    public function addGroup(Request $request){
        $room = Room::create();
        $group = new Group();
        $group->fill([
            "name" => $request->name,
            "caption" => $request->caption,
            "room_id" => $room->id,
        ]);
        if($request->file('image')){
            $request->file('image')->store('public/group-images');
            $file_name = $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('public/group-images', $file_name);
            $group->fill(["image" => $file_name,]);
        }
        $group->save();

        ProfileGroup::create([
            "user_id" => $request->user()->id,
            "profile_id" => $request->profile_id,
            "group_id" => $group->id,
            "state" => "accepted"
        ]);
        $group_member_user_ids = $request->ids;
        foreach ($group_member_user_ids as $user_id){
            $profile = Profile::whereUserId($user_id)->whereIsMain(true)->first();
            ProfileGroup::create([
                "user_id" => $user_id,
                "profile_id" => $profile->id,
                "group_id" => $group->id
            ]);
        };


        $group = Group::find($group->id);
        $group->toBase();
        $profile["state"] = "accepted";
        $profile['not_read'] = "0";
        $profile['room_id'] = $room->id;
        $profile['last_message'] = null;
        $group["members"] = $group->profiles()->get()->pluck(null, "user_id");;
        foreach($group["members"] as $profile){
            $profile->toBase();
            if($profile->show_birthday)$profile['birthday'] = $profile->user->birthday;

        }
        return $group;


    }
}

