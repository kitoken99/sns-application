<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function myRooms(Request $request){
        //ユーザーのプロファイルを取得
        $profiles = $request->user()->profiles()->get();
        $rooms = [];
        //それぞれのプロファイルが属するRoom情報を取得
        foreach ($profiles as $profile){
            $members = $profile->members()->get();
            foreach ($members as $member){
                $roomModel = $member->room()->first();
                //Room情報
                $room = $roomModel->getAttributes();

                $roomMembers = $roomModel->members()->get();
                $room['members'] = [];
                foreach ($roomMembers as $roomMember){
                    $memberProfile = $roomMember->profile()->first();
                    Log::debug($memberProfile);
                    if($memberProfile->user_id==$request->user()->id){
                        $room['account_type'] = $memberProfile->account_type;
                    }else{
                        $filePath = "public/profiles/" . $memberProfile->image;
                            if (Storage::exists($filePath)) {
                                $memberProfile->image = base64_encode(Storage::get($filePath));
                            }
                        
                        array_push($room['members'], $memberProfile->getAttributes());
                    }
                }
                array_push($rooms, $room);
            }
        }
        Log::debug($rooms);
        return $rooms;
    }
    public function register(Request $request)
    {
        $room = Room::create([
            'name' => "",
          ]);

        $profileIds = $request->profileIds;

        foreach ($profileIds as $profileId) {
            Member::create([
                'profile_id' => $profileId,
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
