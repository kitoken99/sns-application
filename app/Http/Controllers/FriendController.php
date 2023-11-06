<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomProfile;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use App\Models\Message;
use App\Models\Friend;

use Illuminate\Support\Facades\Log;


class FriendController extends Controller
{
    public function get(Request $request){
        $response = [];
        $profiles = $request->user()->profiles()->get();
        foreach ($profiles as $profile){
            $response[$profile->id] = [];
        }
        $friends = $request->user()->friends()->get();
        foreach($friends as $friend){
            if($friend->is_top){
                $response[$friend->profile_id][$friend->friend_user_id] = $friend->friend_profile_id;
            }
        }
        return $response;
    }

    public function addFriend(Request $request){
        $room = Friend::whereUserId($request->user()->id)->whereFriendUserId($request->friend_id)->first();
        $user = $request->user();
        if ($room==null) {
            $room= Room::create();
            RoomProfile::create([
                "room_id" => $room->id,
                "user_id" => $user->id,
                "profile_id" => $request->profile_id,
            ]);
            RoomProfile::create([
                "room_id" => $room->id,
                "user_id" => $request->friend_id,
                "profile_id" => $request->friend_profile_id,
            ]);
        }
        $main_profile_id = $user->profiles()->whereIsMain(true)->first()->id;
        $friend = Friend::create([
            "user_id" => $user->id,
            "profile_id" => $request->profile_id,
            "friend_user_id" => $request->friend_id,
            "friend_profile_id" => $request->friend_profile_id,
            "room_id" => $room->id,
            "is_top" =>true,
            "state" => "accepted",
        ]);
        Friend::create([
            "user_id" => $request->friend_id,
            "profile_id" => $request->friend_profile_id,
            "friend_user_id" => $user->id,
            "friend_profile_id" => $request->profile_id,
            "room_id" => $room->id,
            "is_top" => !Friend::whereUserId($request->friend_id)->whereProfileId($request->friend_profile_id)->whereFriendUserId($user->id)->exists(),
            "state" => "unaccepted",
        ]);
        if($main_profile_id!==$request->profile_id&&!Friend::whereUserId($user->id)->whereProfileId($main_profile_id)->whereFriendProfileId($request->friend_profile_id)->exists()){
            Friend::create([
                "user_id" => $user->id,
                "profile_id" => $main_profile_id,
                "friend_user_id" => $request->friend_id,
                "friend_profile_id" => $request->friend_profile_id,
                "room_id" => $room->id,
                "is_top" =>!Friend::whereUserId($user->id)->whereProfileId($main_profile_id)->whereFriendUserId($request->friend_id)->exists(),
                "state" => "accepted",
            ]);
            Friend::create([
                "user_id" => $request->friend_id,
                "profile_id" => $request->friend_profile_id,
                "friend_user_id" => $user->id,
                "friend_profile_id" => $main_profile_id,
                "room_id" => $room->id,
                "is_top" => false,
                "state" => "unaccepted",
            ]);
        }


        $profiles = $request->user()->profiles()->get();
        foreach ($profiles as $profile){
            $response[$profile->id] = [];
        }
        $friends = $request->user()->friends()->get();
        foreach($friends as $friend){
            if($friend->is_top){
                $response[$friend->profile_id][$friend->friend_user_id] = $friend->friend_profile_id;
            }
        }
        return $response;
        //レスポンスデータ
        $profile = Profile::find($friend->friend_profile_id);
        $profile->toBase();
        $response['profile'] = $profile;
        // $response['friendship'] = $friend;
        // $response_room['room_id'] = $friend->room_id;
        // $response_room['profile_id'] = $friend->profile_id;
        // $response_room["name"] = $profile->name;
        // $response_room['caption'] = $profile->caption;
        // $response_room['image'] = $profile->image;
        // $response_room['members'] = [];
        // $profiles = Room::find($friend->room_id)->profiles();
        // foreach ($profiles as $profile){
        //     array_push( $response_room["members"] , $profile->id);
        // }
        // $response_room['not_read'] = "0";
        // $response_room['last_message'] = null;
        // $response['room'] = $response_room;
        return $response;
    }
}
