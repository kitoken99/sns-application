<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function index(Request $request){
        $profileId = $request->profileId;
        $profile = Profile::whereId($profileId)->first();
        $members = $profile->members()->get();
        $rooms = [];
        foreach ($members as $member){
            array_push($rooms, $member->room()->first());
        }
        return $rooms;
    }
    public function register(Request $request)
    {
        $room = Room::create([
            'name' => "",
          ]);

        $memberIds = $request->memberIds;

        foreach ($memberIds as $memberId) {
            Member::create([
                'profile_id' => $memberId,
                'room_id' => $room->id,
            ]);
        };
        $emails = $request->emails;
        foreach ($emails as $email) {
            $user = User::whereEmail($email)->first();
            $profile = $user->profiles()->whereAccountType('authenticator')->first();
            Member::create([
                'profile_id' => $profile->id,
                'room_id' => $room->id,
            ]);
        };
    }
}
