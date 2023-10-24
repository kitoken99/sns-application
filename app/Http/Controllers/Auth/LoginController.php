<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;

class LoginController extends BaseController
{
    public function redirectToProvider(Request $request) {

        $provider = $request->provider;
            $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

            if($provider=='line'){
                $state = Str::random(32);
            $url = $url."&state=".$state."&scope=openid%20profile";
        }
        $success['redirect_url'] = $url;
        return $this->sendResponse($success, 'User register successfully.', 200);
    }

    public function handleProviderCallback(Request $request) {
        $provider = $request->provider;
        try{
            $providerUser = Socialite::driver($provider)->stateless()->user();

        }catch(\Exception $e){
            BaseController::sendError("", $e->getMessage(), 500);
        }
        $authUser = User::socialFindOrCreate($providerUser, $provider);
        Auth::login($authUser, true);
        $success['token'] =  $authUser->createToken('MyApp')-> accessToken;
        return $this->sendResponse($success, 'User login successfully.', 201);
    }
}
