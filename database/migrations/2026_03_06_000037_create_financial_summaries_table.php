<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('period_id');
            $table->string('channel_type')->nullable(); // WEBSITE, TIKTOK_SHOP, LAZADA, SHOPEE, MANUAL
            $table->decimal('gross_revenue', 18, 4)->default(0);
            $table->decimal('net_revenue', 18, 4)->default(0);
            $table->decimal('total_cogs', 18, 4)->default(0);
            $table->decimal('gross_profit', 18, 4)->default(0);
            $table->decimal('total_expenses', 18, 4)->default(0);
            $table->decimal('total_commissions', 18, 4)->default(0);
            $table->decimal('total_refunds', 18, 4)->default(0);
            $table->decimal('total_shipping_fees', 18, 4)->default(0);
            $table->decimal('net_income', 18, 4)->default(0);
            $table->integer('total_orders')->default(0);
            $table->timestamp('generated_at')->useCurrent();

            $table->unique(['period_id', 'channel_type']);
            $table->foreign('period_id')->references('id')->on('accounting_periods')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_summaries');
    }
};
