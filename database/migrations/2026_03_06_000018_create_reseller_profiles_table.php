<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable()->unique(); // cross-db reference to feralde_auth users; null for self-registered applicants
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->unique(); // contact email; used as identifier for self-registered resellers
            $table->string('phone')->nullable();
            $table->string('reseller_code')->unique();
            $table->string('referral_code')->unique();
            $table->unsignedBigInteger('parent_distributor_id')->nullable(); // null = city-based or direct ordering
            $table->string('city')->nullable(); // the city the reseller is based in (no geographic restriction)
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->decimal('total_sales', 18, 4)->default(0);
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('e_wallet_gcash')->nullable();
            $table->string('e_wallet_maya')->nullable();
            $table->timestamps();

            $table->index('parent_distributor_id');
            $table->index('city');
            $table->index('email');
            $table->foreign('parent_distributor_id')->references('id')->on('distributor_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_profiles');
    }
};
