<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\GroupController;



// 通常認証
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// ソーシャル・ログイン
Route::prefix('login/{provider}')->where(['provider' => '(line|github|google|facebook|twitter)'])->group(function(){
    Route::get('/', [SocialLoginController::class ,"redirectToProvider"])->name('social_login.redirect');
    Route::get('/callback', [SocialLoginController::class, "handleProviderCallback"])->name('social_login.callback');
});

Route::group(['middleware' => "auth:api" ], function () {

    //ユーザー
    Route::get('/user', [UserController::class, 'get']);
    Route::patch('/user', [UserController::class, 'update']);
    Route::patch('/user/password', [UserController::class, 'updatePassword']);
    Route::delete('/user', [UserController::class, 'destroy']);

    //プロフィール
    Route::get('/user/profiles', [ProfileController::class, 'myProfiles']);
    Route::get('/profiles', [ProfileController::class, 'get']);
    Route::get('/profile', [ProfileController::class, 'find']);
    Route::post('/profile', [ProfileController::class, 'create']);
    Route::patch('/profile', [ProfileController::class, 'update']);


    //フレンド
    Route::get('/friendship', [FriendController::class, 'get']);
    Route::post('/friendship', [FriendController::class, 'create']);
    Route::patch('/friendship/feature', [FriendController::class, 'updateFeaturedProfile']);
    Route::post('/friendship/permit', [FriendController::class, 'updatePermition']);

    //グループ
    Route::get('/groups', [GroupController::class, 'get']);
    Route::post('/group', [GroupController::class, 'create']);

    //ルーム
    Route::get('/rooms', [RoomController::class, 'get']);

    //メッセージ
    Route::get('/room/{room_id}/messages', [MessageController::class, 'get']);
    Route::post('/room/{room_id}/message', [MessageController::class, 'new']);
    Route::post('/message/read', [MessageController::class, 'read']);
});





