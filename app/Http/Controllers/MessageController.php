<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Events\MessageRecieved;

class MessageController extends Controller
{

    public function roomMessages(Request $request, $room_id){
        $room = Room::find($room_id);
        return $room->messages()->oldest()->select('id','user_id','body')->get();
    }


    public function store(Request $request)
    {
        $message = new Message();
        $message->id = $request->message_id;
        $message->message = $request->message;
        $message->save();

        MessageRecieved::dispatch($message);
        // event(new MessageRecieved($message, $request->room_id));

        return response($message, 201);
    }


    public function show(String $id)
    {
        $messages = Message::where('room_id', $id)
                        ->orderBy(Message::CREATED_AT)->get();

        return $messages ?? abort(404);
    }
}
