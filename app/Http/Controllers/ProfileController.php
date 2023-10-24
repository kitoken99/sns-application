<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function index(Request $request){
        $userId = $request->userId;
        $profiles = Profile::whereUserId($userId)->get();
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
