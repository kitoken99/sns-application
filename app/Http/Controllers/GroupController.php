<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile_Room;

use App\Events\Group\GroupEvent;
use App\Models\Room;
use App\Models\Group;
use App\Models\RoomProfile;
use App\Models\Profile;
use App\Models\Message;
use App\Models\ProfileGroup;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{

    public function get(Request $request){
        $response = [];
        $groups = $request->user()->groups()->get();
        foreach($groups as $group){
            $response[$group->id] = $group->getGroup($request->user()->id);
        }
        if(empty($response))$response = new \stdClass();
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
            "state" => "joined"
        ]);
        foreach ($request->ids as $user_id){
            $profile = Profile::whereUserId($user_id)->whereIsMain(true)->first();
            ProfileGroup::create([
                "user_id" => $user_id,
                "profile_id" => $profile->id,
                "group_id" => $group->id
            ]);
        };

        $group = Group::find($group->id);
        event(new GroupEvent("GroupCreated", $group->id, $request->user()->id));

        return $group->getGroup($request->user()->id);


    }

    public function update(Request $request, $id){
        $group = Group::find($id);
        $group->name = $request->name;
        $group->caption = $request->caption;
        $group->saveImage($request->file('image'));
        $group->save();
        event(new GroupEvent("GroupUpdated", $group->id, $request->user()->id));
        return $group->getGroup($request->user()->id);
    }

    public function invite(Request $request, $id){
        $profiles = [];
        foreach ($request->ids as $user_id){
            $profile = Profile::whereUserId($user_id)->whereIsMain(true)->first();
            $profile->setProfile();
            array_push($profiles, $profile);
            ProfileGroup::create([
                "user_id" => $user_id,
                "profile_id" => $profile->id,
                "group_id" => $id
            ]);
        };
        $group = Group::find($id);
        event(new GroupEvent("MemberInvited", $group->id, $request->user()->id, ['ids' => $request->ids]));
        $members = [];
        $group_profiles = $group->profiles();
        foreach ($group_profiles as $profile){
            $members[$profile->user_id] = $profile->id;
        }
        return [
            "profiles" => $profiles,
            "members" => $members
        ];
    }
    public function getImage(Request $request){
        $filePath = "public/group-images/" . $request->image;
        if (Storage::exists($filePath)) {
            $image = base64_encode(Storage::get($filePath));
        }else{
            $image = base64_encode(Storage::get("public/group-images/user_default.image.png"));
        }
        return $image;
    }

    public function switchProfile(Request $request){
        $profile_group = ProfileGroup::whereUserId($request->user()->id)->whereGroupId($request->group_id)->first();
        $profile_group->update([
            "profile_id" => $request->profile_id
        ]);
        $group = Group::find($request->group_id);

        event(new GroupEvent("MemberUpdated", $group->id, $request->user()->id));
        return $group->getGroup($request->user()->id);
    }


    public function state(Request $request){
      $profile_group = ProfileGroup::whereUserId($request->user()->id)->whereGroupId($request->group_id)->first();
      $profile_group->update([
        "state" => $request->state
      ]);
    }
}

