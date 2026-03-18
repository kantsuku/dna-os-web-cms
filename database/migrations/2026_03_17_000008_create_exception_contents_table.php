<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exception_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->enum('content_type', ['case_study', 'medical_ad_gl', 'effect_claim', 'other']);
            $table->string('title', 500);
            $table->longText('content_html');
            $table->enum('risk_level', ['high', 'critical'])->default('high');
            $table->text('compliance_notes')->nullable();
            $table->boolean('requires_specialist_review')->default(true);
            $table->enum('status', ['draft', 'under_review', 'approved', 'published', 'suspended'])->default('draft')->index();
            $table->foreignId('linked_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'content_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exception_contents');
    }
};
