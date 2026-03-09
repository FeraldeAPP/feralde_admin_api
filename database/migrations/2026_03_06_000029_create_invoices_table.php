<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('invoice_number')->unique();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('due_at')->nullable();
            $table->decimal('subtotal', 18, 4);
            $table->decimal('tax', 18, 4);
            $table->decimal('total', 18, 4);
            $table->string('pdf_url')->nullable();
            $table->text('notes')->nullable();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
