<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Room;
use App\Models\Profile;


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
            $profile->toBase();
            $response[$profile->user_id][$profile->id] = $profile;
        }
        //profiles in group
        $groups = $request->user()->groups()->get();
        foreach ($groups as $group){
            $profiles = Room::find($group->room_id)->profiles();
            foreach ($profiles as $profile){
                $profile->toBase();
                $response[$profile->user_id][$profile->id] = $profile;
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
