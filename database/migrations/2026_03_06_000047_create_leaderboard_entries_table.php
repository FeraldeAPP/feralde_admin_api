<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->string('period'); // e.g. "2026-03"
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->unsignedBigInteger('reseller_id')->nullable();
            $table->decimal('total_sales', 18, 4);
            $table->integer('total_orders');
            $table->integer('rank');
            $table->string('badge')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['period', 'distributor_id']);
            $table->unique(['period', 'reseller_id']);
            $table->index(['period', 'rank']);
            $table->foreign('distributor_id')->references('id')->on('distributor_profiles')->nullOnDelete();
            $table->foreign('reseller_id')->references('id')->on('reseller_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
    }
};
