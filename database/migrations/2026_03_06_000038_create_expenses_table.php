<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // COGS, SHIPPING, MARKETING, SALARIES, PLATFORM_FEES, COMMISSION_PAYOUT, UTILITIES, REFUNDS, PACKAGING, MISCELLANEOUS
            $table->text('description');
            $table->decimal('amount', 18, 4);
            $table->timestamp('expense_date');
            $table->string('reference_id')->nullable();
            $table->string('receipt_url')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index(['category', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
