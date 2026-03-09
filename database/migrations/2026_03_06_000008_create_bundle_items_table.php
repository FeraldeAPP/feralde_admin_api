<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bundle_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->integer('quantity')->default(1);

            $table->unique(['bundle_id', 'product_id', 'variant_id']);
            $table->foreign('bundle_id')->references('id')->on('bundles')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_items');
    }
};
