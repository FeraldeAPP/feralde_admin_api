<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable(); // cross-db reference to feralde_auth users
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('region');
            $table->string('province');
            $table->string('city');
            $table->string('barangay');
            $table->text('details');
            $table->string('postal_code')->nullable();
            $table->string('country')->default('PH');
            $table->boolean('is_default')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
