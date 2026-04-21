<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('distributor_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('distributor_profiles', 'payment_confirmed_at')) {
                $table->timestamp('payment_confirmed_at')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'selected_package_id')) {
                $table->string('selected_package_id')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'facebook_url')) {
                $table->string('facebook_url', 400)->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'tiktok_username')) {
                $table->string('tiktok_username', 100)->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'onboarding_city')) {
                $table->string('onboarding_city', 150)->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'onboarding_completed_at')) {
                $table->timestamp('onboarding_completed_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'payment_confirmed_at',
                'payment_proof_path',
                'selected_package_id',
                'facebook_url',
                'tiktok_username',
                'onboarding_city',
                'onboarding_completed_at',
            ]);
        });
    }
};
