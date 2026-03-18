<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publish_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->json('pages_json');
            $table->string('snapshot_path', 500)->nullable();
            $table->enum('deploy_status', ['pending', 'building', 'deploying', 'success', 'failed', 'rolled_back'])->default('pending');
            $table->foreignId('deployed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deployed_at')->nullable();
            $table->foreignId('rollback_of')->nullable()->constrained('publish_records')->nullOnDelete();
            $table->text('error_log')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'deploy_status']);
            $table->index('deployed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_records');
    }
};
