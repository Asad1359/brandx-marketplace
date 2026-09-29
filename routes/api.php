<?php

use App\Http\Controllers\Api\AdminChatController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BagController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WikipediaController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
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

Route::get('/marketplace', [MarketplaceController::class, 'index']);
Route::get('/marketplace/products', [MarketplaceController::class, 'products']);
Route::post('/marketplace/search', [MarketplaceController::class, 'search']);
Route::get('/marketplace/status/{id}', [MarketplaceController::class, 'status']);
Route::post('/marketplace/callback', [MarketplaceController::class, 'marketplaceCallback']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | BROADCASTING AUTH
    |--------------------------------------------------------------------------
    */

    Broadcast::routes(['middleware' => ['auth:sanctum']]);

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
    Route::post('/profile/image', [ProfileController::class, 'updateImage']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::put('/profile/theme', [ProfileController::class, 'updateTheme']);

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'user']);

    /*
    |--------------------------------------------------------------------------
    | BAGS
    |--------------------------------------------------------------------------
    */

    Route::get('/bags', [BagController::class, 'index']);
    Route::get('/bags/{id}', [BagController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | USER CHAT
    |--------------------------------------------------------------------------
    */

    Route::get('/chat/messages', [ChatController::class, 'userMessages']);
    Route::post('/chat/messages', [ChatController::class, 'userSendMessage']);
    Route::get('/chat/unread', [ChatController::class, 'userUnreadCount']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin')->group(function () {

        /*
        |----------------------------------------------------------------------
        | DASHBOARD
        |----------------------------------------------------------------------
        */

        Route::get('/admin/dashboard', [DashboardController::class, 'admin']);
        Route::get('/admin/profile', [ProfileController::class, 'adminProfile']);
        Route::put('/admin/profile/password', [ProfileController::class, 'updateAdminPassword']);
        Route::put('/admin/profile/theme', [ProfileController::class, 'updateAdminTheme']);

        /*
        |----------------------------------------------------------------------
        | USERS
        |----------------------------------------------------------------------
        */

        Route::get('/admin/users', [AdminUserController::class, 'index']);
        Route::post('/admin/users', [AdminUserController::class, 'store']);
        Route::get('/admin/users/{id}', [AdminUserController::class, 'show']);
        Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);
        Route::patch('/admin/users/{id}/status', [AdminUserController::class, 'toggleStatus']);
        Route::patch('/admin/users/{id}/deactivate', [AdminUserController::class, 'deactivate']);
        Route::patch('/admin/users/{id}/activate', [AdminUserController::class, 'activate']);
        Route::post('/admin/users/{id}/change-password', [AdminUserController::class, 'changePassword']);

        /*
        |----------------------------------------------------------------------
        | BAGS
        |----------------------------------------------------------------------
        */

        Route::post('/admin/bags', [BagController::class, 'store']);
        Route::put('/admin/bags/{id}', [BagController::class, 'update']);
        Route::delete('/admin/bags/{id}', [BagController::class, 'destroy']);

        /*
        |----------------------------------------------------------------------
        | ADMIN CHAT
        |----------------------------------------------------------------------
        */

        Route::get('/admin/chats', [ChatController::class, 'adminConversations']);
        Route::get('/admin/chats/unread', [ChatController::class, 'adminUnreadCount']);
        Route::get('/admin/chats/{userId}', [ChatController::class, 'adminMessages']);
        Route::post('/admin/chats/{userId}', [ChatController::class, 'adminSendMessage']);
    });
});