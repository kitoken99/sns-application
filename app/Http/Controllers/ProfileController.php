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
        $profiles = Profile::whereUserId($userId)->get();
        forEach($profiles as $profile){
            $filePath = "public/profiles/" . $profile->image;
        if (Storage::exists($filePath)) {
            $profile->image = base64_encode(Storage::get($filePath));
        }
        }
        return $profiles;
    }



    public function register(Request $request){
        $profile = Profile::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'account_type' => $request->account_type,
        ]);
        return $profile;
    }
}
