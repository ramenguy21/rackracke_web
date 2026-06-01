<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('listing_id')->constrained()->cascadeOnDelete();
            $table->string('shopify_order_id')->unique(); // idempotency guard
            $table->string('payment_type'); // prepaid | cod
            $table->string('state'); // spatie model-states cast
            $table->unsignedInteger('sale_price_pkr'); // snapshot at order time
            $table->unsignedSmallInteger('take_rate_pct'); // snapshot of commission rate
            $table->string('buyer_contact')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
