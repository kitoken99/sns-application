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
        $profiles = User::whereId($userId)->get();
        return $profiles;
    }

}
