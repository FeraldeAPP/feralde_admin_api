<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_id')->nullable(); // cross-db reference to feralde_auth users
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->unsignedBigInteger('reseller_id')->nullable();
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->string('external_order_id')->nullable();
            $table->string('source'); // WEBSITE, DISTRIBUTOR, RESELLER, TIKTOK_SHOP, LAZADA, MANUAL
            $table->string('status')->default('PENDING'); // PENDING, CONFIRMED, PROCESSING, PACKED, SHIPPED, OUT_FOR_DELIVERY, DELIVERED, CANCELLED, REFUNDED, FAILED
            $table->string('payment_status')->default('PENDING'); // PENDING, PAID, FAILED, REFUNDED, PARTIALLY_REFUNDED
            $table->string('payment_method')->nullable(); // GCASH, MAYA, CREDIT_CARD, DEBIT_CARD, CASH_ON_DELIVERY, BANK_TRANSFER, WALLET
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->unsignedBigInteger('billing_address_id')->nullable();
            $table->decimal('subtotal', 18, 4);
            $table->decimal('shipping_fee', 18, 4)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4);
            $table->string('pricing_tier')->default('RETAIL'); // RETAIL, DISTRIBUTOR, RESELLER, WHOLESALE
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('processed_by')->nullable(); // user_id from feralde_auth
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index('order_number');
            $table->index('customer_id');
            $table->index('distributor_id');
            $table->index(['source', 'status']);
            $table->index('created_at');
            $table->foreign('distributor_id')->references('id')->on('distributor_profiles')->nullOnDelete();
            $table->foreign('reseller_id')->references('id')->on('reseller_profiles')->nullOnDelete();
            $table->foreign('channel_id')->references('id')->on('sales_channels')->nullOnDelete();
            $table->foreign('shipping_address_id')->references('id')->on('addresses')->nullOnDelete();
            $table->foreign('billing_address_id')->references('id')->on('addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
