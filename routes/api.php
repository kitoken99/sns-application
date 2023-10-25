<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\RegisterController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
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
Route::middleware('auth:api')->get('/user', function (Request $request){
    return \App\Models\User::find($request->user()->id);
});

//プロフィール
Route::middleware('auth:api')->get('my-profiles', [ProfileController::class, 'myProfiles']);
Route::post('profile/register', [ProfileController::class, 'register']);

//ルーム
Route::middleware('auth:api')->get('my-rooms', [RoomController::class, 'myRooms']);
Route::post('room/register', [RoomController::class, 'register']);

// websocket connection
Route::middleware('auth:api')->get('/messages/{room_id}', [MessageController::class, 'roomMessages']);

Route::middleware('auth:api')->post('/message', function(Request $request){
    $message = \App\Models\Message::create([
        'user_id' => $request->user()->id,
        'room_id' => request()->room_id,
        'body' => request()->body
    ]);
    $members = $message->room()->first()->members()->get();
    // Log::debug($members->getAttributes());
    foreach ($members as $member){
        if($member->profile()->first()->user()->first()->id != $request->user()->id){
        \App\Models\MessageUser::create([
            'user_id' => $member->profile()->first()->user_id,
            'message_id' => $message->id,
        ]);
    }
    }
    broadcast(new MessageRecieved($message));
    return $message;
});
