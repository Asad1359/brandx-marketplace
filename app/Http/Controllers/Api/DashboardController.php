<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function user(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    public function admin(): JsonResponse
    {
        $admin = Auth::user();

        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $userCount = User::where('role', 'user')->count();
        $activeUsers = User::where('is_active', true)->count();
        $totalBags = Bag::count();

        return response()->json([
            'success' => true,

            'admin' => $admin,

            'stats' => [
                'totalUsers' => $totalUsers,
                'adminCount' => $adminCount,
                'userCount' => $userCount,
                'activeUsers' => $activeUsers,
                'totalBags' => $totalBags,
            ],
        ]);
    }
}