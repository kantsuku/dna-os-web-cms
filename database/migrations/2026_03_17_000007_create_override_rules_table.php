<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('override_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('policy', ['auto_sync', 'confirm_before_sync', 'manual_only', 'locked'])->default('auto_sync');
            $table->text('reason')->nullable();
            $table->foreignId('set_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('override_rules');
    }
};
