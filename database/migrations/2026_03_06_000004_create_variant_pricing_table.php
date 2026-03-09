<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_pricing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->string('tier'); // RETAIL, DISTRIBUTOR, RESELLER, WHOLESALE
            $table->decimal('price', 18, 4);
            $table->decimal('compare_at_price', 18, 4)->nullable();
            $table->integer('min_quantity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamp('effective_from')->useCurrent();
            $table->timestamp('effective_to')->nullable();

            $table->unique(['variant_id', 'tier']);
            $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_pricing');
    }
};
