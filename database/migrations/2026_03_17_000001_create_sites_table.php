<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_id', 50)->index();
            $table->string('name', 255);
            $table->string('domain', 255)->nullable();
            $table->string('xserver_host', 255)->nullable();
            $table->string('xserver_ftp_user', 255)->nullable();
            $table->text('xserver_ftp_pass')->nullable();
            $table->string('xserver_deploy_path', 500)->default('/public_html');
            $table->string('template_set', 100)->default('default');
            $table->enum('status', ['active', 'maintenance', 'archived'])->default('active')->index();
            $table->string('wp_site_url', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
