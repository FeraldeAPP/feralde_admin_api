<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('commission_type'); // PERSONAL_SALE, RESELLER_OVERRIDE, RANK_BONUS, PERFORMANCE_BONUS, CHANNEL_BONUS
            $table->string('applicable_rank')->nullable(); // STARTER, BRONZE, SILVER, GOLD, PLATINUM, DIAMOND
            $table->decimal('personal_sale_rate', 8, 4)->nullable();
            $table->decimal('reseller_override_rate', 8, 4)->nullable();
            $table->decimal('min_sales_volume', 18, 4)->nullable();
            $table->decimal('max_sales_volume', 18, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('effective_from');
            $table->timestamp('effective_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
