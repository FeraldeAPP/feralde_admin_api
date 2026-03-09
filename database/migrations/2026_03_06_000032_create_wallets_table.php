<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id')->unique()->nullable();
            $table->unsignedBigInteger('reseller_id')->unique()->nullable();
            $table->decimal('balance', 18, 4)->default(0);
            $table->decimal('pending_balance', 18, 4)->default(0);
            $table->decimal('lifetime_earned', 18, 4)->default(0);
            $table->decimal('lifetime_withdrawn', 18, 4)->default(0);
            $table->timestamps();

            $table->foreign('distributor_id')->references('id')->on('distributor_profiles')->nullOnDelete();
            $table->foreign('reseller_id')->references('id')->on('reseller_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
