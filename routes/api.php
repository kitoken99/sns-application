<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FriendshipController;
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
    Route::get('/profiles', [ProfileController::class, 'get']);
    Route::get('/profile/image', [ProfileController::class, 'getImage']);
    Route::get('/profile', [ProfileController::class, 'find']);
    Route::post('/profile', [ProfileController::class, 'create']);
    Route::post('/profile/{id}', [ProfileController::class, 'update']);
    Route::delete('/profile/{id}', [ProfileController::class, 'destroy']);


    //フレンド
    Route::get('/friendship', [FriendshipController::class, 'get']);
    Route::post('/friendship', [FriendshipController::class, 'create']);
    Route::patch('/friendship/feature', [FriendshipController::class, 'updateFeaturedProfile']);
    Route::post('/friendship/permit', [FriendshipController::class, 'updatePermition']);
    Route::patch('/friendship/state', [FriendshipController::class, 'state']);

    //グループ
    Route::get('/groups', [GroupController::class, 'get']);
    Route::post('/group', [GroupController::class, 'create']);
    Route::post('/group/{id}', [GroupController::class, 'update']);
    Route::get('/group/image', [GroupController::class, 'getImage']);
    Route::patch('/group/profile', [GroupController::class, 'switchProfile']);
    Route::patch('/group/state', [GroupController::class, 'state']);



    //メッセージ
    Route::get('/room/{room_id}/messages', [MessageController::class, 'get']);
    Route::post('/room/{room_id}/message', [MessageController::class, 'new']);
    Route::post('/message/read', [MessageController::class, 'read']);
});





