<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 255);
            $table->string('title', 500);
            $table->enum('page_type', ['top', 'lower', 'blog', 'news', 'exception'])->default('lower');
            $table->string('template_name', 255)->default('default_lower');
            $table->text('meta_description')->nullable();
            $table->string('og_image_path', 500)->nullable();
            $table->enum('status', ['draft', 'pending_review', 'approved', 'published', 'archived'])->default('draft')->index();
            $table->unsignedInteger('publish_version')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
            $table->index('page_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
