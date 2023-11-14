<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile_Room;
use App\Models\Room;
use App\Models\Group;
use App\Models\RoomProfile;
use App\Models\Profile;
use App\Models\Message;
use App\Models\ProfileGroup;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{

    public function get(Request $request){
        $response = [];
        $profiles = $request->user()->profiles()->get();
        $groups = $request->user()->groups()->get();
        foreach ($profiles as $profile){
            $response[$profile->id] = [];
            if($profile->is_main){
                $main_profile_id = $profile->id;
            }
        }
        foreach($groups as $group){
            $group->toBase();
            $group_data['id'] = $group->id;
            $group_data['profile_id'] = $group->profile_id;
            $group_data['name'] = $group->name;
            $group_data['caption'] = $group->caption;
            $group_data['image'] = $group->image;
            $group_data['members'] = [];
            $group_data['room_id'] = $group->room_id;
            $roomProfiles = RoomProfile::whereRoomId($group->room_id)->get();
            foreach ($roomProfiles as $roomProfile){
                $group_data["members"][$roomProfile->user_id] = $roomProfile->profile_id;
            }
            $group_data['state'] = $group->pivot->state;
            $response[$group->pivot->profile_id][$group->id] = $group_data;
            if($main_profile_id!=$group->pivot->profile_id){
                $response[$main_profile_id][$group->id] = $group_data;
            }
        }
        return $response;
    }

    public function create(Request $request){
        $room = Room::create();
        $group = new Group();
        $group->fill([
            "name" => $request->name,
            "caption" => $request->caption,
            "room_id" => $room->id,
        ]);
        if($request->file('image')){
            $group->saveImage($request->file('image'));
        }
        $group->save();

        ProfileGroup::create([
            "user_id" => $request->user()->id,
            "profile_id" => $request->profile_id,
            "group_id" => $group->id,
            "state" => "accepted"
        ]);
        RoomProfile::create([
            "room_id" => $room->id,
            "user_id" => $request->user()->id,
            "profile_id" => $request->profile_id,
        ]);
        $group_member_user_ids = $request->ids;
        foreach ($group_member_user_ids as $user_id){
            $profile = Profile::whereUserId($user_id)->whereIsMain(true)->first();
            ProfileGroup::create([
                "user_id" => $user_id,
                "profile_id" => $profile->id,
                "group_id" => $group->id
            ]);
            RoomProfile::create([
                "room_id" => $room->id,
                "user_id" => $user_id,
                "profile_id" => $profile->id,
            ]);
        };
        //レスポンスデータ
        //プロファイルID
        $group = Group::find($group->id);
        $profile_id = [];
        array_push($profile_id, Profile::find($request->profile_id)->id);
        array_push($profile_id, $request->user()->profiles()->whereIsMain(true)->first()->id);
        $response["profile_id"] = $profile_id;
        //グループデータ
        $group->toBase();
        $group_data['id'] = $group->id;
        $group_data['name'] = $group->name;
        $group_data['caption'] = $group->caption;
        $group_data['image'] = $group->image;
        $group_data['room_id'] = $group->room_id;
        $group_data['members'] = [];
        $profiles = Room::find($group->room_id)->profiles();
        foreach ($profiles as $profile){
            $group_data["members"][$profile->user_id] = $profile->id;
        }
        $group_data['state'] = "accepted";
        $response["group"] = $group_data;
        //ルームデータ
        $response_room['room_id'] = $group->room_id;
        $response_room['profile_id'] = $profile_id;
        $response_room["name"] = $group->name;
        $response_room['caption'] = $group->caption;
        $response_room['image'] = $group->image;
        $response_room['members'] = [];
        $profiles = Room::find($group->room_id)->profiles();
        foreach ($profiles as $profile){
            $response_room["members"][$profile->user_id] = $profile->id;
        }
        $response_room['not_read'] = "0";
        $response_room['last_message'] = null;
        $response_room['last_updated_at'] = Room::find($group->room_id)->created_at;
        $response['room'] = $response_room;
        return $response;


    }
}

