<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\BagController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\MarketplaceController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| PUBLIC AUTH ROUTES
|--------------------------------------------------------------------------
|
| These routes use the web middleware because the application uses
| Laravel session authentication.
|
*/

Route::middleware('web')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/register',
        [AuthController::class, 'register']
    );

    Route::post(
        '/register/verify-otp',
        [AuthController::class, 'verifyRegistrationOtp']
    );

    Route::post(
        '/register/resend-otp',
        [AuthController::class, 'resendRegistrationOtp']
    );


    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/login',
        [AuthController::class, 'login']
    );


    /*
    |--------------------------------------------------------------------------
    | FORGOT PASSWORD
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/forgot-password',
        [AuthController::class, 'forgotPassword']
    );

    Route::post(
        '/forgot-password/verify-otp',
        [AuthController::class, 'verifyPasswordResetOtp']
    );

    Route::post(
        '/forgot-password/resend-otp',
        [AuthController::class, 'resendPasswordResetOtp']
    );

    Route::post(
        '/reset-password',
        [AuthController::class, 'resetPassword']
    );
});


/*
|--------------------------------------------------------------------------
| PUBLIC MARKETPLACE ROUTES
|--------------------------------------------------------------------------
|
| Marketplace search is intentionally outside the auth middleware.
|
| Flow:
|
| Vue
|   ↓
| Project 1 :8000
|   ↓
| Project 2 :8001
|   ↓
| Queue Job
|   ↓
| Callback to Project 1 :8000
|
*/

/*
|--------------------------------------------------------------------------
| MARKETPLACE STATUS
|--------------------------------------------------------------------------
*/

Route::get(
    '/marketplace',
    [MarketplaceController::class, 'index']
);


/*
|--------------------------------------------------------------------------
| START MARKETPLACE SEARCH
|--------------------------------------------------------------------------
*/

Route::post(
    '/marketplace/search',
    [MarketplaceController::class, 'search']
);


/*
|--------------------------------------------------------------------------
| MARKETPLACE SEARCH STATUS
|--------------------------------------------------------------------------
*/

Route::get(
    '/marketplace/status/{id}',
    [MarketplaceController::class, 'status']
);


/*
|--------------------------------------------------------------------------
| MARKETPLACE CALLBACK
|--------------------------------------------------------------------------
|
| Project 2 :8001 calls this endpoint after the queue job finishes.
|
*/

Route::post(
    '/marketplace/callback',
    [MarketplaceController::class, 'marketplaceCallback']
);


/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER ROUTES
|--------------------------------------------------------------------------
|
| These routes require the Laravel session.
|
*/

Route::middleware(['web', 'auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/user',
        [AuthController::class, 'user']
    );

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    );


    /*
    |--------------------------------------------------------------------------
    | USER DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard/user',
        [DashboardController::class, 'user']
    );


    /*
    |--------------------------------------------------------------------------
    | USER PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'index']
    );

    Route::put(
        '/profile',
        [ProfileController::class, 'update']
    );

    Route::post(
        '/profile/image',
        [ProfileController::class, 'updateImage']
    );

    Route::put(
        '/profile/password',
        [ProfileController::class, 'updatePassword']
    );

    Route::put(
        '/profile/theme',
        [ProfileController::class, 'updateTheme']
    );


    /*
    |--------------------------------------------------------------------------
    | USER BAGS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/bags',
        [BagController::class, 'index']
    );

    Route::get(
        '/bags/{id}',
        [BagController::class, 'show']
    );

    Route::post(
        '/bags',
        [BagController::class, 'store']
    );

    Route::post(
        '/bags/{id}',
        [BagController::class, 'update']
    );

    Route::delete(
        '/bags/{id}',
        [BagController::class, 'destroy']
    );


    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin')
        ->prefix('admin')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | ADMIN DASHBOARD
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/dashboard',
                [AdminController::class, 'dashboard']
            );


            /*
            |--------------------------------------------------------------------------
            | ADMIN PROFILE
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/profile',
                [ProfileController::class, 'adminProfile']
            );

            Route::put(
                '/profile/password',
                [ProfileController::class, 'updateAdminPassword']
            );

            Route::put(
                '/profile/theme',
                [ProfileController::class, 'updateAdminTheme']
            );


            /*
            |--------------------------------------------------------------------------
            | ADMIN USERS
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/users',
                [AdminUserController::class, 'index']
            );

            Route::get(
                '/users/{id}',
                [AdminUserController::class, 'show']
            );

            Route::post(
                '/users',
                [AdminUserController::class, 'store']
            );

            Route::put(
                '/users/{id}',
                [AdminUserController::class, 'update']
            );

            Route::delete(
                '/users/{id}',
                [AdminUserController::class, 'destroy']
            );

            Route::patch(
                '/users/{id}/status',
                [AdminUserController::class, 'toggleStatus']
            );

            Route::patch(
                '/users/{id}/activate',
                [AdminUserController::class, 'activate']
            );

            Route::patch(
                '/users/{id}/deactivate',
                [AdminUserController::class, 'deactivate']
            );

            Route::post(
                '/users/{id}/change-password',
                [AdminUserController::class, 'changePassword']
            );


            /*
            |--------------------------------------------------------------------------
            | ADMIN BAGS
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/bags',
                [BagController::class, 'index']
            );

            Route::get(
                '/bags/{id}',
                [BagController::class, 'show']
            );

            Route::post(
                '/bags',
                [BagController::class, 'store']
            );

            Route::post(
                '/bags/{id}',
                [BagController::class, 'update']
            );

            Route::delete(
                '/bags/{id}',
                [BagController::class, 'destroy']
            );
        });
});