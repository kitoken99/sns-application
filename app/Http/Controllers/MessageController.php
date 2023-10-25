<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Events\MessageRecieved;
use App\Models\MessageUser;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{

    public function roomMessages(Request $request, $room_id){

        $messages =  Room::find($room_id)->messages()->get();
        Log::debug("ここから");
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
