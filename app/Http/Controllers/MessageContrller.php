<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Events\MessageRecieved;

class MessageController extends Controller
{
    /**
     * メッセージの受信
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        $message = new Message();
        $message->id = $request->message_id;
        $message->message = $request->message;
        $message->save();

        broadcast(new MessageRecieved($message->message, $message->id))->toOthers();
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
