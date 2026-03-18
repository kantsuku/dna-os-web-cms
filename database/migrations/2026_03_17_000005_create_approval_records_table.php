<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('content_variants')->cascadeOnDelete();
            $table->enum('action', ['approve', 'reject', 'send_back']);
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_records');
    }
};
