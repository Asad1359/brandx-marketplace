<?php

namespace App\Services;

use DOMDocument;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MarketplaceService
{
    /**
     * Fetch products from all supported marketplaces.
     */
    public function fetch(string $category): array
    {
        $category = trim($category);

        if ($category === '') {
            return [];
        }

        $allProducts = [];

        /*
        |--------------------------------------------------------------------------
        | Amazon
        |--------------------------------------------------------------------------
        */
        try {
            $amazonProducts = $this->fetchAmazon($category);

            if (!empty($amazonProducts)) {
                $allProducts = array_merge(
                    $allProducts,
                    $amazonProducts
                );
            }
        } catch (Throwable $e) {
            Log::warning('Amazon marketplace fetch failed', [
                'search' => $category,
                'error' => $e->getMessage(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Daraz
        |--------------------------------------------------------------------------
        */
        try {
            $darazProducts = $this->fetchDaraz($category);

            if (!empty($darazProducts)) {
                $allProducts = array_merge(
                    $allProducts,
                    $darazProducts
                );
            }
        } catch (Throwable $e) {
            Log::warning('Daraz marketplace fetch failed', [
                'search' => $category,
                'error' => $e->getMessage(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Alibaba
        |--------------------------------------------------------------------------
        */
        try {
            $alibabaProducts = $this->fetchAlibaba($category);

            if (!empty($alibabaProducts)) {
                $allProducts = array_merge(
                    $allProducts,
                    $alibabaProducts
                );
            }
        } catch (Throwable $e) {
            Log::warning('Alibaba marketplace fetch failed', [
                'search' => $category,
                'error' => $e->getMessage(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | AliExpress
        |--------------------------------------------------------------------------
        */
        try {
            $aliExpressProducts = $this->fetchAliExpress($category);

            if (!empty($aliExpressProducts)) {
                $allProducts = array_merge(
                    $allProducts,
                    $aliExpressProducts
                );
            }
        } catch (Throwable $e) {
            Log::warning('AliExpress marketplace fetch failed', [
                'search' => $category,
                'error' => $e->getMessage(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Temu
        |--------------------------------------------------------------------------
        */
        try {
            $temuProducts = $this->fetchTemu($category);

            if (!empty($temuProducts)) {
                $allProducts = array_merge(
                    $allProducts,
                    $temuProducts
                );
            }
        } catch (Throwable $e) {
            Log::warning('Temu marketplace fetch failed', [
                'search' => $category,
                'error' => $e->getMessage(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Final normalize + deduplicate
        |--------------------------------------------------------------------------
        */
        $allProducts = $this->dedupeProducts($allProducts);

        shuffle($allProducts);

        return array_slice($allProducts, 0, 100);
    }


    /*
    |--------------------------------------------------------------------------
    | HTTP Headers
    |--------------------------------------------------------------------------
    */

    private function headers(): array
    {
        return [
            'User-Agent' =>
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
                'AppleWebKit/537.36 (KHTML, like Gecko) ' .
                'Chrome/139.0.0.0 Safari/537.36',

            'Accept' =>
                'text/html,application/xhtml+xml,application/xml;q=0.9,' .
                'image/avif,image/webp,image/apng,*/*;q=0.8',

            'Accept-Language' =>
                'en-US,en;q=0.9',

            'Cache-Control' =>
                'no-cache',

            'Pragma' =>
                'no-cache',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Amazon
    |--------------------------------------------------------------------------
    */

    private function fetchAmazon(string $query): array
    {
        $url = 'https://www.amazon.com/s';

        $response = Http::withHeaders($this->headers())
            ->timeout(30)
            ->retry(2, 1000)
            ->get($url, [
                'k' => $query,
            ]);

        Log::info('Amazon response', [
            'status' => $response->status(),
            'length' => strlen($response->body()),
        ]);

        if (!$response->successful()) {
            return [];
        }

        return $this->parseAmazon(
            $response->body()
        );
    }


    private function parseAmazon(string $html): array
    {
        $products = [];

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();

        @$dom->loadHTML(
            mb_convert_encoding(
                $html,
                'HTML-ENTITIES',
                'UTF-8'
            )
        );

        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $items = $xpath->query(
            '//div[contains(@data-component-type,"s-search-result")]'
        );

        if ($items === false) {
            return [];
        }

        foreach ($items as $item) {

            $title = $this->xpathText(
                $xpath,
                './/h2//span',
                $item
            );

            if ($title === '') {
                $title = $this->xpathText(
                    $xpath,
                    './/h2',
                    $item
                );
            }

            if ($title === '') {
                continue;
            }

            $image = $this->xpathAttribute(
                $xpath,
                './/img',
                'src',
                $item
            );

            if ($image === '') {
                $image = $this->xpathAttribute(
                    $xpath,
                    './/img',
                    'data-src',
                    $item
                );
            }

            $price = $this->xpathText(
                $xpath,
                './/span[contains(@class,"a-price")]' .
                '//span[contains(@class,"a-offscreen")]',
                $item
            );

            if ($price === '') {
                $price = $this->xpathText(
                    $xpath,
                    './/span[contains(@class,"a-price-whole")]',
                    $item
                );
            }

            $rating = $this->xpathText(
                $xpath,
                './/span[contains(@class,"a-icon-alt")]',
                $item
            );

            $reviews = $this->xpathText(
                $xpath,
                './/span[contains(@class,"a-size-base") and ' .
                'contains(@class,"s-underline-text")]',
                $item
            );

            $url = $this->xpathAttribute(
                $xpath,
                './/h2//a',
                'href',
                $item
            );

            if (
                $url !== '' &&
                !str_starts_with($url, 'http')
            ) {
                $url = 'https://www.amazon.com' . $url;
            }

            $priceValue = $this->parsePrice($price);

            $products[] = [
                'title' => $this->cleanText($title),

                'name' => $this->cleanText($title),

                'image' => $this->normalizeImage($image),

                'price' => $priceValue !== null
                    ? $this->formatPrice(
                        $priceValue,
                        'Amazon'
                    )
                    : (
                        $price !== ''
                            ? $price
                            : 'Price unavailable'
                    ),

                'price_value' => $priceValue,

                'rating' => $this->parseRating($rating),

                'reviews' => $this->parseReviews($reviews),

                'seller' => 'Amazon',

                'url' => $this->normalizeUrl($url),
            ];

            if (count($products) >= 30) {
                break;
            }
        }

        return $this->dedupeProducts($products);
    }


    /*
    |--------------------------------------------------------------------------
    | Daraz
    |--------------------------------------------------------------------------
    */

    private function fetchDaraz(string $query): array
    {
        $url = 'https://www.daraz.pk/catalog/';

        $response = Http::withHeaders($this->headers())
            ->timeout(30)
            ->retry(2, 1000)
            ->get($url, [
                'q' => $query,
                'page' => 1,
            ]);

        Log::info('Daraz response', [
            'status' => $response->status(),
            'length' => strlen($response->body()),
        ]);

        if (!$response->successful()) {
            return [];
        }

        return $this->parseDaraz(
            $response->body()
        );
    }


    private function parseDaraz(string $html): array
    {
        $products = [];

        $products = array_merge(
            $products,
            $this->parseJsonLd(
                $html,
                'Daraz'
            )
        );

        $products = array_merge(
            $products,
            $this->parseEmbeddedProducts(
                $html,
                'Daraz'
            )
        );

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();

        @$dom->loadHTML(
            mb_convert_encoding(
                $html,
                'HTML-ENTITIES',
                'UTF-8'
            )
        );

        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $links = $xpath->query(
            '//a[contains(@href,"/products/")]'
        );

        if ($links !== false) {

            foreach ($links as $link) {

                $title = $this->cleanText(
                    $link->textContent ?? ''
                );

                if (
                    $title === '' ||
                    strlen($title) < 3
                ) {
                    continue;
                }

                $url = $link->getAttribute('href');

                if (
                    $url !== '' &&
                    !str_starts_with($url, 'http')
                ) {
                    $url =
                        'https://www.daraz.pk' .
                        $url;
                }

                $image = '';

                $img = $xpath->query(
                    './/img',
                    $link
                );

                if (
                    $img !== false &&
                    $img->length > 0
                ) {
                    $image =
                        $img->item(0)->getAttribute('src')
                        ?:
                        $img->item(0)->getAttribute('data-src');
                }

                $products[] = [
                    'title' => $title,

                    'name' => $title,

                    'image' =>
                        $this->normalizeImage($image),

                    'price' =>
                        'Price unavailable',

                    'price_value' =>
                        null,

                    'rating' =>
                        null,

                    'reviews' =>
                        null,

                    'seller' =>
                        'Daraz',

                    'url' =>
                        $this->normalizeUrl($url),
                ];

                if (count($products) >= 30) {
                    break;
                }
            }
        }

        return $this->dedupeProducts($products);
    }


    /*
    |--------------------------------------------------------------------------
    | Alibaba
    |--------------------------------------------------------------------------
    */

    private function fetchAlibaba(string $query): array
    {
        $url =
            'https://www.alibaba.com/trade/search';

        $response = Http::withHeaders($this->headers())
            ->timeout(30)
            ->retry(2, 1000)
            ->get($url, [
                'SearchText' => $query,
            ]);

        Log::info('Alibaba response', [
            'status' => $response->status(),
            'length' => strlen($response->body()),
        ]);

        if (!$response->successful()) {
            return [];
        }

        $html = $response->body();

        $products = [];

        $products = array_merge(
            $products,
            $this->parseJsonLd(
                $html,
                'Alibaba'
            )
        );

        $products = array_merge(
            $products,
            $this->parseEmbeddedProducts(
                $html,
                'Alibaba'
            )
        );

        return $this->dedupeProducts($products);
    }


    /*
    |--------------------------------------------------------------------------
    | AliExpress
    |--------------------------------------------------------------------------
    */

    private function fetchAliExpress(string $query): array
    {
        $url =
            'https://www.aliexpress.com/wholesale';

        $response = Http::withHeaders($this->headers())
            ->timeout(30)
            ->retry(2, 1000)
            ->get($url, [
                'SearchText' => $query,
            ]);

        Log::info('AliExpress response', [
            'status' => $response->status(),
            'length' => strlen($response->body()),
        ]);

        if (!$response->successful()) {
            return [];
        }

        $html = $response->body();

        $products = [];

        /*
        |--------------------------------------------------------------------------
        | AliExpress dedicated parser
        |--------------------------------------------------------------------------
        */
        $products = array_merge(
            $products,
            $this->parseAliExpress(
                $html
            )
        );

        /*
        |--------------------------------------------------------------------------
        | JSON-LD fallback
        |--------------------------------------------------------------------------
        */
        $products = array_merge(
            $products,
            $this->parseJsonLd(
                $html,
                'AliExpress'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Generic embedded JSON fallback
        |--------------------------------------------------------------------------
        */
        $products = array_merge(
            $products,
            $this->parseEmbeddedProducts(
                $html,
                'AliExpress'
            )
        );

        $products =
            $this->dedupeProducts($products);

        Log::info('AliExpress products parsed', [
            'search' => $query,
            'count' => count($products),
        ]);

        return array_slice(
            $products,
            0,
            30
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AliExpress Dedicated Parser
    |--------------------------------------------------------------------------
    */

    private function parseAliExpress(string $html): array
    {
        $products = [];

        /*
        |--------------------------------------------------------------------------
        | Decode common escaped JSON
        |--------------------------------------------------------------------------
        */

        $decodedHtml = html_entity_decode(
            $html,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $decodedHtml = str_replace(
            [
                '\\/',
                '\\u002F',
                '\\u002f',
                '\\u003A',
                '\\u003a',
            ],
            [
                '/',
                '/',
                '/',
                ':',
                ':',
            ],
            $decodedHtml
        );

        /*
        |--------------------------------------------------------------------------
        | JSON-LD first
        |--------------------------------------------------------------------------
        */

        $jsonProducts =
            $this->parseJsonLd(
                $decodedHtml,
                'AliExpress'
            );

        if (!empty($jsonProducts)) {
            $products = array_merge(
                $products,
                $jsonProducts
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Extract likely product objects
        |--------------------------------------------------------------------------
        */

        $patterns = [

            /*
            | title + price
            */
            '/["\'](?:name|title|productName|productTitle)["\']\s*:\s*["\'](.{3,500}?)["\'].*?' .
            '["\'](?:price|salePrice|currentPrice|discountPrice|minPrice|maxPrice)["\']\s*:\s*["\']?([0-9]+(?:\.[0-9]+)?)["\']?/is',

            /*
            | price + title
            */
            '/["\'](?:price|salePrice|currentPrice|discountPrice|minPrice|maxPrice)["\']\s*:\s*["\']?([0-9]+(?:\.[0-9]+)?)["\']?.{0,3000}?' .
            '["\'](?:name|title|productName|productTitle)["\']\s*:\s*["\'](.{3,500}?)["\']/is',
        ];

        foreach ($patterns as $pattern) {

            if (!preg_match_all(
                $pattern,
                $decodedHtml,
                $matches,
                PREG_SET_ORDER
            )) {
                continue;
            }

            foreach ($matches as $match) {

                /*
                |--------------------------------------------------------------
                | Determine title / price
                |--------------------------------------------------------------
                */

                if (
                    isset($match[1]) &&
                    is_numeric($match[1])
                ) {
                    $priceRaw = $match[1];
                    $titleRaw = $match[2] ?? '';
                } else {
                    $titleRaw = $match[1] ?? '';
                    $priceRaw = $match[2] ?? '';
                }

                $title =
                    $this->cleanText($titleRaw);

                if ($title === '') {
                    continue;
                }

                $priceValue =
                    $this->parsePrice($priceRaw);

                /*
                |--------------------------------------------------------------
                | Find image near product data
                |--------------------------------------------------------------
                */

                $image =
                    $this->findAliExpressImage(
                        $decodedHtml,
                        $title
                    );

                /*
                |--------------------------------------------------------------
                | Find URL near product data
                |--------------------------------------------------------------
                */

                $url =
                    $this->findAliExpressUrl(
                        $decodedHtml,
                        $title
                    );

                $products[] = [
                    'title' =>
                        $title,

                    'name' =>
                        $title,

                    'image' =>
                        $this->normalizeImage(
                            $image
                        ),

                    'price' =>
                        $priceValue !== null
                            ? $this->formatPrice(
                                $priceValue,
                                'AliExpress'
                            )
                            : 'Price unavailable',

                    'price_value' =>
                        $priceValue,

                    'rating' =>
                        null,

                    'reviews' =>
                        null,

                    'seller' =>
                        'AliExpress',

                    'url' =>
                        $this->normalizeUrl(
                            $url
                        ),
                ];

                if (count($products) >= 30) {
                    break 2;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Scan image + title + price blocks
        |--------------------------------------------------------------------------
        */

        if (count($products) < 30) {

            $imagePattern =
                '/["\'](?:image|imageUrl|imageURL|image_url|pic|picUrl|productImage|mainImage)["\']\s*:\s*["\']([^"\']{20,1000})["\']/i';

            if (preg_match_all(
                $imagePattern,
                $decodedHtml,
                $imageMatches
            )) {

                foreach ($imageMatches[1] as $image) {

                    $image =
                        $this->normalizeImage(
                            $image
                        );

                    if (!$image) {
                        continue;
                    }

                    /*
                    |----------------------------------------------------------
                    | Find nearby data around image
                    |----------------------------------------------------------
                    */

                    $position =
                        strpos(
                            $decodedHtml,
                            $image
                        );

                    if ($position === false) {
                        continue;
                    }

                    $chunk =
                        substr(
                            $decodedHtml,
                            max(0, $position - 5000),
                            10000
                        );

                    $title = '';

                    if (preg_match(
                        '/["\'](?:name|title|productName|productTitle)["\']\s*:\s*["\'](.{3,500}?)["\']/is',
                        $chunk,
                        $titleMatch
                    )) {
                        $title =
                            $this->cleanText(
                                $titleMatch[1]
                            );
                    }

                    if ($title === '') {
                        continue;
                    }

                    $priceValue = null;

                    if (preg_match(
                        '/["\'](?:price|salePrice|currentPrice|discountPrice|minPrice|maxPrice)["\']\s*:\s*["\']?([0-9]+(?:\.[0-9]+)?)["\']?/i',
                        $chunk,
                        $priceMatch
                    )) {

                        $priceValue =
                            $this->parsePrice(
                                $priceMatch[1]
                            );
                    }

                    $products[] = [
                        'title' =>
                            $title,

                        'name' =>
                            $title,

                        'image' =>
                            $image,

                        'price' =>
                            $priceValue !== null
                                ? $this->formatPrice(
                                    $priceValue,
                                    'AliExpress'
                                )
                                : 'Price unavailable',

                        'price_value' =>
                            $priceValue,

                        'rating' =>
                            null,

                        'reviews' =>
                            null,

                        'seller' =>
                            'AliExpress',

                        'url' =>
                            null,
                    ];

                    if (count($products) >= 30) {
                        break;
                    }
                }
            }
        }

        return $this->dedupeProducts(
            $products
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Find AliExpress Image
    |--------------------------------------------------------------------------
    */

    private function findAliExpressImage(
        string $html,
        string $title
    ): ?string {
        if ($title === '') {
            return null;
        }

        $position =
            stripos(
                $html,
                $title
            );

        if ($position === false) {
            return null;
        }

        $chunk =
            substr(
                $html,
                max(0, $position - 5000),
                10000
            );

        $patterns = [

            '/["\'](?:image|imageUrl|imageURL|image_url|pic|picUrl|productImage|mainImage)["\']\s*:\s*["\']([^"\']{20,1000})["\']/i',

            '/["\'](?:src|srcUrl|imageSrc)["\']\s*:\s*["\']([^"\']{20,1000})["\']/i',

        ];

        foreach ($patterns as $pattern) {

            if (preg_match(
                $pattern,
                $chunk,
                $match
            )) {

                $image =
                    $this->normalizeImage(
                        $match[1]
                    );

                if ($image) {
                    return $image;
                }
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Find AliExpress URL
    |--------------------------------------------------------------------------
    */

    private function findAliExpressUrl(
        string $html,
        string $title
    ): ?string {
        if ($title === '') {
            return null;
        }

        $position =
            stripos(
                $html,
                $title
            );

        if ($position === false) {
            return null;
        }

        $chunk =
            substr(
                $html,
                max(0, $position - 5000),
                10000
            );

        $patterns = [

            '/["\'](?:url|productUrl|product_url|itemUrl|item_url)["\']\s*:\s*["\']([^"\']+)["\']/i',

            '/["\'](?:href|link)["\']\s*:\s*["\']([^"\']+)["\']/i',

        ];

        foreach ($patterns as $pattern) {

            if (preg_match(
                $pattern,
                $chunk,
                $match
            )) {

                return $this->normalizeUrl(
                    $match[1]
                );
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Temu
    |--------------------------------------------------------------------------
    */

    private function fetchTemu(string $query): array
    {
        $url =
            'https://www.temu.com/search_result.html';

        $response = Http::withHeaders($this->headers())
            ->timeout(30)
            ->retry(2, 1000)
            ->get($url, [
                'search_key' => $query,
            ]);

        Log::info('Temu response', [
            'status' => $response->status(),
            'length' => strlen($response->body()),
        ]);

        if (!$response->successful()) {
            return [];
        }

        $html = $response->body();

        $products = [];

        $products = array_merge(
            $products,
            $this->parseJsonLd(
                $html,
                'Temu'
            )
        );

        $products = array_merge(
            $products,
            $this->parseEmbeddedProducts(
                $html,
                'Temu'
            )
        );

        return $this->dedupeProducts($products);
    }


    /*
    |--------------------------------------------------------------------------
    | JSON-LD Parser
    |--------------------------------------------------------------------------
    */

    private function parseJsonLd(
        string $html,
        string $seller
    ): array {
        $products = [];

        if (!preg_match_all(
            '/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is',
            $html,
            $matches
        )) {
            return [];
        }

        foreach ($matches[1] as $json) {

            $json =
                html_entity_decode(
                    $json,
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );

            $data =
                json_decode(
                    trim($json),
                    true
                );

            if (!is_array($data)) {
                continue;
            }

            $items = [];

            if (
                isset($data['@graph']) &&
                is_array($data['@graph'])
            ) {
                $items =
                    $data['@graph'];
            } else {
                $items =
                    [$data];
            }

            foreach ($items as $item) {

                if (!is_array($item)) {
                    continue;
                }

                $type =
                    $item['@type'] ?? '';

                $isProduct = false;

                if (is_string($type)) {
                    $isProduct =
                        strtolower($type) ===
                        'product';
                }

                if (is_array($type)) {
                    $isProduct =
                        in_array(
                            'Product',
                            $type,
                            true
                        );
                }

                if (
                    !$isProduct &&
                    !isset($item['name'])
                ) {
                    continue;
                }

                $title =
                    $this->cleanText(
                        $item['name']
                        ?? $item['title']
                        ?? ''
                    );

                if ($title === '') {
                    continue;
                }

                $image =
                    $item['image']
                    ?? null;

                if (is_array($image)) {
                    $image =
                        $image[0] ?? null;
                }

                $url =
                    $item['url']
                    ?? null;

                $priceValue =
                    null;

                if (
                    isset($item['offers']) &&
                    is_array($item['offers'])
                ) {

                    $offers =
                        $item['offers'];

                    if (isset($offers[0])) {
                        $offers =
                            $offers[0];
                    }

                    $priceValue =
                        $this->parsePrice(
                            $offers['price']
                            ?? $offers['lowPrice']
                            ?? null
                        );
                }

                if ($priceValue === null) {

                    $priceValue =
                        $this->parsePrice(
                            $item['price']
                            ?? null
                        );
                }

                $rating =
                    null;

                $reviews =
                    null;

                if (
                    isset($item['aggregateRating']) &&
                    is_array($item['aggregateRating'])
                ) {

                    $rating =
                        $this->parseRating(
                            $item['aggregateRating']['ratingValue']
                            ?? null
                        );

                    $reviews =
                        $this->parseReviews(
                            $item['aggregateRating']['reviewCount']
                            ?? $item['aggregateRating']['ratingCount']
                            ?? null
                        );
                }

                $products[] = [
                    'title' =>
                        $title,

                    'name' =>
                        $title,

                    'image' =>
                        $this->normalizeImage(
                            is_string($image)
                                ? $image
                                : null
                        ),

                    'price' =>
                        $priceValue !== null
                            ? $this->formatPrice(
                                $priceValue,
                                $seller
                            )
                            : 'Price unavailable',

                    'price_value' =>
                        $priceValue,

                    'rating' =>
                        $rating,

                    'reviews' =>
                        $reviews,

                    'seller' =>
                        $seller,

                    'url' =>
                        $this->normalizeUrl(
                            is_string($url)
                                ? $url
                                : null
                        ),
                ];

                if (count($products) >= 30) {
                    return $this->dedupeProducts(
                        $products
                    );
                }
            }
        }

        return $this->dedupeProducts(
            $products
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Embedded Product Parser
    |--------------------------------------------------------------------------
    */

    private function parseEmbeddedProducts(
        string $html,
        string $seller
    ): array {
        $products = [];

        $html =
            html_entity_decode(
                $html,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

        $html = str_replace(
            [
                '\\/',
                '\\u002F',
                '\\u002f',
            ],
            [
                '/',
                '/',
                '/',
            ],
            $html
        );

        /*
        |--------------------------------------------------------------------------
        | Title + Price
        |--------------------------------------------------------------------------
        */

        $patterns = [

            '/["\'](?:name|title|productName|productTitle)["\']\s*:\s*["\']([^"\']{3,500})["\'][\s\S]{0,3000}?' .
            '["\'](?:price|salePrice|currentPrice|discountPrice|originalPrice|minPrice|maxPrice)["\']\s*:\s*["\']?([0-9]+(?:\.[0-9]+)?)["\']?/i',

            '/["\'](?:name|title|productName|productTitle)["\']\s*:\s*"([^"]{3,500})"[\s\S]{0,3000}?' .
            '"(?:price|salePrice|currentPrice|discountPrice)"\s*:\s*([0-9]+(?:\.[0-9]+)?)/i',
        ];

        foreach ($patterns as $pattern) {

            if (!preg_match_all(
                $pattern,
                $html,
                $matches,
                PREG_SET_ORDER
            )) {
                continue;
            }

            foreach ($matches as $match) {

                $title =
                    $this->cleanText(
                        $match[1] ?? ''
                    );

                if ($title === '') {
                    continue;
                }

                $priceValue =
                    $this->parsePrice(
                        $match[2] ?? null
                    );

                /*
                |--------------------------------------------------------------------------
                | Try to find image around title
                |--------------------------------------------------------------------------
                */

                $image =
                    $this->findNearbyImage(
                        $html,
                        $title
                    );

                $url =
                    $this->findNearbyUrl(
                        $html,
                        $title
                    );

                $products[] = [
                    'title' =>
                        $title,

                    'name' =>
                        $title,

                    'image' =>
                        $this->normalizeImage(
                            $image
                        ),

                    'price' =>
                        $priceValue !== null
                            ? $this->formatPrice(
                                $priceValue,
                                $seller
                            )
                            : 'Price unavailable',

                    'price_value' =>
                        $priceValue,

                    'rating' =>
                        null,

                    'reviews' =>
                        null,

                    'seller' =>
                        $seller,

                    'url' =>
                        $this->normalizeUrl(
                            $url
                        ),
                ];

                if (count($products) >= 30) {
                    return $this->dedupeProducts(
                        $products
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | JSON Objects
        |--------------------------------------------------------------------------
        */

        if (preg_match_all(
            '/\{[^{}]{0,10000}\}/s',
            $html,
            $matches
        )) {

            foreach ($matches[0] as $jsonObject) {

                $data =
                    json_decode(
                        $jsonObject,
                        true
                    );

                if (!is_array($data)) {
                    continue;
                }

                $title =
                    $this->cleanText(
                        $data['name']
                        ?? $data['title']
                        ?? $data['productName']
                        ?? $data['productTitle']
                        ?? ''
                    );

                if ($title === '') {
                    continue;
                }

                $priceValue =
                    $this->findPriceInArray(
                        $data
                    );

                $image =
                    $this->findImageInArray(
                        $data
                    );

                $url =
                    $data['url']
                    ?? $data['productUrl']
                    ?? $data['product_url']
                    ?? $data['itemUrl']
                    ?? null;

                $rating =
                    $this->findNumericValue(
                        $data,
                        [
                            'rating',
                            'ratingValue',
                        ]
                    );

                $reviews =
                    $this->findNumericValue(
                        $data,
                        [
                            'reviews',
                            'reviewCount',
                            'ratingCount',
                        ]
                    );

                $products[] = [
                    'title' =>
                        $title,

                    'name' =>
                        $title,

                    'image' =>
                        $this->normalizeImage(
                            is_string($image)
                                ? $image
                                : null
                        ),

                    'price' =>
                        $priceValue !== null
                            ? $this->formatPrice(
                                $priceValue,
                                $seller
                            )
                            : 'Price unavailable',

                    'price_value' =>
                        $priceValue,

                    'rating' =>
                        $rating !== null
                            ? (float) $rating
                            : null,

                    'reviews' =>
                        $reviews !== null
                            ? (int) $reviews
                            : null,

                    'seller' =>
                        $seller,

                    'url' =>
                        $this->normalizeUrl(
                            is_string($url)
                                ? $url
                                : null
                        ),
                ];

                if (count($products) >= 30) {
                    return $this->dedupeProducts(
                        $products
                    );
                }
            }
        }

        return $this->dedupeProducts(
            $products
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Find Nearby Image
    |--------------------------------------------------------------------------
    */

    private function findNearbyImage(
        string $html,
        string $title
    ): ?string {
        $position =
            stripos(
                $html,
                $title
            );

        if ($position === false) {
            return null;
        }

        $chunk =
            substr(
                $html,
                max(0, $position - 4000),
                8000
            );

        $patterns = [

            '/["\'](?:image|imageUrl|imageURL|image_url|pic|picUrl|productImage|mainImage)["\']\s*:\s*["\']([^"\']{20,1000})["\']/i',

            '/["\'](?:src|srcUrl|imageSrc)["\']\s*:\s*["\']([^"\']{20,1000})["\']/i',

        ];

        foreach ($patterns as $pattern) {

            if (preg_match(
                $pattern,
                $chunk,
                $match
            )) {

                $image =
                    $this->normalizeImage(
                        $match[1]
                    );

                if ($image) {
                    return $image;
                }
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Find Nearby URL
    |--------------------------------------------------------------------------
    */

    private function findNearbyUrl(
        string $html,
        string $title
    ): ?string {
        $position =
            stripos(
                $html,
                $title
            );

        if ($position === false) {
            return null;
        }

        $chunk =
            substr(
                $html,
                max(0, $position - 3000),
                6000
            );

        $patterns = [

            '/["\'](?:url|productUrl|product_url|itemUrl|item_url)["\']\s*:\s*["\']([^"\']+)["\']/i',

            '/["\'](?:href|link)["\']\s*:\s*["\']([^"\']+)["\']/i',

        ];

        foreach ($patterns as $pattern) {

            if (preg_match(
                $pattern,
                $chunk,
                $match
            )) {

                return $this->normalizeUrl(
                    $match[1]
                );
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Find Price Recursively
    |--------------------------------------------------------------------------
    */

    private function findPriceInArray(
        array $data
    ): ?float {
        $priceKeys = [

            'price',

            'salePrice',

            'currentPrice',

            'discountPrice',

            'originalPrice',

            'minPrice',

            'maxPrice',

            'min_price',

            'max_price',

            'sale_price',

            'current_price',

        ];

        foreach ($priceKeys as $key) {

            if (
                array_key_exists(
                    $key,
                    $data
                ) &&
                is_scalar(
                    $data[$key]
                )
            ) {

                $price =
                    $this->parsePrice(
                        (string) $data[$key]
                    );

                if ($price !== null) {
                    return $price;
                }
            }
        }

        foreach ($data as $value) {

            if (is_array($value)) {

                $price =
                    $this->findPriceInArray(
                        $value
                    );

                if ($price !== null) {
                    return $price;
                }
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Find Image Recursively
    |--------------------------------------------------------------------------
    */

    private function findImageInArray(
        array $data
    ): ?string {
        $imageKeys = [

            'image',

            'imageUrl',

            'imageURL',

            'image_url',

            'pic',

            'picUrl',

            'picURL',

            'picture',

            'pictureUrl',

            'productImage',

            'productImageUrl',

            'mainImage',

            'mainImageUrl',

            'coverImage',

            'src',

        ];

        foreach ($imageKeys as $key) {

            if (
                array_key_exists(
                    $key,
                    $data
                )
            ) {

                $value =
                    $data[$key];

                if (is_string($value)) {

                    $image =
                        $this->normalizeImage(
                            $value
                        );

                    if ($image) {
                        return $image;
                    }
                }

                if (is_array($value)) {

                    foreach ($value as $item) {

                        if (is_string($item)) {

                            $image =
                                $this->normalizeImage(
                                    $item
                                );

                            if ($image) {
                                return $image;
                            }
                        }
                    }
                }
            }
        }

        foreach ($data as $value) {

            if (is_array($value)) {

                $image =
                    $this->findImageInArray(
                        $value
                    );

                if ($image !== null) {
                    return $image;
                }
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Find Numeric Value
    |--------------------------------------------------------------------------
    */

    private function findNumericValue(
        array $data,
        array $keys
    ): ?float {
        foreach ($keys as $key) {

            if (
                array_key_exists(
                    $key,
                    $data
                ) &&
                is_numeric(
                    $data[$key]
                )
            ) {

                return (float) $data[$key];
            }
        }

        foreach ($data as $value) {

            if (is_array($value)) {

                $result =
                    $this->findNumericValue(
                        $value,
                        $keys
                    );

                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Deduplicate + Normalize
    |--------------------------------------------------------------------------
    */

    private function dedupeProducts(
        array $products
    ): array {
        $unique = [];

        $seen = [];

        foreach ($products as $product) {

            if (!is_array($product)) {
                continue;
            }

            $title =
                $this->cleanText(
                    $product['title']
                    ?? $product['name']
                    ?? ''
                );

            if ($title === '') {
                continue;
            }

            $seller =
                trim(
                    (string) (
                        $product['seller']
                        ?? 'Unknown'
                    )
                );

            $key =
                strtolower(
                    $seller . '|' . $title
                );

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            /*
            |--------------------------------------------------------------------------
            | Image
            |--------------------------------------------------------------------------
            */

            $image =
                $product['image']
                ?? $product['image_url']
                ?? $product['imageUrl']
                ?? null;

            /*
            |--------------------------------------------------------------------------
            | Price
            |--------------------------------------------------------------------------
            */

            $price =
                $product['price']
                ?? 'Price unavailable';

            $priceValue =
                $product['price_value']
                ?? $product['priceValue']
                ?? null;

            if (
                $priceValue === null &&
                is_string($price)
            ) {

                $priceValue =
                    $this->parsePrice(
                        $price
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | URL
            |--------------------------------------------------------------------------
            */

            $url =
                $product['url']
                ?? $product['product_url']
                ?? $product['productUrl']
                ?? '';

            /*
            |--------------------------------------------------------------------------
            | Final product
            |--------------------------------------------------------------------------
            */

            $product['title'] =
                $title;

            $product['name'] =
                $title;

            $product['image'] =
                $this->normalizeImage(
                    is_string($image)
                        ? $image
                        : null
                );

            /*
            | If an image is missing but another
            | image field exists, try it.
            */

            if (
                empty($product['image'])
            ) {

                $fallbackImage =
                    $this->findImageInArray(
                        $product
                    );

                if ($fallbackImage) {
                    $product['image'] =
                        $fallbackImage;
                }
            }

            $product['price'] =
                $price;

            $product['price_value'] =
                $priceValue;

            $product['seller'] =
                $seller !== ''
                    ? $seller
                    : 'Unknown';

            $product['url'] =
                $this->normalizeUrl(
                    is_string($url)
                        ? $url
                        : null
                );

            $product['rating'] =
                isset($product['rating']) &&
                is_numeric(
                    $product['rating']
                )
                    ? (float) $product['rating']
                    : null;

            $product['reviews'] =
                isset($product['reviews']) &&
                is_numeric(
                    $product['reviews']
                )
                    ? (int) $product['reviews']
                    : null;

            $unique[] =
                $product;
        }

        return $unique;
    }


    /*
    |--------------------------------------------------------------------------
    | DOM Helpers
    |--------------------------------------------------------------------------
    */

    private function xpathText(
        DOMXPath $xpath,
        string $query,
        ?DOMNode $context = null
    ): string {
        $nodes =
            $xpath->query(
                $query,
                $context
            );

        if (
            $nodes === false ||
            $nodes->length === 0
        ) {
            return '';
        }

        return $this->cleanText(
            $nodes->item(0)->textContent ?? ''
        );
    }


    private function xpathAttribute(
        DOMXPath $xpath,
        string $query,
        string $attribute,
        ?DOMNode $context = null
    ): string {
        $nodes =
            $xpath->query(
                $query,
                $context
            );

        if (
            $nodes === false ||
            $nodes->length === 0
        ) {
            return '';
        }

        return trim(
            $nodes->item(0)
                ->attributes
                ->getNamedItem($attribute)
                ?->nodeValue
            ?? ''
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Text Cleaning
    |--------------------------------------------------------------------------
    */

    private function cleanText(
        ?string $text
    ): string {
        if ($text === null) {
            return '';
        }

        $text =
            html_entity_decode(
                $text,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

        $text =
            preg_replace(
                '/\s+/u',
                ' ',
                $text
            );

        return trim(
            (string) $text
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Price Parser
    |--------------------------------------------------------------------------
    */

    private function parsePrice(
        mixed $value
    ): ?float {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value =
            html_entity_decode(
                (string) $value,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

        $value =
            preg_replace(
                '/[^\d.,-]/u',
                '',
                $value
            );

        if ($value === '') {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | 1,299.99
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($value, ',') &&
            str_contains($value, '.')
        ) {

            $value =
                str_replace(
                    ',',
                    '',
                    $value
                );

        } elseif (
            str_contains($value, ',')
        ) {

            /*
            | 1,299
            */

            if (
                preg_match(
                    '/^\d{1,3}(,\d{3})+$/',
                    $value
                )
            ) {

                $value =
                    str_replace(
                        ',',
                        '',
                        $value
                    );

            } else {

                $value =
                    str_replace(
                        ',',
                        '.',
                        $value
                    );
            }
        }

        return is_numeric($value)
            ? (float) $value
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Price Formatting
    |--------------------------------------------------------------------------
    */

    private function formatPrice(
        float $price,
        string $seller
    ): string {
        if ($seller === 'Daraz') {

            return 'Rs. ' .
                number_format(
                    $price,
                    0
                );
        }

        return '$' .
            number_format(
                $price,
                2
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Rating Parser
    |--------------------------------------------------------------------------
    */

    private function parseRating(
        mixed $value
    ): ?float {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        if (is_numeric($value)) {

            $rating =
                (float) $value;

            return $rating >= 0
                ? $rating
                : null;
        }

        if (
            preg_match(
                '/([0-5](?:\.\d+)?)/',
                (string) $value,
                $match
            )
        ) {

            return (float) $match[1];
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Reviews Parser
    |--------------------------------------------------------------------------
    */

    private function parseReviews(
        mixed $value
    ): ?int {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (
            preg_match(
                '/([\d,]+)/',
                (string) $value,
                $match
            )
        ) {

            return (int) str_replace(
                ',',
                '',
                $match[1]
            );
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Image Normalization
    |--------------------------------------------------------------------------
    */

    private function normalizeImage(
        ?string $image
    ): ?string {
        if (
            $image === null ||
            trim($image) === ''
        ) {
            return null;
        }

        $image =
            trim($image);

        /*
        |--------------------------------------------------------------------------
        | Remove escaped URLs
        |--------------------------------------------------------------------------
        */

        $image =
            str_replace(
                [
                    '\\/',
                    '\\u002F',
                    '\\u002f',
                    '\\u003A',
                    '\\u003a',
                    '&amp;',
                ],
                [
                    '/',
                    '/',
                    '/',
                    ':',
                    ':',
                    '&',
                ],
                $image
            );

        /*
        |--------------------------------------------------------------------------
        | Decode HTML entities
        |--------------------------------------------------------------------------
        */

        $image =
            html_entity_decode(
                $image,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

        /*
        |--------------------------------------------------------------------------
        | Protocol-relative URL
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $image,
                '//'
            )
        ) {

            $image =
                'https:' . $image;
        }

        /*
        |--------------------------------------------------------------------------
        | AliExpress sometimes stores escaped unicode
        |--------------------------------------------------------------------------
        */

        $image =
            preg_replace_callback(
                '/\\\\u([0-9a-fA-F]{4})/',
                function ($match) {

                    $code =
                        hexdec(
                            $match[1]
                        );

                    return mb_convert_encoding(
                        '&#' . $code . ';',
                        'UTF-8',
                        'HTML-ENTITIES'
                    );
                },
                $image
            );

        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        if (
            !str_starts_with(
                $image,
                'http://'
            ) &&
            !str_starts_with(
                $image,
                'https://'
            )
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Ignore placeholders
        |--------------------------------------------------------------------------
        */

        $lower =
            strtolower($image);

        if (
            str_contains(
                $lower,
                'placeholder'
            ) ||
            str_contains(
                $lower,
                'no-image'
            ) ||
            str_contains(
                $lower,
                'no_image'
            ) ||
            str_contains(
                $lower,
                'default-image'
            )
        ) {
            return null;
        }

        return $image;
    }


    /*
    |--------------------------------------------------------------------------
    | URL Normalization
    |--------------------------------------------------------------------------
    */

    private function normalizeUrl(
        ?string $url
    ): ?string {
        if (
            $url === null ||
            trim($url) === ''
        ) {
            return null;
        }

        $url =
            trim($url);

        $url =
            str_replace(
                [
                    '\\/',
                    '\\u002F',
                    '\\u002f',
                    '&amp;',
                ],
                [
                    '/',
                    '/',
                    '/',
                    '&',
                ],
                $url
            );

        $url =
            html_entity_decode(
                $url,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

        if (
            str_starts_with(
                $url,
                '//'
            )
        ) {

            $url =
                'https:' . $url;
        }

        if (
            str_starts_with(
                $url,
                '/'
            )
        ) {
            return $url;
        }

        if (
            !str_starts_with(
                $url,
                'http://'
            ) &&
            !str_starts_with(
                $url,
                'https://'
            )
        ) {
            return null;
        }

        return $url;
    }
}