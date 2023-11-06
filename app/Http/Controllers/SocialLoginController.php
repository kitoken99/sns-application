<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class SocialLoginController extends Controller
{
    public function redirectToProvider(Request $request): JsonResponse {

        $provider = $request->provider;
            $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

            if($provider=='line'){
                $state = Str::random(32);
            $url = $url."&state=".$state."&scope=openid%20profile";
        }
        return response()->json(['redirect_url' => $url,], 200);
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
        $token =  $authUser->createToken('MyApp')-> accessToken;
        return response()->json(['token' => $token,], 201);
    }
}
