<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AdminChatController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BagController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\ProfileController;


/*
|--------------------------------------------------------------------------
| PUBLIC AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);

Route::post('/register/verify-otp', [AuthController::class, 'verifyRegistrationOtp']);

Route::post('/register/resend-otp', [AuthController::class, 'resendRegistrationOtp']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyPasswordResetOtp']);

Route::post('/forgot-password/resend-otp', [AuthController::class, 'resendPasswordResetOtp']);

Route::post('/reset-password', [AuthController::class, 'resetPassword']);


/*
|--------------------------------------------------------------------------
| MARKETPLACE CALLBACK (called by Project 2)
|--------------------------------------------------------------------------
|
| Yeh route public hai kyunke Project 2 yahan callback bhejta hai.
| Agar chahein to isay signature / token se protect kar sakte hain.
|
*/

Route::post('/marketplace/callback', [MarketplaceController::class, 'marketplaceCallback']);


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH
    |--------------------------------------------------------------------------
    */

    Route::get('/user', [AuthController::class, 'user']);

    Route::post('/logout', [AuthController::class, 'logout']);


    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'index']);

    Route::put('/profile', [ProfileController::class, 'update']);

    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);

    Route::put('/profile/theme', [ProfileController::class, 'updateTheme']);

    Route::post('/profile/image', [ProfileController::class, 'updateImage']);


    /*
    |--------------------------------------------------------------------------
    | USER DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'user']);


    /*
    |--------------------------------------------------------------------------
    | BAGS (user side)
    |--------------------------------------------------------------------------
    */

    Route::get('/bags', [BagController::class, 'index']);

    Route::get('/bags/{id}', [BagController::class, 'show']);


    /*
    |--------------------------------------------------------------------------
    | MARKETPLACE (user side)
    |--------------------------------------------------------------------------
    */

    Route::get('/marketplace', [MarketplaceController::class, 'index']);

    Route::get('/marketplace/products', [MarketplaceController::class, 'products']);

    Route::post('/marketplace/search', [MarketplaceController::class, 'search']);

    Route::get('/marketplace/status/{id}', [MarketplaceController::class, 'status']);


    /*
    |--------------------------------------------------------------------------
    | USER CHAT
    |--------------------------------------------------------------------------
    |
    | ⚠️ Yeh methods ChatController mein exactly inhi names se hain:
    |    userMessages, userSendMessage, userUnreadCount
    |
    */

    Route::get('/chat', [ChatController::class, 'userMessages']);

    Route::post('/chat/message', [ChatController::class, 'userSendMessage']);

    Route::get('/chat/unread', [ChatController::class, 'userUnreadCount']);


    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin')->prefix('admin')->group(function () {

        /*
        |------------------------------------------------------------------
        | DASHBOARD
        |------------------------------------------------------------------
        */

        Route::get('/dashboard', [AdminController::class, 'dashboard']);


        /*
        |------------------------------------------------------------------
        | ADMIN PROFILE
        |------------------------------------------------------------------
        */

        Route::get('/profile', [ProfileController::class, 'adminProfile']);

        Route::put('/profile', [ProfileController::class, 'update']);

        Route::put('/profile/password', [ProfileController::class, 'updateAdminPassword']);

        Route::put('/profile/theme', [ProfileController::class, 'updateAdminTheme']);


        /*
        |------------------------------------------------------------------
        | ADMIN USERS
        |------------------------------------------------------------------
        */

        Route::get('/users', [AdminUserController::class, 'index']);

        Route::post('/users', [AdminUserController::class, 'store']);

        Route::get('/users/{id}', [AdminUserController::class, 'show']);

        Route::put('/users/{id}', [AdminUserController::class, 'update']);

        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);

        Route::patch('/users/{id}/status', [AdminUserController::class, 'toggleStatus']);

        Route::patch('/users/{id}/activate', [AdminUserController::class, 'activate']);

        Route::patch('/users/{id}/deactivate', [AdminUserController::class, 'deactivate']);

        Route::post('/users/{id}/change-password', [AdminUserController::class, 'changePassword']);


        /*
        |------------------------------------------------------------------
        | ADMIN BAGS
        |------------------------------------------------------------------
        */

        Route::get('/bags', [BagController::class, 'index']);

        Route::post('/bags', [BagController::class, 'store']);

        Route::get('/bags/{id}', [BagController::class, 'show']);

        Route::put('/bags/{id}', [BagController::class, 'update']);

        Route::delete('/bags/{id}', [BagController::class, 'destroy']);


        /*
        |------------------------------------------------------------------
        | ADMIN CHAT
        |------------------------------------------------------------------
        |
        | ⚠️ Yeh methods AdminChatController mein exactly inhi names se hain:
        |    getAllChats, getConversation, sendMessage
        |
        */

        Route::get('/chats', [AdminChatController::class, 'getAllChats']);

        Route::get('/chats/{userId}', [AdminChatController::class, 'getConversation']);

        Route::post('/chats/message', [AdminChatController::class, 'sendMessage']);
    });
});