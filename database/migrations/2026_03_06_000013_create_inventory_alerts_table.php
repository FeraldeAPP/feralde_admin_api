<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id');
            $table->string('type'); // LOW_STOCK, OUT_OF_STOCK, OVERSTOCK, EXPIRY_WARNING
            $table->integer('threshold')->nullable();
            $table->integer('current_level');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('is_resolved');
            $table->foreign('inventory_id')->references('id')->on('inventory')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_alerts');
    }
};
