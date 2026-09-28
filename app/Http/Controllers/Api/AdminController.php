<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bag;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'success' => true,

            'stats' => [
                'totalUsers' => User::count(),
                'adminCount' => User::where('role', 'admin')->count(),
                'activeUsers' => User::where('is_active', true)->count(),
                'totalBags' => Bag::count(),
            ],
        ]);
    }

    public function users(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'users' => User::latest()->get(),
        ]);
    }
}