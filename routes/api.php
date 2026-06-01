<?php

use App\Http\Controllers\ShopifyWebhookController;
use Illuminate\Support\Facades\Route;

// Shopify webhooks — HMAC verified inside the controller, raw body preserved
Route::post('/webhooks/shopify/orders-paid', ShopifyWebhookController::class)
    ->name('webhooks.shopify.orders-paid')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
