<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\API\BaseController as BaseController;

class LoginController extends BaseController
{
    public function redirectToProvider(Request $request) {
        $state = Str::random(32);
        $provider = $request->provider;
            $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

            if($provider=='line'){
            $url = $url."&state=".$state."&scope=openid%20profile";
        }
        $success['redirect_url'] = $url;
        return $this->sendResponse($success, 'User register successfully.', 200);
    }

    public function handleProviderCallback(Request $request) {

        $provider = $request->provider;
        $social_user = Socialite::driver($provider)->stateless()->user();
        $social_email = $social_user->getEmail();
        $social_name = $social_user->getName();
        $success['user']=$social_name;
        $success['email']=$social_email;
        // $success['email']=$provider;
        return $this->sendResponse($success, 'User register successfully.', 201);
        // if(!is_null($social_email)) {

        //     $user = User::firstOrCreate([
        //         'email' => $social_email
        //     ], [
        //         'email' => $social_email,
        //         'name' => $social_name,
        //         'password' => Hash::make(Str::random())
        //     ]);

        //     auth()->login($user);
        //     return redirect('/dashboard');

        // }

        // return '必要な情報が取得できていません。';

    }
}
