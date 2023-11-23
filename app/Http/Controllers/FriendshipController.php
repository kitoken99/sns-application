<?php

namespace App\Http\Controllers;

use App\Events\Permission\PermissionUpdated;
use Illuminate\Http\Request;
use App\Models\RoomProfile;
use App\Models\User;
use App\Models\Room;
use App\Models\Profile;
use App\Models\Friendship;
use App\Models\Permition;
use App\Models\PermittedProfile;
use Illuminate\Support\Facades\Log;

class FriendshipController extends Controller
{
    public function get(Request $request){
        $response =[];
        $friendships = $request->user()->friendships()->get();
        foreach($friendships as $friendship){
            $response[$friendship->friend_user_id] = $friendship->getFriendship();
        }
        if(empty($response))$response = new \stdClass();
        return $response;
    }

    public function create(Request $request){
        $user = $request->user();
        $main_profile_id = $user->profiles()->whereIsMain(true)->first()->id;
        $friend_profile_id = User::find($request->friend_id)->profiles()->whereIsMain(true)->first()->id;

        $room_id= Room::create()->id;
        $permitting_id = Permition::create()->id;
        $permitted_id = Permition::create()->id;
        PermittedProfile::create([
            "permition_id" => $permitting_id,
            "profile_id" =>$main_profile_id,
        ]);
        PermittedProfile::create([
            "permition_id" => $permitted_id,
            "profile_id" =>$friend_profile_id,
        ]);
        $friendship = Friendship::create([
            "user_id" => $user->id,
            "friend_user_id" => $request->friend_id,
            "permitting_id" => $permitting_id,
            "permitted_id" => $permitted_id,
            "profile_id" => $friend_profile_id,
            "room_id" => $room_id,
            "state" => "accepted",
        ]);
        Friendship::create([
            "user_id" => $request->friend_id,
            "friend_user_id" => $user->id,
            "permitting_id" => $permitted_id,
            "permitted_id" => $permitting_id,
            "profile_id" => $main_profile_id,
            "room_id" => $room_id,
            "state" => "unaccepted",
        ]);

        $profile = Profile::find($friend_profile_id);
        $profile->setProfile();
        $response['profile'] = $profile;
        $response['friendship'] = $friendship->getFriendship();
        return $response;
    }

    public function updateFeaturedProfile(Request $request){
        $friend = Friendship::whereUserId($request->user()->id)->whereFriendUserId($request->friend_id)->first();
        $friend->profile_id = $request->profile_id;
        $friend->save();
        return $friend;
    }

    public function updatePermition(Request $request){
        $user = $request->user();
        $main_profile_id = $user->profiles()->whereIsMain(true)->first()->id;
        $friendship = Friendship::whereUserId($request->user()->id)->whereFriendUserId($request->friend_id)->first();
        $permitted_profiles = Permition::find($friendship->permitting_id)->permittedProfiles();
        foreach($request->list as $key => $value){
            if($key == $main_profile_id)continue;
            $tempProfiles = clone $permitted_profiles;
            if($value && !$tempProfiles->whereProfileId($key)->exists()){
                PermittedProfile::create([
                    'permition_id' => $friendship->permitting_id,
                    'profile_id' => $key
                ]);
            }
            if(!$value && $tempProfiles->whereProfileId($key)->exists()){
                $tempProfiles->whereProfileId($key)->first()->delete();
            }
        }
        broadcast(new PermissionUpdated($friendship));
        return Permition::find($friendship->permitting_id)->permittedProfiles()->get();
    }

    public function state(Request $request){
        $friendship = Friendship::whereUserId($request->user()->id)->whereFriendUserId($request->friend_id)->first();
        $friendship->state = $request->state;
        $friendship->update();
    }
}
