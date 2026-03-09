<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_promo_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('promo_code_id');
            $table->decimal('discount', 18, 4);
            $table->timestamp('applied_at')->useCurrent();

            $table->unique(['order_id', 'promo_code_id']);
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_promo_usages');
    }
};
