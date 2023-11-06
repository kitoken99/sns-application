<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;


class AuthController extends Controller
{

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => Hash::make($request->password),
          'auth_type' => "password",
        ]);
        Profile::create([
            'user_id' => $user->id,
            'account_type' => 'main',
            'name' => $user->name,
            'is_main' => true
        ]);

        $token =  $user->createToken('MyApp')->accessToken;
        return response()->json(['token' => $token,], 201);
    }


    public function login(LoginRequest $request): JsonResponse
    {
        $existingUser = User::whereEmail($request->email)->first();
        if($existingUser){
            if($existingUser->auth_type == "social"){
                return response()->json([
                    'errors' => ["email" => ["Please login with Social Account"]]
                ], 422);
            }
        }
        $request->authenticate();
            $user = auth()->user();
            $token =  $user->createToken('MyApp')->accessToken;
            return response()->json(['token' => $token,], 201);

    }
}
