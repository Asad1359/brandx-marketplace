<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Notifications\UserPasswordChangedNotification;

class AdminUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | USERS LIST
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');

            });
        }

        /*
        |--------------------------------------------------------------------------
        | ROLE FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->filled('role')) {

            $query->where(
                'role',
                $request->role
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $users = $query
            ->latest()
            ->paginate(
                (int) $request->get('per_page', 15)
            );

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW USER
    |--------------------------------------------------------------------------
    */

    public function show($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ADD USER
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'role' => [
                'required',
                Rule::in([
                    'user',
                    'admin',
                ]),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

        ]);

        $user = User::create([

            'name' => $validated['name'],

            'email' => $validated['email'],

            'password' => Hash::make(
                $validated['password']
            ),

            'role' => $validated['role'],

            'is_active' => true,

            'email_verified_at' => now(),

        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE USER
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ): JsonResponse {

        $user = User::find($id);

        if (!$user) {

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(
                    'users',
                    'email'
                )->ignore($user->id),
            ],

            'role' => [
                'required',
                Rule::in([
                    'user',
                    'admin',
                ]),
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE BASIC INFORMATION
        |--------------------------------------------------------------------------
        */

        $user->name =
            $validated['name'];

        $user->email =
            $validated['email'];

        $user->role =
            $validated['role'];


        /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists(
                'is_active',
                $validated
            )
        ) {

            $user->is_active =
                $validated['is_active'];
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE PASSWORD
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $validated['password']
            )
        ) {

            $user->password =
                Hash::make(
                    $validated['password']
                );
        }


        $user->save();


        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */

    public function destroy($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | PROTECT CURRENT ADMIN
        |--------------------------------------------------------------------------
        */

        if (
            (int) $user->id ===
            (int) auth()->id()
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'You cannot delete your own account.',
            ], 422);
        }


        $user->delete();


        return response()->json([
            'success' => true,
            'message' =>
                'User deleted successfully.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DEACTIVATE USER
    |--------------------------------------------------------------------------
    */

    public function deactivate($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | PROTECT CURRENT ADMIN
        |--------------------------------------------------------------------------
        */

        if (
            (int) $user->id ===
            (int) auth()->id()
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'You cannot deactivate your own account.',
            ], 422);
        }


        $user->is_active = false;

        $user->save();


        return response()->json([
            'success' => true,
            'message' =>
                'User deactivated successfully.',
            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVATE USER
    |--------------------------------------------------------------------------
    */

    public function activate($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }


        $user->is_active = true;

        $user->save();


        return response()->json([
            'success' => true,
            'message' =>
                'User activated successfully.',
            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE USER STATUS
    |--------------------------------------------------------------------------
    */

    public function toggleStatus($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | PROTECT CURRENT ADMIN
        |--------------------------------------------------------------------------
        */

        if (
            (int) $user->id ===
            (int) auth()->id()
        ) {

            return response()->json([
                'success' => false,
                'message' =>
                    'You cannot change your own status.',
            ], 422);
        }


        $user->is_active =
            !$user->is_active;

        $user->save();


        return response()->json([
            'success' => true,

            'message' => $user->is_active
                ? 'User activated successfully.'
                : 'User deactivated successfully.',

            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE NEW PASSWORD
    |--------------------------------------------------------------------------
    */

    public function changePassword($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | GENERATE TEMPORARY PASSWORD
        |--------------------------------------------------------------------------
        */

        $newPassword =
            $this->generatePassword();


        /*
        |--------------------------------------------------------------------------
        | SAVE PASSWORD
        |--------------------------------------------------------------------------
        */

        $user->password =
            Hash::make(
                $newPassword
            );

        $user->save();


        /*
        |--------------------------------------------------------------------------
        | SEND EMAIL
        |--------------------------------------------------------------------------
        */

        try {

            $user->notify(
                new UserPasswordChangedNotification(
                    $newPassword
                )
            );

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Password was changed, but email could not be sent.',
            ], 500);
        }


        return response()->json([
            'success' => true,
            'message' =>
                'New password generated and sent to '
                . $user->email,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE SECURE PASSWORD
    |--------------------------------------------------------------------------
    */

    private function generatePassword(): string
    {
        return 'BX-'
            . strtoupper(
                substr(
                    bin2hex(
                        random_bytes(4)
                    ),
                    0,
                    8
                )
            )
            . random_int(10, 99);
    }
}