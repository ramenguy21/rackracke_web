<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
     * Push an approved listing to Shopify as an active product.
     * Creates the seller's collection if it doesn't exist yet.
     * Photos are included in the creation payload via presigned S3 URLs —
     * Shopify fetches and permanently re-hosts them.
     */
    public function pushListing(Listing $listing): void
    {
        $seller = $listing->seller;
        $handle = Str::slug($seller->shop_name);

        // 1. Ensure the seller's collection exists
        $collectionId = $this->ensureCollection($seller->shop_name, $handle);

        // 2. Build images array from presigned S3 URLs (30 min is plenty for Shopify to fetch)
        $images = array_values(array_filter(
            array_map(function (string $path) {
                try {
                    return ['src' => Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(30))];
                } catch (\Throwable $e) {
                    Log::warning('Could not generate presigned URL for listing photo', ['path' => $path, 'error' => $e->getMessage()]);
                    return null;
                }
            },
            $listing->photos ?? []
        )));

        // 3. Create product on Shopify
        $response = $this->post('products.json', [
            'product' => [
                'title'        => $listing->title,
                'body_html'    => e($listing->description ?? ''),
                'vendor'       => $seller->shop_name,
                'product_type' => 'Secondhand',
                'status'       => 'active',
                'images'       => $images,
                'variants'     => [[
                    'price'                => number_format($listing->price_pkr, 2, '.', ''),
                    'inventory_management' => 'shopify',
                    'inventory_quantity'   => 1,
                ]],
            ],
        ]);

        $productId = $response['product']['id'];

        // 4. Assign to seller's collection
        $this->post('collects.json', [
            'collect' => [
                'product_id'    => $productId,
                'collection_id' => $collectionId,
            ],
        ]);

        // 5. Persist IDs back to the listing
        $listing->update([
            'shopify_product_id' => (string) $productId,
            'collection_handle'  => $handle,
        ]);
    }

    /**
     * Archive a listing's Shopify product so it's no longer visible to buyers.
     * Safe to call even if shopify_product_id is not set.
     */
    public function withdrawListing(Listing $listing): void
    {
        if (!$listing->shopify_product_id) {
            return;
        }

        $this->put("products/{$listing->shopify_product_id}.json", [
            'product' => ['status' => 'archived'],
        ]);
    }

    /**
     * Register the orders/paid webhook on Shopify.
     * Checks for an existing subscription first — safe to call multiple times.
     *
     * @return array The webhook array (new or existing)
     */
    public function registerWebhook(string $address): array
    {
        // Check for existing subscription matching this topic + address
        $existing = $this->get('webhooks.json?' . http_build_query([
            'topic'   => 'orders/paid',
            'address' => $address,
        ]));

        if (!empty($existing['webhooks'])) {
            return $existing['webhooks'][0];
        }

        $response = $this->post('webhooks.json', [
            'webhook' => [
                'topic'   => 'orders/paid',
                'address' => $address,
                'format'  => 'json',
            ],
        ]);

        return $response['webhook'];
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function ensureCollection(string $title, string $handle): int
    {
        $existing = $this->get('custom_collections.json?' . http_build_query(['handle' => $handle]));
        if (!empty($existing['custom_collections'])) {
            return $existing['custom_collections'][0]['id'];
        }

        $response = $this->post('custom_collections.json', [
            'custom_collection' => [
                'title'     => $title,
                'handle'    => $handle,
                'published' => true,
            ],
        ]);

        return $response['custom_collection']['id'];
    }

    private function get(string $endpoint): array
    {
        $response = Http::withHeaders(['X-Shopify-Access-Token' => $this->token])
            ->get("https://{$this->domain}/admin/api/{$this->apiVersion}/{$endpoint}");

        if ($response->failed()) {
            Log::error('Shopify GET failed', [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            $response->throw();
        }

        return $response->json();
    }

    private function post(string $endpoint, array $data): array
    {
        $response = Http::withHeaders(['X-Shopify-Access-Token' => $this->token])
            ->post("https://{$this->domain}/admin/api/{$this->apiVersion}/{$endpoint}", $data);

        if ($response->failed()) {
            Log::error('Shopify POST failed', [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            $response->throw();
        }

        return $response->json();
    }

    private function put(string $endpoint, array $data): array
    {
        $response = Http::withHeaders(['X-Shopify-Access-Token' => $this->token])
            ->put("https://{$this->domain}/admin/api/{$this->apiVersion}/{$endpoint}", $data);

        if ($response->failed()) {
            Log::error('Shopify PUT failed', [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            $response->throw();
        }

        return $response->json();
    }
}
