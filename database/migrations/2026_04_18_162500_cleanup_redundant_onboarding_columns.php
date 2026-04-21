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
            // Drop redundant onboarding columns in favor of original registration fields
            $table->dropColumn([
                'selected_package_id',
                'onboarding_city'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_profiles', function (Blueprint $table) {
            $table->string('selected_package_id')->nullable();
            $table->string('onboarding_city', 150)->nullable();
        });
    }
};
