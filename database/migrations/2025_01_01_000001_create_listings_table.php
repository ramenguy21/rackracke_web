<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('seller_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('condition'); // new_with_tags | excellent | good | fair
            $table->unsignedInteger('price_pkr');
            $table->json('photos')->default('[]');
            $table->string('shopify_product_id')->nullable()->unique();
            $table->string('collection_handle')->nullable();
            $table->string('status')->default('draft'); // draft | pending_review | live | sold | withdrawn
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
