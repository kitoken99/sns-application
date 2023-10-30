<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile_Room;
use App\Models\Room;
use App\Models\User;
use App\Models\Profile;
use App\Models\Message;



class UserController extends Controller
{
    public function get(Request $request){
        return User::find($request->user()->id);
    }

    public function update(Request $request){
        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->birth_day = $request->birthday;
        $user->save();
        return User::find($user->id);
    }

    public function destroy(Request $request){
        $user = $request->user();
        return User::find($user->id)->delete();
    }

}
