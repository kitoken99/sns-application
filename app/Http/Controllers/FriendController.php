<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile_Room;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use App\Models\Message;
use App\Models\Friend;
use Illuminate\Support\Facades\Log;


class FriendController extends Controller
{
    public function myFriends(Request $request){
        $records = $request->user()->messages()->where('is_read', false);
        $friends = $request->user()->friends()->get();
        $profiles = [];
        foreach($friends as $friend){
            $profile = Profile::find($friend->friend_profile_id);
            $profile->toBase();
            $profile["state"] = $friend->state;
            $profile["my_profile_id"] = $friend->profile_id;
            $profile['room_id'] = $friend->room_id;
            $profile['not_read'] = $records->whereRoomId($friend->room_id)->count();

            //ラストメッセージ
            $last_message = Room::find($friend->room_id)->messages()->latest('created_at')->first();
            if($last_message){
                $last_message['name'] = Profile::whereUserId($last_message->user_id)->first()->name;
                $profile['last_updated_at'] = $last_message->created_at;
            }else{
                $profile['last_updated_at'] = Room::find($friend->room_id)->created_at;
            }
            $profile['last_message'] = $last_message;
            //メンバーズ
            $members =[
              $friend->user_id => Profile::find($friend->profile_id),
              $friend->friend_user_id => Profile::find($friend->friend_profile_id),
            ];
            foreach($members as $member){
                $member->toBase();
                if($member->show_birthday)$member['birthday'] = $member->user->birthday;
            }
            $profile["members"] = $members;
            if($profile->show_birthday)$profile['birthday'] = $profile->user->birthday;
            array_push($profiles, $profile);
        }
        return $profiles;
    }

    public function addFriend(Request $request){
        $room = Room::create();
        $user = $request->user();
        $friend = Friend::create([
            "user_id" => $user->id,
            "profile_id" => $request->profile_id,
            "friend_user_id" => $request->friend_id,
            "friend_profile_id" => $request->friend_profile_id,
            "room_id" => $room->id,
            "state" => "accepted",
        ]);
        Friend::create([
            "user_id" => $request->friend_id,
            "profile_id" => $request->friend_profile_id,
            "friend_user_id" => $user->id,
            "friend_profile_id" => $request->profile_id,
            "room_id" => $room->id,
        ]);

        //レスポンスデータ
        $profile = Profile::find($friend->friend_profile_id);
        $profile->toBase();
        $profile["state"] = $friend->state;
        $profile["my_profile_id"] = $friend->profile_id;
        $profile['not_read'] = "0";
        $profile['room_id'] = $friend->room_id;
        $profile['last_message'] = null;
        if($profile->show_birthday)$profile['birthday'] = $profile->user->birthday;
        Log::debug($profile->pluck(null, "room_id"));
        return $profile;
    }



}
