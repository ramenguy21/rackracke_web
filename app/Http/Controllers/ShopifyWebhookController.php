<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessShopifyOrderPaid;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShopifyWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // 1. Verify HMAC against raw body — must happen before any parsing.
        $rawBody = $request->getContent();
        $hmac    = $request->header('X-Shopify-Hmac-Sha256');
        $secret  = config('services.shopify.webhook_secret');

        $computed = base64_encode(hash_hmac('sha256', $rawBody, $secret, true));

        if (!hash_equals($computed, (string) $hmac)) {
            return response('Unauthorized', 401);
        }

        // 2. Dispatch idempotent queued job; return fast.
        $payload = json_decode($rawBody, true);
        ProcessShopifyOrderPaid::dispatch($payload);

        return response('OK', 200);
    }
}
