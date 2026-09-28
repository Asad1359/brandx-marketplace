<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WikipediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WikipediaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Wikipedia API is working.',
        ]);
    }

    public function search(
        Request $request,
        WikipediaService $wikipedia
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a search query.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $results = $wikipedia->search(
                trim($request->query),
                1
            );

            return response()->json([
                'success' => true,
                'query' => $request->query,
                'results' => $results,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'results' => [],
            ], 500);
        }
    }
}