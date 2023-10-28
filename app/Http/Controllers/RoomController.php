<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile_Room;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use App\Models\Message;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Profiler\Profiler;

class RoomController extends Controller
{
    public function myRooms(Request $request, $profile_id){
        //ユーザーのプロファイルを取得
        $profile = Profile::find($profile_id);

        //未読数の取得
        $records = $request->user()->messages()->where('is_read', false)->get();

        //Room情報の取得
        $rooms = $profile->rooms()->get()->pluck(null, "id");
        foreach($rooms as $room){
            //メンバー情報
            $room['members'] = $room->profiles()->get();
            $filePath = "public/profiles/" . $profile->image;
            foreach($room['members'] as $member){
                $member->toBase();
            }

            //未読数
            $room['not_read'] = $records->whereRoomId($room['id'])->count();
        

            //最後のメッセージ
            $last_message = $room->messages()->latest('created_at')->first();
            $last_message['name'] = $room->profiles()->whereUserId($last_message->user_id)->first()->name;
            $room['last_message'] = $last_message;
        }
        if ($rooms->isEmpty()) {
            return (object) [];
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
            $profile = Profile::whereId($profileId)->first();
            $profile->rooms()->sync($room->id);
        };
        $emails = $request->emails;
        foreach ($emails as $email) {
            $user = User::whereEmail($email)->first();
            $profile = $user->profiles()->whereAccountType('authenticator')->first();
            $profile->rooms()->sync($room->id);
        };
    }
}
