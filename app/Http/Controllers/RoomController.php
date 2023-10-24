<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
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
