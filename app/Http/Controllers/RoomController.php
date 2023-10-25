<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use App\Models\MessageUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function myRooms(Request $request){
        //ユーザーのプロファイルを取得
        $profiles = $request->user()->profiles()->get();
        $records = MessageUser::where('user_id', $request->user()->id)->where('is_read', false)->get();
        $rooms = [];
        //それぞれのプロファイルが属するRoom情報を取得
        foreach ($profiles as $profile){
            $members = $profile->members()->get();
            foreach ($members as $member){
                $roomModel = $member->room()->first();
                $room = $roomModel->getAttributes();

                //未読数計算
                $notRead = 0;
                foreach ($roomModel->messages()->get() as $message){
                     $notRead = $notRead + $records->where('message_id', $message->id)->count();
                }
                $room['notRead'] = $notRead;


                $roomMembers = $roomModel->members()->get();
                $room['members'] = [];
                foreach ($roomMembers as $roomMember){
                    $memberProfile = $roomMember->profile()->first();
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
        return $rooms;
    }
    public function register(Request $request)
    {
        $room = Room::create([
            'name' => $request->name == "" ? "" : $request->name,
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
