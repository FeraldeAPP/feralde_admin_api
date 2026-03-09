<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_assets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // IMAGE, VIDEO, DOCUMENT, PDF
            $table->string('url');
            $table->string('thumbnail_url')->nullable();
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('uploaded_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_assets');
    }
};
