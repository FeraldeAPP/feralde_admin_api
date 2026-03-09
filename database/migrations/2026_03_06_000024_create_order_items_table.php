<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('bundle_id')->nullable();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->string('sku');
            $table->integer('quantity');
            $table->decimal('unit_price', 18, 4);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('total_price', 18, 4);
            $table->decimal('cost_price', 18, 4)->nullable();
            $table->string('pricing_tier')->default('RETAIL');
            $table->decimal('commissionable_amount', 18, 4)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('order_id');
            $table->index('variant_id');
            $table->index('product_id');
            $table->index('bundle_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('bundle_id')->references('id')->on('bundles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
