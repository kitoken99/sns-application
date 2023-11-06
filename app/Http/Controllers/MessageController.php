<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Room;
use App\Models\Friend;
use Illuminate\Http\Request;
use App\Events\MessageRecieved;
use App\Models\MessageUser;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{

    public function roomMessages(Request $request, $room_id){
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

    public function newMessage(Request $request, $room_id){
            $message = Message::create([
                'user_id' => $request->user()->id,
                'room_id' => $room_id,
                'body' => request()->body
            ]);

            $members = Friend::whereRoomId($room_id)->get();
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
    public function readMessages(Request $request, $room_id){
        $message = MessageUser::where('message_id', $request->query('id'))->whereUserId($request->user()->id)->get();
        $message->is_read = true;
        return $message->save();

    }

    public function store(Request $request)
    {
        $message = new Message();
        $message->id = $request->message_id;
        $message->message = $request->message;
        $message->save();

        // MessageRecieved::dispatch($message);
        event(new MessageRecieved($message, $request->room_id));

        return response($message, 201);
    }


    public function show(String $id)
    {
        $messages = Message::where('room_id', $id)
                        ->orderBy(Message::CREATED_AT)->get();

        return $messages ?? abort(404);
    }
}
