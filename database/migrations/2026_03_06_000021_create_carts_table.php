<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable(); // cross-db reference to feralde_auth users
            $table->string('session_id')->nullable();
            $table->string('distributor_ref')->nullable(); // distributor referral code
            $table->string('reseller_ref')->nullable();   // reseller referral code
            $table->string('promo_code')->nullable();
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
