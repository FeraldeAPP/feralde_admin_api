<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_id');
            $table->unsignedBigInteger('variant_id');
            $table->string('external_listing_id');
            $table->string('external_sku')->nullable();
            $table->decimal('external_price', 18, 4)->nullable();
            $table->integer('external_stock')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique(['channel_id', 'variant_id']);
            $table->foreign('channel_id')->references('id')->on('sales_channels')->cascadeOnDelete();
            $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_products');
    }
};
