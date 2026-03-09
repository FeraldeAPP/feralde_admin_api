<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_channels', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // WEBSITE, TIKTOK_SHOP, LAZADA, SHOPEE, MANUAL
            $table->string('name');
            $table->string('external_store_id')->nullable();
            $table->json('credentials')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('sync_settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_channels');
    }
};
