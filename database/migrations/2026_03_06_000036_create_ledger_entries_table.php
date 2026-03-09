<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('period_id');
            $table->timestamp('entry_date');
            $table->string('entry_type');
            $table->string('category')->nullable(); // COGS, SHIPPING, MARKETING, SALARIES, PLATFORM_FEES, COMMISSION_PAYOUT, UTILITIES, REFUNDS, PACKAGING, MISCELLANEOUS
            $table->string('channel_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->text('description');
            $table->decimal('amount', 18, 4);
            $table->decimal('debit', 18, 4)->default(0);
            $table->decimal('credit', 18, 4)->default(0);
            $table->boolean('is_reconciled')->default(false);
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('period_id');
            $table->index('entry_date');
            $table->index(['reference_type', 'reference_id']);
            $table->foreign('period_id')->references('id')->on('accounting_periods')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
