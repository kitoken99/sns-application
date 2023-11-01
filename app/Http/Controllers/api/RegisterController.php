<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Http\JsonResponse;


class RegisterController extends BaseController
{

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 422);
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

        $success['token'] =  $user->createToken('MyApp')->accessToken;
        return $this->sendResponse($success, 'User register successfully.', 201);
    }


    public function login(LoginRequest $request): JsonResponse
    {
        $existingUser = User::whereEmail($request->email)->first();
        if($existingUser){
            if($existingUser->auth_type == "social"){
                $errors = ["email" => ["Please login with Social Account"]];
                return BaseController::sendError('Authentication Error.', $errors, 422);
            }
        }
        $request->authenticate();
            $user = auth()->user();
            $success['token'] =  $user->createToken('MyApp')-> accessToken;
            return $this->sendResponse($success, 'User login successfully.', 201);

    }
}
