<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('shop_name')->unique();
            $table->string('phone');
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('payout_method')->nullable();
            $table->string('status')->default('pending'); // pending | approved | suspended
            $table->string('city')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('verified')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('seller_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('seller_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_sessions');
        Schema::dropIfExists('seller_password_reset_tokens');
        Schema::dropIfExists('sellers');
    }
};
