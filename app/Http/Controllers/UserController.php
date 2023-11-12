<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class UserController extends Controller
{
    public function get(Request $request){
        return User::find($request->user()->id);
    }

    public function update(Request $request){
        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->birthday = $request->birthday;
        $user->save();
        return User::find($user->id);
    }

    public function updatePassword(Request $request){
        if ($request->user()->auth_type!="social" && !Hash::check($request->previous_password, $request->user()->password)) {
            return response()->json(['result' => 'Unauthorized'], 401);
        }
            $validator = Validator::make($request->only('new_password', 'confirm_new_password'), [
                'new_password' => 'required',
                'confirm_new_password' => 'required|same:new_password',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }
            if ($request->user()->auth_type!="social") {
                $auth_type = $request->user()->auth_type;
            }else{
                $auth_type = 'both';
            }
            $request->user()->update([
                    'password' => Hash::make($request->new_password),
                    'auth_type' => $auth_type
            ]);
            $user = User::find($request->user()->id);
            return response()->json(['result' => "updated", "user" => $user] , 201);
    }

    public function destroy(Request $request){
        $user = $request->user();
        $user->token()->revoke();
        $profiles = $user->profiles()->get();
        $providers = $user->providers()->get();

        //プロファイル
        forEach($profiles as $profile){
            $profile->update([
                'exist' => null
            ]);
            $profile->delete();
        };
        //プロバイダ
        forEach($providers as $provider){
            $provider->update([
                'exist' => null
            ]);
            $provider->delete();
        };
        //ユーザー
        User::find($user->id)->update([
            'exist' => null
        ]);
        $user->delete();
        return response()->json(['result' => "deleted"], 201);
    }

}
