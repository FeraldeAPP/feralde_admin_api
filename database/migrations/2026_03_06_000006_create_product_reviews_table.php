<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('user_id'); // cross-db reference to feralde_auth users
            $table->string('order_id')->nullable(); // cross-db reference
            $table->integer('rating');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
