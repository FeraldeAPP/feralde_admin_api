<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique(); // cross-db reference to feralde_auth users
            $table->string('distributor_code')->unique();
            $table->string('rank')->default('STARTER'); // STARTER, BRONZE, SILVER, GOLD, PLATINUM, DIAMOND
            $table->string('referral_code')->unique();
            $table->unsignedBigInteger('parent_distributor_id')->nullable();
            $table->string('assigned_city')->nullable(); // the Philippine city this distributor is assigned to cover (unique: one distributor per city)
            $table->string('application_doc_url')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejected_by')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspended_by')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->decimal('total_network_sales', 18, 4)->default(0);
            $table->decimal('total_personal_sales', 18, 4)->default(0);
            $table->timestamp('rank_updated_at')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('e_wallet_gcash')->nullable();
            $table->string('e_wallet_maya')->nullable();
            $table->timestamps();

            $table->index('parent_distributor_id');
            $table->index('referral_code');
            $table->index('rank');
            $table->unique('assigned_city'); // one distributor per city
            $table->foreign('parent_distributor_id')->references('id')->on('distributor_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_profiles');
    }
};
