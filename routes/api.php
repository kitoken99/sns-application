<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\RegisterController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Events\MessageRecieved;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group([
  'as' => 'passport.',
  'prefix' => config('passport.path', 'oauth'),
  'namespace' => '\Laravel\Passport\Http\Controllers',
], function () {

});

// 通常認証
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);
Route::middleware('auth:api')->get('/user', function (Request $request) {
  return $request->user();
});

// ソーシャル・ログイン
Route::prefix('login/{provider}')->where(['provider' => '(line|github|google|facebook|twitter)'])->group(function(){
    Route::get('/', 'App\Http\Controllers\Auth\LoginController@redirectToProvider')->name('social_login.redirect');
    Route::get('/callback', 'App\Http\Controllers\Auth\LoginController@handleProviderCallback')->name('social_login.callback');
});


//ユーザー
Route::group(['middleware' => "auth:api" ], function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::patch('/user', [UserController::class, 'updateUser']);
});

//プロフィール
Route::middleware('auth:api')->get('my-profiles', [ProfileController::class, 'myProfiles']);
Route::middleware('auth:api')->get('my-profiles', [ProfileController::class, 'myProfiles']);
Route::middleware('auth:api')->post('profile', [ProfileController::class, 'create']);
Route::middleware('auth:api')->get('profile', [ProfileController::class, 'find']);

//ルーム
Route::middleware('auth:api')->get('profile/{profile_id}/rooms', [RoomController::class, 'myRooms']);
Route::post('room/register', [RoomController::class, 'register']);

// websocket connection
Route::middleware('auth:api')->get('/messages/{room_id}', [MessageController::class, 'roomMessages']);
Route::middleware('auth:api')->get('/room/{room_id}/profiles', [ProfileController::class, 'roomProfiles']);
Route::middleware('auth:api')->post('/room/{room_id}/message', [MessageController::class, 'newMessage']);



