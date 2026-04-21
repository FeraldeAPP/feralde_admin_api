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
            if (!Schema::hasColumn('distributor_profiles', 'business_name')) {
                $table->string('business_name')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'business_type')) {
                $table->string('business_type')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'selected_city')) {
                $table->string('selected_city')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'tin_or_reg_no')) {
                $table->string('tin_or_reg_no')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'business_address')) {
                $table->text('business_address')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'contact_number')) {
                $table->string('contact_number')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'gender')) {
                $table->string('gender')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'region')) {
                $table->string('region')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'province')) {
                $table->string('province')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'barangay')) {
                $table->string('barangay')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'street_address')) {
                $table->string('street_address')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'zip_code')) {
                $table->string('zip_code')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'landmark')) {
                $table->string('landmark')->nullable();
            }
            
            // Additional fields found in DB but missing from migrations
            if (!Schema::hasColumn('distributor_profiles', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'verification_token')) {
                $table->string('verification_token')->nullable();
            }
            if (!Schema::hasColumn('distributor_profiles', 'is_email_verified')) {
                $table->boolean('is_email_verified')->default(false);
            }
            if (!Schema::hasColumn('distributor_profiles', 'is_approved')) {
                $table->boolean('is_approved')->default(false);
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
                'business_name',
                'business_type',
                'selected_city',
                'tin_or_reg_no',
                'business_address',
                'contact_number',
                'date_of_birth',
                'gender',
                'region',
                'province',
                'city',
                'barangay',
                'street_address',
                'zip_code',
                'landmark',
                'first_name',
                'last_name',
                'email',
                'verification_token',
                'is_email_verified',
                'is_approved',
            ]);
        });
    }
};
