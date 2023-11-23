<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use App\Models\ProfileGroup;
use App\Events\Profile\ProfileDeleted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ProfileController extends Controller
{
    public function get(Request $request){
        $response = [];
        $default_profile = new Profile;
        $default_profile->getDefaultProfile();
        $default_profile->toBase();
        //my profiles
        $profiles = $request->user()->profiles()->get();
        foreach($profiles as $profile){
            $profile->setProfile();
            $response[$profile->user_id][$profile->id] = $profile;
        }
        //friendships profiles
        $friendships = $request->user()->friendships()->get();
        foreach ($friendships as $friendship){
            foreach($friendship->permittedProfiles() as $profile){
                if($profile){
                    $profile->setProfile();
                    $response[$profile->user_id][$profile->id] = $profile;
                }else{
                    if(!User::find($friendship->friend_user_id)){
                        $response[$friendship->friend_user_id][$friendship->profile_id] = $default_profile;
                        $response[$friendship->friend_user_id][$friendship->profile_id]->user_id = $friendship->friend_user_id;
                        $response[$friendship->friend_user_id][$friendship->profile_id]->id = $friendship->profile_id;
                    }
                }
            }
        }
        //profiles in group
        $groups = $request->user()->groups()->get();
        foreach ($groups as $group){
            $group_profiles= ProfileGroup::whereGroupId($group->id)->get();
            foreach ($group_profiles as $group_profile){
                $profile = Profile::find($group_profile->profile_id);
                if($profile){
                    $profile->toBase();
                    $response[$profile->user_id][$profile->id] = $profile;
                }else{
                    if(!User::find($friendship->friend_user_id)){
                        $response[$group_profile->user_id][$group_profile->profile_id] = $default_profile;
                        $response[$group_profile->user_id][$group_profile->profile_id]["user_id"] = $group_profile->user_id;
                        $response[$group_profile->user_id][$group_profile->profile_id]["id"] = $group_profile->profile_id;
                    }
                }
            }

        }
        return $response;
    }

    public function getImage(Request $request){
        $filePath = "public/profiles/" . $request->image;
        if (Storage::exists($filePath)) {
            $image = base64_encode(Storage::get($filePath));
        }else{
            $image = base64_encode(Storage::get("public/profiles/user_default.image.png"));
        }
        return $image;
    }

    public function find(Request $request){
        $email = $request->input('email');
        $user = User::whereEmail($email)->first();

        $profile = $user->profiles()->whereIsMain(true)->first();
        $profile->setProfile();
        return $profile;
    }

    public function create(Request $request){
        $profile= new Profile();
        $profile->user_id = $request->user()->id;
        $profile->name = $request->name;
        $profile->account_type = $request->account_type;
        $profile->caption = $request->caption;
        $profile->saveImage($request->file('image'));
        $profile->save();
        $profile = Profile::find($profile->id);
        $profile->setProfile();
        return $profile;
    }

    public function update(Request $request, $id){
        $profile = Profile::find($id);
        $profile->name = $request->name;
        $profile->account_type = $request->account_type;
        $profile->caption = $request->caption;
        $profile->show_birthday = $request->show_birthday=="true";
        $profile->saveImage($request->file('image'));
        $profile->save();


        $profile->setProfile();
        return $profile;
    }

    public function destroy(Request $request,$id){
        $main_profile = $request->user()->profiles()->whereIsMain(true)->first();
        $profile = Profile::find($id);
        if($profile->is_main) return response()->json(['result' => "failed"], 400);
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
        Log::debug($permittions);
        foreach($permittions as $permittion){
            $permittion->delete();
        }
        //フレンド情報
        $friendships = $profile->friendships()->get();
        Log::debug($friendships);
        foreach($friendships as $friendship){
            $friendship->update([
                'profile_id' => $main_profile->id
            ]);
        }
        $profile->exist = null;
        $profile->save();

        $profile->delete();
        return response()->json(['result' => "deleted"], 201);
    }
}
