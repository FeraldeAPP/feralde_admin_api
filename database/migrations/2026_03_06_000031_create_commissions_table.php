<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->unsignedBigInteger('reseller_id')->nullable();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->unsignedBigInteger('commission_rule_id')->nullable();
            $table->string('commission_type'); // PERSONAL_SALE, RESELLER_OVERRIDE, RANK_BONUS, PERFORMANCE_BONUS, CHANNEL_BONUS
            $table->decimal('base_amount', 18, 4);
            $table->decimal('rate', 8, 4);
            $table->decimal('amount', 18, 4);
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, PAID, CANCELLED, ON_HOLD
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['distributor_id', 'status']);
            $table->index(['reseller_id', 'status']);
            $table->index('order_id');
            $table->foreign('distributor_id')->references('id')->on('distributor_profiles')->nullOnDelete();
            $table->foreign('reseller_id')->references('id')->on('reseller_profiles')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('commission_rule_id')->references('id')->on('commission_rules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
