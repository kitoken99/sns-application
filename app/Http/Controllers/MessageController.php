<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Room;
use App\Models\Group;
use App\Models\Friendship;
use Illuminate\Http\Request;
use App\Events\MessageRecieved;
use App\Models\MessageUser;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{

    public function get(Request $request, $room_id){
        $messages =  Room::find($room_id)->messages()->get();


        //既読処理
        $read_records = [];
        foreach($messages as $message){
            $read_records = MessageUser::where('message_id', $message->id)->where('is_read', false)->get();
            foreach($read_records as $record){
                if($request->user()->id == $record->user_id){
                    $record->is_read = true;
                    $record->save();
                }
            }
        }
        return $messages;
    }

    public function new(Request $request, $room_id){
            $message = Message::create([
                'user_id' => $request->user()->id,
                'room_id' => $room_id,
                'body' => request()->body
            ]);
            if(Friendship::whereRoomId($room_id)->exists())
                $members = Friendship::whereRoomId($room_id)->groupBy('user_id')->get(['user_id']);
            if(Group::whereRoomId($room_id)->exists())
                $members = Group::whereRoomId($room_id)->first()->profiles();

            foreach($members as $member){
                if($member->user_id != $request->user()->id){
                    MessageUser::create([
                        'user_id' => $member->user_id,
                        'message_id' => $message->id,
                ]);
            }
            }
            broadcast(new MessageRecieved($message));
            return $message;
    }

    public function read(Request $request){
        Log::debug($request->id);
        $message = MessageUser::whereMessageId($request->id)->whereUserId($request->user()->id)->first();
        $message->is_read = true;
        return $message->save();

    }
}
