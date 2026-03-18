<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('section_key', 100);
            $table->integer('sort_order')->default(0);
            $table->enum('content_source_type', ['dna_os', 'manual', 'exception', 'client_post'])->default('dna_os');
            $table->json('content_source_ref')->nullable();
            $table->boolean('is_human_edited')->default(false);
            $table->timestamps();

            $table->unique(['page_id', 'section_key']);
            $table->index('content_source_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
