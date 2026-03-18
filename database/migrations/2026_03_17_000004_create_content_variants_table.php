<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->enum('source_type', ['dna_os_sync', 'human_edit', 'ai_regenerated']);
            $table->longText('content_html');
            $table->longText('content_raw')->nullable();
            $table->longText('original_content')->nullable();
            $table->json('diff_from_original')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('edit_reason')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'approved', 'published', 'superseded'])->default('draft')->index();
            $table->timestamps();

            $table->unique(['section_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_variants');
    }
};
