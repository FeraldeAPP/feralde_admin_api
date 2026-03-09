<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->string('title');
            $table->string('type'); // VIDEO, ARTICLE, QUIZ, PDF, CERTIFICATION
            $table->string('content_url')->nullable();
            $table->longText('body')->nullable();
            $table->integer('duration_min')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('training_modules')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_contents');
    }
};
