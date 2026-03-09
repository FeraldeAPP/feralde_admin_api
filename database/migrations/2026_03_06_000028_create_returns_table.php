<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->text('reason');
            $table->string('status')->default('PENDING');
            $table->decimal('refund_amount', 18, 4)->nullable();
            $table->string('refund_method')->nullable();
            $table->text('notes')->nullable();
            $table->string('processed_by')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
