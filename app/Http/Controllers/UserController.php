<?php

namespace App\Http\Controllers;

use App\Events\Profile\ProfileDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Log;


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
        //既存パスワードと比較
        if ($request->user()->auth_type!="social" && !Hash::check($request->previous_password, $request->user()->password)) {
            return response()->json(['result' => 'Unauthorized'], 401);
        }
        //バリデーションチェック
        $validator = Validator::make($request->only('new_password', 'confirm_new_password'), [
            'new_password' => 'required',
            'confirm_new_password' => 'required|same:new_password',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
        //ユーザーアップデート
        if ($request->user()->auth_type!="social") {
            $auth_type = $request->user()->auth_type;
        }else{
            $auth_type = 'both';
        }
        $request->user()->update([
                'password' => Hash::make($request->new_password),
                'auth_type' => $auth_type
        ]);
        return response()->json(['result' => "updated"] , 201);
    }

    public function destroy(Request $request){
        $user = $request->user();
        $profiles = $user->profiles()->get()->reverse();
        $providers = $user->providers()->get();
        $main_profile = $user->profiles()->whereIsMain(true)->first();
        //プロファイル
        forEach($profiles as $profile){
            event(new ProfileDeleted($profile));
            //グループ情報
            $profile_groups = $profile->profileGroups()->get();
            foreach($profile_groups as $profile_group){
                $profile_group->update([
                  'profile_id' => $main_profile->id
                ]);
            }
            //パーミッション情報
            $permittions = $profile->permittion()->get();
            foreach($permittions as $permittion){
                Log::debug($permittion);
                $permittion->delete();
            }
            //フレンド情報
            $friendships = $profile->friendships()->get();
            foreach($friendships as $friendship){
                $friendship->update([
                    'profile_id' => $main_profile->id
                ]);
            }
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
        $user->token()->revoke();
        $user->delete();
        return response()->json(['result' => "deleted"], 201);
    }

}
