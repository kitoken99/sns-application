<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\RegisterController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\GroupController;
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


Route::group(['middleware' => "auth:api" ], function () {
    //ユーザー
    Route::get('/user', [UserController::class, 'get']);
    Route::patch('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'destroy']);


    //プロフィール
    Route::get('/profiles', [ProfileController::class, 'myProfiles']);
    Route::get('my-profiles', [ProfileController::class, 'myProfiles']);
    Route::post('profile', [ProfileController::class, 'create']);
    Route::get('/profile', [ProfileController::class, 'find']);

    //フレンズ
    Route::get('/friends', [FriendController::class, 'myFriends']);
    Route::post('/friend', [FriendController::class, 'addFriend']);

    //グループ
    Route::get('/groups', [GroupController::class, 'myGroups']);
    Route::post('/group', [GroupController::class, 'addGroup']);



    //ルーム
    Route::get('/room/{room_id}', [MessageController::class, 'roomMessages']);
    Route::post('/room/{room_id}/message', [MessageController::class, 'newMessage']);

    Route::post('room/register', [RoomController::class, 'register']);
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

Route::middleware('auth:api')->get('/room/{room_id}/profiles', [ProfileController::class, 'roomProfiles']);




