<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_rank_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            $table->string('previous_rank');
            $table->string('new_rank');
            $table->timestamp('changed_at')->useCurrent();
            $table->string('changed_by')->nullable();
            $table->text('reason')->nullable();

            $table->index('distributor_id');
            $table->foreign('distributor_id')->references('id')->on('distributor_profiles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_rank_history');
    }
};
