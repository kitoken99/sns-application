<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomProfile;
use App\Models\User;
use App\Models\Room;
use App\Models\Profile;
use App\Models\Friend;
use App\Models\Permition;
use App\Models\PermittedProfile;
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
            $permitting_profiles = Permition::find($friend->permitting_id)->permittedProfiles()->get();
            foreach($permitting_profiles as $permitting_profile){
                    $profile = Profile::find($permitting_profile->profile_id);
                    $response[$profile->id][$friend->friend_user_id]["profile_id"] = $friend->profile_id;
                    $response[$profile->id][$friend->friend_user_id]["room_id"] = $friend->room_id;
                    $response[$profile->id][$friend->friend_user_id]["state"] = $friend->state;
                    if(!Profile::find($friend->profile_id)){
                        if(!User::find($friend->friend_user_id)){
                            $response[$profile->id][$friend->friend_user_id]["state"] = "deleted";
                        }
                    }
            }
        }
        return $response;
    }

    public function create(Request $request){
        $user = $request->user();
        $main_profile_id = $user->profiles()->whereIsMain(true)->first()->id;
        $friend_profile_id = User::find($request->friend_id)->profiles()->whereIsMain(true)->first()->id;
        $friend = Friend::whereUserId($request->user()->id)->whereFriendUserId($request->friend_id)->first();

        //room情報取得
        if ($friend==null) {
            $room_id= Room::create()->id;
            RoomProfile::create([
                "room_id" => $room_id,
                "user_id" => $user->id,
                "profile_id" => $request->profile_id,
            ]);
            RoomProfile::create([
                "room_id" => $room_id,
                "user_id" => $request->friend_id,
                "profile_id" => $friend_profile_id,
            ]);
        }else{
            $room_id = $friend->room_id;
        }

        //mainアカウントの情報追加(初回追加時)
        if(!$friend){
            $permitting_id = Permition::create()->id;
            $permitted_id = Permition::create()->id;
            $friend = Friend::create([
                "user_id" => $user->id,
                "friend_user_id" => $request->friend_id,
                "permitting_id" => $permitting_id,
                "permitted_id" => $permitted_id,
                "profile_id" => $friend_profile_id,
                "room_id" => $room_id,
                "state" => "accepted",
            ]);
            Friend::create([
                "user_id" => $request->friend_id,
                "friend_user_id" => $user->id,
                "permitting_id" => $permitted_id,
                "permitted_id" => $permitting_id,
                "profile_id" => $request->profile_id,
                "room_id" => $room_id,
                "state" => "unaccepted",
            ]);
            PermittedProfile::create([
                "permition_id" => $permitting_id,
                "profile_id" =>$main_profile_id,
            ]);
            PermittedProfile::create([
                "permition_id" => $permitted_id,
                "profile_id" =>$friend_profile_id,
            ]);
        }

        //友人情報追加
        if($main_profile_id!=$request->profile_id){
            PermittedProfile::create([
                "permition_id" => $friend->permitting_id,
                "profile_id" =>$request->profile_id,
            ]);
        }

        //他のアカウントのaccept処理
        $friend->state = "accepted";
        $friend->save();


        //レスポンスデータ
        $profile = Profile::find($friend_profile_id);
        $profile->toBase();
        $response['profile'] = $profile;
        $response['friendship'] = $friend;
        $response['profile_id'] = $request->profile_id;
        $response_room['room_id'] = $friend->room_id;
        $response_room['profile_id'] = [];
            array_push($response_room['profile_id'], $friend->profile_id);
            array_push($response_room['profile_id'],$main_profile_id);
        $response_room["name"] = $profile->name;
        $response_room['caption'] = $profile->caption;
        $response_room['image'] = $profile->image;
        $response_room['members'] = [];
        $profiles = Room::find($friend->room_id)->profiles();
        foreach ($profiles as $profile){
            $response_room["members"][$profile->user_id] = null;
        }
        $response_room['not_read'] = "0";
        $response_room['last_message'] = null;
        $response_room['last_updated_at'] = Room::find($friend->room_id)->created_at;
        $response['room'] = $response_room;
        return $response;
    }

    public function updateFeaturedProfile(Request $request){
        $friend = Friend::whereUserId($request->user()->id)->whereFriendUserId($request->friend_id)->first();
        $friend->profile_id = $request->profile_id;
        $friend->save();
        return $friend;
    }
    
    public function updatePermition(Request $request){
        $user = $request->user();
        $main_profile_id = $user->profiles()->whereIsMain(true)->first()->id;
        $friend = Friend::whereUserId($request->user()->id)->whereFriendUserId($request->friend_id)->first();
        $permitted_profiles = Permition::find($friend->permitting_id)->permittedProfiles();
        foreach($request->list as $key => $value){
            if($key == $main_profile_id)continue;
            $tempProfiles = clone $permitted_profiles;
            if($value && !$tempProfiles->whereProfileId($key)->exists()){
                PermittedProfile::create([
                    'permition_id' => $friend->permitting_id,
                    'profile_id' => $key
                ]);
            }
            if(!$value && $tempProfiles->whereProfileId($key)->exists()){
                $tempProfiles->whereProfileId($key)->first()->delete();
            }
        }
        return Permition::find($friend->permitting_id)->permittedProfiles()->get();
    }
}
