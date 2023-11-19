<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile_Room;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use App\Models\RoomProfile;
use App\Models\Permition;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Profiler\Profiler;

class RoomController extends Controller
{

    public function get(Request $request){
        $user = $request->user();
        $rooms = [];

        //個人ルーム
        $friends=$user->friendships()->get();
        foreach ($friends as $friend){
            if($friend->state=="not_friend"){
                continue;
            }
            $rooms[$friend->room_id] =  $friend->getRoom(true);
        }

        $groups = $request->user()->groups()->get();
        foreach($groups as $group){
            $rooms[$group->room_id] = $group->getRoom($user->id);
        }
        if(empty($rooms))
            $rooms = new \stdClass();
        return $rooms;
    }

}
