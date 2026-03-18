<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->enum('sync_type', ['manual', 'scheduled']);
            $table->integer('sections_updated')->default(0);
            $table->integer('sections_skipped')->default(0);
            $table->integer('sections_conflicted')->default(0);
            $table->json('details')->nullable();
            $table->enum('status', ['success', 'partial', 'failed']);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
