<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('replicated_store_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id')->unique()->nullable();
            $table->unsignedBigInteger('reseller_id')->unique()->nullable();
            $table->string('store_slug')->unique();
            $table->string('store_name')->nullable();
            $table->string('banner_url')->nullable();
            $table->text('welcome_message')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('distributor_id')->references('id')->on('distributor_profiles')->nullOnDelete();
            $table->foreign('reseller_id')->references('id')->on('reseller_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('replicated_store_settings');
    }
};
