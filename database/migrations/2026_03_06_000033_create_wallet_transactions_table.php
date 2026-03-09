<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->string('type'); // CREDIT_COMMISSION, CREDIT_REFUND, CREDIT_ADJUSTMENT, DEBIT_WITHDRAWAL, DEBIT_PURCHASE, DEBIT_REVERSAL
            $table->decimal('amount', 18, 4);
            $table->decimal('balance_before', 18, 4);
            $table->decimal('balance_after', 18, 4);
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('wallet_id');
            $table->index(['reference_type', 'reference_id']);
            $table->foreign('wallet_id')->references('id')->on('wallets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
