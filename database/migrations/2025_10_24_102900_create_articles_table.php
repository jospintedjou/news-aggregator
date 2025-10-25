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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('author')->nullable();
            $table->string('source');
            $table->string('category')->nullable();
            $table->string('url');
            $table->string('image_url')->nullable();
            $table->string('external_id');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('source');
            $table->index('category');
            $table->index('published_at');
            $table->unique(['source', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
