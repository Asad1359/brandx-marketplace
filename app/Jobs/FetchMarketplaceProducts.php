<?php

namespace App\Jobs;

use App\Models\MarketplaceSearch;
use App\Services\MarketplaceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class FetchMarketplaceProducts implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Maximum time allowed for the job.
     */
    public int $timeout = 180;

    /**
     * Run the job only once.
     */
    public int $tries = 1;

    /**
     * Marketplace search ID.
     */
    protected int $searchId;

    /**
     * Create a new job.
     */
    public function __construct(int $searchId)
    {
        $this->searchId = $searchId;
    }

    /**
     * Execute the job.
     */
    public function handle(
        MarketplaceService $marketplaceService
    ): void {
        $search = MarketplaceSearch::find($this->searchId);

        if (!$search) {
            return;
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | Mark search as processing
            |--------------------------------------------------------------------------
            */

            $search->update([
                'status' => 'processing',
                'error' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Fetch marketplace products
            |--------------------------------------------------------------------------
            */

            $products = $marketplaceService->fetch(
                $search->search
            );

            /*
            |--------------------------------------------------------------------------
            | Make sure products are a normal array
            |--------------------------------------------------------------------------
            */

            $products = is_array($products)
                ? array_values($products)
                : [];

            /*
            |--------------------------------------------------------------------------
            | No products found
            |--------------------------------------------------------------------------
            */

            if (empty($products)) {
                $search->update([
                    'status' => 'completed',
                    'products' => [],
                    'error' => 'No live products found for ' . $search->search,
                ]);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Products found
            |--------------------------------------------------------------------------
            */

            $search->update([
                'status' => 'completed',
                'products' => $products,
                'error' => null,
            ]);

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Save error
            |--------------------------------------------------------------------------
            */

            $search->update([
                'status' => 'failed',
                'products' => [],
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}