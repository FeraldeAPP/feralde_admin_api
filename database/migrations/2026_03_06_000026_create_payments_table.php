<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('method'); // GCASH, MAYA, CREDIT_CARD, DEBIT_CARD, CASH_ON_DELIVERY, BANK_TRANSFER, WALLET
            $table->string('status')->default('PENDING'); // PENDING, PAID, FAILED, REFUNDED, PARTIALLY_REFUNDED
            $table->decimal('amount', 18, 4);
            $table->string('currency')->default('PHP');
            $table->string('gateway_reference')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refunded_amount', 18, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
