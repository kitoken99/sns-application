<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\RegisterController;
use App\Http\Controllers\RoomController;
use App\Events\MessageRecieved;
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

//ルーム
Route::get('room/index', [RoomController::class, 'index']);
Route::post('room/register', [RoomController::class, 'register']);

// websocket connection
Route::middleware('auth:api')->get('/user', function (Request $request){
    return \App\Models\User::find($request->user()->id);
});

Route::middleware('auth:api')->get('/messages', function (Request $request){
    return \App\Models\Message::oldest()->select('id','user_id','body')->get();
});
Route::middleware('auth:api')->post('/message', function(Request $request){
    $message = \App\Models\Message::create([
        'user_id' => $request->user()->id,
        'room_id' => request()->room_id,
        'body' => request()->body
    ]);
    event(new MessageRecieved($message->body));
    return $message;
});
