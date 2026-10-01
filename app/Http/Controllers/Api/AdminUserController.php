<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

use App\Notifications\UserPasswordChangedNotification;


class AdminUserController extends Controller
{
    /*
    |==========================================================================
    | USERS LIST
    |==========================================================================
    | GET /api/admin/users
    |
    | Query params:
    |   ?search=john
    |   ?role=admin
    |   ?page=1
    |   ?per_page=15
    |==========================================================================
    */

    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        /*
        |----------------------------------------------------------------------
        | Search by name or email
        |----------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->query('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        /*
        |----------------------------------------------------------------------
        | Filter by role
        |----------------------------------------------------------------------
        */

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        /*
        |----------------------------------------------------------------------
        | Pagination
        |----------------------------------------------------------------------
        */

        $perPage = (int) $request->query('per_page', 15);

        $users = $query
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'users'   => $users,
        ]);
    }


    /*
    |==========================================================================
    | SHOW USER
    |==========================================================================
    | GET /api/admin/users/{id}
    |==========================================================================
    */

    public function show($id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user'    => $user,
        ]);
    }


    /*
    |==========================================================================
    | ADD USER
    |==========================================================================
    | POST /api/admin/users
    |==========================================================================
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
                Rule::in(['user', 'admin']),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'role'              => $validated['role'],
            'password'          => Hash::make($validated['password']),
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'user'    => $user,
        ], 201);
    }


    /*
    |==========================================================================
    | UPDATE USER
    |==========================================================================
    | PUT /api/admin/users/{id}
    |==========================================================================
    */

    public function update(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
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
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'role' => [
                'required',
                Rule::in(['user', 'admin']),
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
        |----------------------------------------------------------------------
        | Basic info
        |----------------------------------------------------------------------
        */

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->role  = $validated['role'];

        /*
        |----------------------------------------------------------------------
        | Optional is_active
        |----------------------------------------------------------------------
        */

        if (array_key_exists('is_active', $validated)) {
            $user->is_active = $validated['is_active'];
        }

        /*
        |----------------------------------------------------------------------
        | Optional password
        |----------------------------------------------------------------------
        */

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'user'    => $user->fresh(),
        ]);
    }


    /*
    |==========================================================================
    | DELETE USER
    |==========================================================================
    | DELETE /api/admin/users/{id}
    |==========================================================================
    */

    public function destroy(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        /*
        |----------------------------------------------------------------------
        | Prevent self-delete
        |----------------------------------------------------------------------
        */

        if ((int) $request->user()->id === (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }


    /*
    |==========================================================================
    | ACTIVATE USER
    |==========================================================================
    | PATCH /api/admin/users/{id}/activate
    |==========================================================================
    */

    public function activate($id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->is_active = true;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User activated successfully.',
            'user'    => $user,
        ]);
    }


    /*
    |==========================================================================
    | DEACTIVATE USER
    |==========================================================================
    | PATCH /api/admin/users/{id}/deactivate
    |==========================================================================
    */

    public function deactivate(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        /*
        |----------------------------------------------------------------------
        | Prevent self-deactivate
        |----------------------------------------------------------------------
        */

        if ((int) $request->user()->id === (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account.',
            ], 422);
        }

        $user->is_active = false;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User deactivated successfully.',
            'user'    => $user,
        ]);
    }


    /*
    |==========================================================================
    | TOGGLE USER STATUS
    |==========================================================================
    | PATCH /api/admin/users/{id}/status
    |==========================================================================
    */

    public function toggleStatus(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        /*
        |----------------------------------------------------------------------
        | Prevent self-toggle
        |----------------------------------------------------------------------
        */

        if ((int) $request->user()->id === (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own status.',
            ], 422);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return response()->json([
            'success'   => true,
            'message'   => $user->is_active
                ? 'User activated successfully.'
                : 'User deactivated successfully.',
            'is_active' => (bool) $user->is_active,
            'user'      => $user,
        ]);
    }


    /*
    |==========================================================================
    | GENERATE NEW PASSWORD
    |==========================================================================
    | POST /api/admin/users/{id}/change-password
    |
    | Generates a new secure password, saves it, and sends it by email.
    |==========================================================================
    */

    public function changePassword($id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        /*
        |----------------------------------------------------------------------
        | Generate secure password
        |----------------------------------------------------------------------
        */

        $newPassword = $this->generatePassword();

        $user->password = Hash::make($newPassword);
        $user->save();

        /*
        |----------------------------------------------------------------------
        | Email the new password
        |----------------------------------------------------------------------
        */

        try {

            $user->notify(
                new UserPasswordChangedNotification($newPassword)
            );

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Password was changed, but email could not be sent.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'New password generated and sent to ' . $user->email,
        ]);
    }


    /*
    |==========================================================================
    | GENERATE SECURE PASSWORD
    |==========================================================================
    | Example output: BX-A1B2C3D4-42
    |==========================================================================
    */

    private function generatePassword(): string
    {
        return 'BX-'
            . strtoupper(
                substr(
                    bin2hex(random_bytes(4)),
                    0,
                    8
                )
            )
            . '-'
            . random_int(10, 99);
    }
}