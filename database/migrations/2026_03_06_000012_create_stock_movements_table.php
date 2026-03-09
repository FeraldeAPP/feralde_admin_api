<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('type'); // STOCK_IN, STOCK_OUT, ADJUSTMENT, DAMAGED, RETURNED, TRANSFERRED, RESERVED, RELEASED
            $table->integer('quantity');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('performed_by')->nullable(); // user_id from feralde_auth
            $table->timestamp('created_at')->useCurrent();

            $table->index('variant_id');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
            $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
