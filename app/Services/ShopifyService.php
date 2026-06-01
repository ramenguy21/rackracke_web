<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ShopifyService
{
    private string $domain;
    private string $token;
    private string $apiVersion = '2024-10';

    public function __construct()
    {
        $this->domain = config('services.shopify.store_domain');
        $this->token  = config('services.shopify.admin_token');
    }

    /**
     * Push an approved listing to Shopify as a product.
     * Creates the seller's collection if it doesn't exist yet.
     */
    public function pushListing(Listing $listing): void
    {
        $seller = $listing->seller;

        // 1. Ensure collection exists
        $handle = Str::slug($seller->shop_name);
        $collectionId = $this->ensureCollection($seller->shop_name, $handle);

        // 2. Create product
        $response = $this->post('products.json', [
            'product' => [
                'title'       => $listing->title,
                'body_html'   => e($listing->description ?? ''),
                'vendor'      => $seller->shop_name,
                'product_type' => 'Secondhand',
                'variants'    => [[
                    'price'                => (string) $listing->price_pkr,
                    'inventory_management' => 'shopify',
                    'inventory_quantity'   => 1,
                ]],
            ],
        ]);

        $productId = $response['product']['id'];
        $variantId = $response['product']['variants'][0]['id'];

        // 3. Assign to collection
        $this->post('collects.json', [
            'collect' => [
                'product_id'    => $productId,
                'collection_id' => $collectionId,
            ],
        ]);

        // 4. Persist IDs
        $listing->update([
            'shopify_product_id' => (string) $productId,
            'collection_handle'  => $handle,
        ]);
    }

    private function ensureCollection(string $title, string $handle): int
    {
        // Check if collection with this handle already exists
        $existing = $this->get("custom_collections.json?handle={$handle}");
        if (!empty($existing['custom_collections'])) {
            return $existing['custom_collections'][0]['id'];
        }

        $response = $this->post('custom_collections.json', [
            'custom_collection' => [
                'title'  => $title,
                'handle' => $handle,
            ],
        ]);

        return $response['custom_collection']['id'];
    }

    private function get(string $endpoint): array
    {
        return Http::withHeaders(['X-Shopify-Access-Token' => $this->token])
            ->get("https://{$this->domain}/admin/api/{$this->apiVersion}/{$endpoint}")
            ->throw()
            ->json();
    }

    private function post(string $endpoint, array $data): array
    {
        return Http::withHeaders(['X-Shopify-Access-Token' => $this->token])
            ->post("https://{$this->domain}/admin/api/{$this->apiVersion}/{$endpoint}", $data)
            ->throw()
            ->json();
    }
}
