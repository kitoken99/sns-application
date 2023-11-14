<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Room;
use App\Models\Profile;
use App\Models\ProfileGroup;
use App\Models\Permition;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class ProfileController extends Controller
{

    public function myProfiles(Request $request){
        $profiles = $request->user()->profiles()->get();
        foreach($profiles as $profile){
            $profile->toBase();
            $profile->birthday = $request->user()->birthday;
        }
        return $profiles->pluck(null, 'id');
    }

    public function get(Request $request){
        $response = [];
        $default_profile = [
            "name" => "unknown",
            "caption" => "",
            "image" => base64_encode(Storage::get("public/profiles/user_default.image.png")),
            "show_barthday" => false,
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
            $permitted_profiles = Permition::find($friend->permitted_id)->permittedProfiles()->get();
            foreach($permitted_profiles as $permitted_profile){
                $profile = Profile::find($permitted_profile->profile_id);
                if($profile){
                    $profile->toBase();
                    $response[$profile->user_id][$profile->id] = $profile;
                }else{
                    $response[$friend->friend_user_id][$friend->friend_profile_id] = $default_profile;
                    $response[$friend->friend_user_id][$friend->friend_profile_id]["user_id"] = $friend->friend_user_id;
                }
            }


        }
        //profiles in group
        $groups = $request->user()->groups()->get();
        foreach ($groups as $group){
            $group_profiles= ProfileGroup::whereGroupId($group->id)->get();
            foreach ($group_profiles as $group_profile){
                $profile = Profile::find($group_profile->profile_id);
                if($profile){
                    $profile->toBase();
                    $response[$profile->user_id][$profile->id] = $profile;
                }else{
                    $response[$group_profile->user_id][$group_profile->profile_id] = $default_profile;
                    $response[$group_profile->user_id][$group_profile->profile_id]["user_id"] = $group_profile->user_id;
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

    public function update(Request $request){
        $profile = Profile::find($request->input('id'));
        $profile->account_type = $request->account_type;
        $profile->name = $request->name;
        $profile->caption = $request->caption;
        $profile->show_birthday = $request->show_birthday==1;
        if($request->file('image')){
            $request->file('image')->store('public/profiles');
            $file_name = $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('public/profiles', $file_name);
            $profile->image = $file_name;
        }
        $profile->save();
        if($profile->show_birthday){
            $profile->birthday = $request->user()->birthday;
        }
        $profile->toBase();
        return $profile;
    }
}
