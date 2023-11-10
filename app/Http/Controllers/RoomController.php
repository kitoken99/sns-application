<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile_Room;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use App\Models\RoomProfile;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Profiler\Profiler;

class RoomController extends Controller
{

    public function get(Request $request){
        $user = $request->user();
        $profiles = $user->profiles()->get();
        $rooms = [];
        foreach ($profiles as $profile){
            if($profile->is_main){
                $main_profile_id = $profile->id;
            }
        }
        $records = $request->user()->messages()->where('is_read', false);

        //個人ルーム
        $friends=$user->friends()->get();
        foreach ($friends as $friend){
            if($friend->state=="not_friend"){
                continue;
            }
            //ルームデータ
            if (isset($rooms[$friend->room_id])) {
                array_push($rooms[$friend->room_id]['profile_id'], $friend->profile_id);
                continue;
            }
            $profile = Profile::find($friend->friend_profile_id);
            if($profile)$profile->toBase();
            $room['room_id'] = $friend->room_id;
            $room['profile_id'] = [];
            array_push($room['profile_id'], $friend->profile_id);
            $room["name"] = $profile?$profile->name:"unknown";
            $room['caption'] = $profile?$profile->caption:"";
            $room['image'] = $profile?$profile->image:base64_encode(Storage::get( "public/profiles/user_default.image.png"));
            $room['members'] = [];
            $room_profiles = RoomProfile::whereRoomId($friend->room_id)->get();
            foreach ($room_profiles as $room_profile){
                $room["members"][$room_profile->user_id] = null;
            }
            $room['not_read'] = $records->whereRoomId($friend->room_id)->count();
            $last_message = Room::find($friend->room_id)->messages()->latest('created_at')->first();
            if($last_message){
                $last_message['name'] = Profile::whereUserId($last_message->user_id)->first()->name;
                $room['last_updated_at'] = $last_message->created_at;
            }else{
                $room['last_updated_at'] = Room::find($friend->room_id)->created_at;
            }
            $room['last_message'] = $last_message;
            $rooms[$friend->room_id] = $room;
            if($main_profile_id!=$friend->profile_id){
                $rooms[$friend->room_id] = $room;
            }
        }

        $groups = $request->user()->groups()->get();
        foreach($groups as $group){
            $group->toBase();
            $group_data['name'] = $group->name;
            $group_data['caption'] = $group->caption;
            $group_data['image'] = $group->image;
            $group_data['members'] = [];
            $roomProfiles = RoomProfile::whereRoomId($group->room_id)->get();
            foreach ($roomProfiles as $roomProfile){
                $group_data["members"][$roomProfile->user_id] = $roomProfile->profile_id;
            }
            $group_data['not_read'] = $records->whereRoomId($group->room_id)->count();
            $group_data['profile_id'] = [];
            array_push($group_data['profile_id'],$group->pivot->profile_id);
            array_push($group_data['profile_id'],$main_profile_id);
            $group_data['state'] = $group->pivot->state;
            $last_message = Room::find($group->room_id)->messages()->latest('created_at')->first();
            if($last_message){
                $last_message['name'] = Profile::whereUserId($last_message->user_id)->first()->name;
                $group_data['last_updated_at'] = $last_message->created_at;
            }else{
                $group_data['last_updated_at'] = Room::find($group->room_id)->created_at;
            }
            $group_data['last_message'] = $last_message;
            $group_data['room_id'] = $group->room_id;
            $rooms[$group->room_id] = $group_data;
        }
        return $rooms;
    }

}
