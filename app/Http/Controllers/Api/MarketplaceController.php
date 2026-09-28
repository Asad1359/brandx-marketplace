<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketplaceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MARKETPLACE API
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/marketplace
     */
    public function index(): JsonResponse
    {
        $searches = MarketplaceSearch::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Marketplace is working.',
            'data' => $searches,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GET MARKETPLACE PRODUCTS
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/marketplace/products
     */
    public function products(): JsonResponse
    {
        $searches = MarketplaceSearch::where('status', 'completed')
            ->latest()
            ->get();

        $products = [];

        foreach ($searches as $search) {
            $searchProducts = is_array($search->products)
                ? $search->products
                : [];

            foreach ($searchProducts as $product) {
                if (!is_array($product)) {
                    continue;
                }

                $product['search_id'] = $search->id;
                $product['search'] = $search->search;

                $products[] = $product;
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($products) > 0
                ? 'Marketplace products found.'
                : 'No marketplace products found.',
            'products' => $products,
            'count' => count($products),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | START MARKETPLACE SEARCH
    |--------------------------------------------------------------------------
    */

    /**
     * POST /api/marketplace/search
     *
     * Project 1:
     * - Creates the search record.
     * - Sends search ID + search text to Project 2.
     *
     * Project 2:
     * - Runs the queue job.
     * - Sends processing/completed/failed callback back here.
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim(
            (string) $request->input('query', '')
        );

        if ($query === '') {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a product name.',
            ], 422);
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | Create Search In Project 1
            |--------------------------------------------------------------------------
            */

            $marketplaceSearch = MarketplaceSearch::create([
                'search' => $query,
                'status' => 'pending',
                'products' => [],
                'error' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Project 2 URL
            |--------------------------------------------------------------------------
            */

            $project2Url = rtrim(
                (string) config('services.marketplace.project2_url'),
                '/'
            );

            if ($project2Url === '') {
                $marketplaceSearch->update([
                    'status' => 'failed',
                    'error' => 'Project 2 URL is not configured.',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Project 2 URL is not configured.',
                    'search_id' => $marketplaceSearch->id,
                ], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | Send Search To Project 2
            |--------------------------------------------------------------------------
            */

            $response = Http::timeout(15)
                ->acceptJson()
                ->asJson()
                ->post(
                    $project2Url . '/api/marketplace/start-job',
                    [
                        'search_id' => $marketplaceSearch->id,
                        'search' => $query,
                    ]
                );

            /*
            |--------------------------------------------------------------------------
            | Project 2 Request Failed
            |--------------------------------------------------------------------------
            */

            if ($response->failed()) {
                $error = 'Project 2 could not start the marketplace job.';

                if ($response->body() !== '') {
                    $error .= ' Response: ' . $response->body();
                }

                $marketplaceSearch->update([
                    'status' => 'failed',
                    'error' => $error,
                ]);

                Log::error(
                    'Project 2 marketplace job request failed',
                    [
                        'search_id' => $marketplaceSearch->id,
                        'search' => $query,
                        'http_status' => $response->status(),
                        'response' => $response->body(),
                    ]
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to start marketplace job.',
                    'search_id' => $marketplaceSearch->id,
                    'status' => 'failed',
                    'error' => $error,
                ], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | Log Successful Request
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Project 2 marketplace job started',
                [
                    'search_id' => $marketplaceSearch->id,
                    'search' => $query,
                    'project2_url' => $project2Url,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Return Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,
                'message' => 'Marketplace search started.',
                'search_id' => $marketplaceSearch->id,
                'search' => $query,
                'status' => 'pending',
            ], 202);

        } catch (\Throwable $e) {

            Log::error(
                'Unable to start marketplace search',
                [
                    'search' => $query,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Unable to start marketplace search.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH STATUS
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/marketplace/status/{id}
     */
    public function status(int $id): JsonResponse
    {
        $search = MarketplaceSearch::find($id);

        if (!$search) {
            return response()->json([
                'success' => false,
                'message' => 'Search not found.',
            ], 404);
        }

        if (
            in_array(
                $search->status,
                ['pending', 'processing'],
                true
            )
        ) {
            return response()->json([
                'success' => true,
                'status' => $search->status,
                'search_id' => $search->id,
                'search' => $search->search,
                'products' => [],
                'count' => 0,
            ]);
        }

        if ($search->status === 'failed') {
            return response()->json([
                'success' => false,
                'status' => 'failed',
                'search_id' => $search->id,
                'search' => $search->search,
                'message' => $search->error
                    ?: 'Marketplace search failed.',
                'products' => [],
                'count' => 0,
            ], 500);
        }

        $products = is_array($search->products)
            ? $search->products
            : [];

        return response()->json([
            'success' => true,
            'status' => 'completed',
            'search_id' => $search->id,
            'search' => $search->search,
            'products' => array_values($products),
            'count' => count($products),
            'message' => count($products) > 0
                ? 'Products found successfully.'
                : ($search->error ?: 'No products found.'),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | CALLBACK FROM PROJECT 2
    |--------------------------------------------------------------------------
    */

    /**
     * POST /api/marketplace/callback
     */
    public function marketplaceCallback(Request $request): JsonResponse
    {
        Log::info(
            'Marketplace callback received.',
            [
                'payload' => $request->all(),
            ]
        );

        $searchId = $request->input('search_id');

        if (
            $searchId === null ||
            !is_numeric($searchId) ||
            (int) $searchId <= 0
        ) {
            return response()->json([
                'success' => false,
                'message' => 'search_id is required.',
            ], 422);
        }

        $searchId = (int) $searchId;

        $search = MarketplaceSearch::find($searchId);

        if (!$search) {
            return response()->json([
                'success' => false,
                'message' => 'Search not found.',
                'search_id' => $searchId,
            ], 404);
        }

        $status = $request->input('status');

        /*
        |--------------------------------------------------------------------------
        | PROCESSING
        |--------------------------------------------------------------------------
        */

        if ($status === 'processing') {
            $search->update([
                'status' => 'processing',
                'error' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Search marked as processing.',
                'search_id' => $search->id,
                'status' => 'processing',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | COMPLETED
        |--------------------------------------------------------------------------
        */

        if ($status === 'completed') {
            $products = $request->input('products', []);

            if (!is_array($products)) {
                $products = [];
            }

            $search->update([
                'status' => 'completed',
                'products' => array_values($products),
                'error' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Marketplace results saved successfully.',
                'search_id' => $search->id,
                'status' => 'completed',
                'count' => count($products),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | FAILED
        |--------------------------------------------------------------------------
        */

        if ($status === 'failed') {
            $error = $request->input('error')
                ?: $request->input('message')
                ?: 'Marketplace search failed.';

            $search->update([
                'status' => 'failed',
                'error' => $error,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Marketplace failure saved.',
                'search_id' => $search->id,
                'status' => 'failed',
                'error' => $error,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | INVALID STATUS
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => false,
            'message' => 'Invalid callback status.',
            'status' => $status,
        ], 422);
    }
}