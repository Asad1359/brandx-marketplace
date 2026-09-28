<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | USER PROFILE
    |--------------------------------------------------------------------------
    */

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => Auth::user(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->name = $request->name;
        $user->email = $request->email;

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE IMAGE
    |--------------------------------------------------------------------------
    */

    public function updateImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid image.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Delete old image
        |--------------------------------------------------------------------------
        */

        if ($user->profile_image) {

            $oldImage = public_path(
                'uploads/profile/' . $user->profile_image
            );

            if (File::exists($oldImage)) {
                File::delete($oldImage);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Create upload directory
        |--------------------------------------------------------------------------
        */

        $destination = public_path(
            'uploads/profile'
        );

        if (!File::exists($destination)) {

            File::makeDirectory(
                $destination,
                0755,
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Upload image
        |--------------------------------------------------------------------------
        */

        $image = $request->file('image');

        $extension = $image->getClientOriginalExtension();

        $imageName =
            time() .
            '_' .
            uniqid() .
            '.' .
            $extension;

        $image->move(
            $destination,
            $imageName
        );


        /*
        |--------------------------------------------------------------------------
        | Save image name
        |--------------------------------------------------------------------------
        */

        $user->profile_image = $imageName;

        $user->save();


        return response()->json([
            'success' => true,
            'message' => 'Profile image updated successfully.',
            'user' => $user->fresh(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PASSWORD
    |--------------------------------------------------------------------------
    */

    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Check current password
        |--------------------------------------------------------------------------
        */

        if (!Hash::check(
            $request->current_password,
            $user->password
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Save new password
        |--------------------------------------------------------------------------
        */

        $user->password = Hash::make(
            $request->password
        );

        $user->save();


        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE USER THEME
    |--------------------------------------------------------------------------
    */

    public function updateTheme(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'theme' => [
                    'required',
                    'in:light,dark,system',
                ],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid theme.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Save theme
        |--------------------------------------------------------------------------
        */

        $user->theme = $request->theme;

        $user->save();


        return response()->json([
            'success' => true,
            'message' => 'Theme updated successfully.',
            'theme' => $user->theme,
            'user' => $user->fresh(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN PROFILE
    |--------------------------------------------------------------------------
    */

    public function adminProfile(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN PASSWORD
    |--------------------------------------------------------------------------
    */

    public function updateAdminPassword(
        Request $request
    ): JsonResponse {
        return $this->updatePassword($request);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN THEME
    |--------------------------------------------------------------------------
    */

    public function updateAdminTheme(
        Request $request
    ): JsonResponse {
        return $this->updateTheme($request);
    }
}