<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->unsignedBigInteger('reseller_id')->nullable();
            $table->decimal('amount', 18, 4);
            $table->decimal('fee', 18, 4)->default(0);
            $table->decimal('net_amount', 18, 4);
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, PROCESSING, COMPLETED, REJECTED, CANCELLED
            $table->string('bank_account_name');
            $table->string('bank_account_number');
            $table->string('bank_name')->nullable();
            $table->string('e_wallet_type')->nullable();
            $table->string('e_wallet_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('processed_by')->nullable(); // user_id from feralde_auth
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('distributor_id');
            $table->index('reseller_id');
            $table->foreign('wallet_id')->references('id')->on('wallets')->cascadeOnDelete();
            $table->foreign('distributor_id')->references('id')->on('distributor_profiles')->nullOnDelete();
            $table->foreign('reseller_id')->references('id')->on('reseller_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
