<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->string('user_id'); // cross-db reference to feralde_auth users
            $table->timestamp('completed_at')->useCurrent();
            $table->integer('score')->nullable();
            $table->boolean('certified')->default(false);

            $table->unique(['module_id', 'user_id']);
            $table->foreign('module_id')->references('id')->on('training_modules')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_completions');
    }
};
