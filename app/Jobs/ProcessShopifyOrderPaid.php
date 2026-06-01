<?php

namespace App\Jobs;

use App\Models\Listing;
use App\Models\Order;
use App\States\Order\Placed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessShopifyOrderPaid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly array $payload) {}

    public function handle(): void
    {
        $shopifyOrderId = (string) $this->payload['id'];

        // Idempotency: if this order already exists, do nothing.
        if (Order::where('shopify_order_id', $shopifyOrderId)->exists()) {
            return;
        }

        DB::transaction(function () use ($shopifyOrderId) {
            foreach ($this->payload['line_items'] ?? [] as $lineItem) {
                $shopifyProductId = (string) ($lineItem['product_id'] ?? '');
                $listing = Listing::where('shopify_product_id', $shopifyProductId)->first();

                if (!$listing) {
                    continue;
                }

                $paymentGateway = $this->payload['payment_gateway'] ?? '';
                $paymentType    = str_contains(strtolower($paymentGateway), 'cod') ? 'cod' : 'prepaid';

                $order = Order::create([
                    'listing_id'       => $listing->id,
                    'shopify_order_id' => $shopifyOrderId,
                    'payment_type'     => $paymentType,
                    'state'            => Placed::$name,
                    'sale_price_pkr'   => (int) round((float) ($lineItem['price'] ?? 0)),
                    'take_rate_pct'    => 8, // default commission; TODO: per-seller override
                    'buyer_contact'    => $this->payload['customer']['phone']
                                         ?? $this->payload['customer']['email']
                                         ?? null,
                ]);

                $listing->update(['status' => 'sold']);
            }
        });
    }
}
