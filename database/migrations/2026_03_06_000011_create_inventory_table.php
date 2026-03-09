<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id')->unique();
            $table->unsignedBigInteger('warehouse_id');
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('quantity_damaged')->default(0);
            $table->integer('reorder_point')->default(10);
            $table->integer('reorder_quantity')->default(50);
            $table->timestamp('last_stocked_at')->nullable();
            $table->timestamp('last_audited_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('warehouse_id');
            $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
