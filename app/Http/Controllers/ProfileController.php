<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Room;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class ProfileController extends Controller
{
    public function myProfiles(Request $request){
        $profiles = $request->user()->profiles()->get();
        foreach($profiles as $profile){
            $profile->toBase();
        }
        return $profiles->pluck(null, 'id');
    }

    public function get(Request $request){
        $response = [];
        $default_profile = [
            "name" => "unknown",
            "caption" => "",
            "image" => base64_encode(Storage::get("public/profiles/user_default.image.png")),
            "show_barthdays" => false,
        ];
        //my profiles
        $profiles = $request->user()->profiles()->get();
        foreach($profiles as $profile){
            $profile->toBase();
            $response[$profile->user_id][$profile->id] = $profile;
        }
        //friends profiles
        $friends = $request->user()->friends()->get();
        foreach ($friends as $friend){
            $profile = Profile::find($friend->friend_profile_id);
            if($profile){
                $profile->toBase();
                $response[$profile->user_id][$profile->id] = $profile;
            }else{
                $response[$friend->friend_user_id][$friend->friend_profile_id] = $default_profile;
                $response[$friend->friend_user_id][$friend->friend_profile_id]["user_id"] = $friend->friend_user_id;
            }
        }
        //profiles in group
        $groups = $request->user()->groups()->get();
        foreach ($groups as $group){
            $profiles = Room::find($group->room_id)->profiles();
            foreach ($profiles as $profile){
                if($profile){
                    $profile->toBase();
                    $response[$profile->user_id][$profile->id] = $profile;
                }else{
                    $response[$friend->friend_user_id][$friend->friend_profile_id] = $default_profile;
                    $response[$friend->friend_user_id][$friend->friend_profile_id]["user_id"] = $friend->friend_user_id;
                }
            }

        }
        return $response;
    }

    public function find(Request $request){
        $email = $request->input('email');
        $user = User::whereEmail($email)->first();

        $profile = $user->profiles()->whereIsMain(true)->first();
        $profile->toBase();
        return $profile;
    }

    public function create(Request $request){
        if($request->file('image')){
            $request->file('image')->store('public/profiles');
            $file_name = $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('public/profiles', $file_name);
            $profile = Profile::create([
                'user_id' => $request->user()->id,
                'name' => $request->name,
                'account_type' => $request->account_type,
                'image' => $file_name ,
                'caption' => $request->caption,
            ]);
        }else{
            $profile = Profile::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'account_type' => $request->account_type,
            'caption' => $request->caption,
            ]);
        }
        $profile = Profile::find($profile->id);
        $profile->toBase();
        return $profile;
    }
}
