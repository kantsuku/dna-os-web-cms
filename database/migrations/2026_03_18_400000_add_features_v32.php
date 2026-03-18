<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // コンポーネントにカスタムCSS追加
        Schema::table('components', function (Blueprint $table) {
            $table->longText('custom_css')->nullable()->after('preview_html');
        });

        // サイト共通パーツ設定
        Schema::table('sites', function (Blueprint $table) {
            $table->json('header_config')->nullable()->after('status');
            $table->json('footer_config')->nullable()->after('header_config');
        });

        // メディアファイル
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 255);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('media_folders')->nullOnDelete();
        });

        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('filename', 500);
            $table->string('original_name', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 500)->nullable();
            $table->string('disk', 50)->default('public'); // public / s3
            $table->string('path', 1000); // storage path
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->cascadeOnDelete();
            $table->foreign('folder_id')->references('id')->on('media_folders')->nullOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['clinic_id', 'folder_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('media_folders');
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['header_config', 'footer_config']);
        });
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn('custom_css');
        });
    }
};
