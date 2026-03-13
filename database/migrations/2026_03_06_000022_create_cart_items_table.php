<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('variant_id');
            $table->integer('quantity');
            $table->timestamp('added_at')->useCurrent();

            $table->unique(['cart_id', 'variant_id']);
            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
