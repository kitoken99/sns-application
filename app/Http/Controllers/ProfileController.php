<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function myProfiles(Request $request){
        $userId = $request->user()->id;
        $profiles = Profile::whereUserId($userId)->get()->pluck(null, "id");
        forEach($profiles as $profile){
            $filePath = "public/profiles/" . $profile->image;
        if (Storage::exists($filePath)) {
            $profile->image = base64_encode(Storage::get($filePath));
        }
        }
        $profiles = $profiles->keyBy('id')->map->toArray();
        return $profiles;
    }

    public function roomProfiles($room_id){
        $profiles =  Room::find($room_id)->profiles()->get();
        foreach($profiles as $profile){
            $profile->toBase();
        }
        return $profiles->pluck(null, "id");
    }

    public function register(Request $request){
        $request->file('image')->store('public/profiles');

        $file_name = $request->file('image')->getClientOriginalName();
        $request->file('image')->storeAs('public/profiles', $file_name);
        $profile = Profile::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'account_type' => $request->account_type,
            'image' => $file_name,
            'caption' => $request->caption,

        ]);
        return $profile;
    }
}
