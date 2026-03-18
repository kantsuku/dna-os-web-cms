<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users に role 追加
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'editor', 'client'])->default('editor')->after('email');
        });

        // デザイントークン（グローバル）
        Schema::create('design_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50); // color, font, spacing, radius, shadow
            $table->string('key', 100);
            $table->string('value', 255);
            $table->string('label', 255);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category', 'key']);
        });

        // コンポーネント定義
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique(); // 現行キー (com-h2 等)
            $table->string('migration_key', 100)->nullable(); // 将来の移行先キー (acms-h2 等)
            $table->string('name', 255);
            $table->string('category', 50); // heading, layout, content, cta, utility
            $table->text('html_template')->nullable();
            $table->json('default_styles')->nullable();
            $table->text('preview_html')->nullable();
            $table->text('description')->nullable();
            $table->json('variants')->nullable(); // バリエーション (_white, _lg 等)
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // サイト（先に作成）
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_id', 50)->index();
            $table->string('name', 255);
            $table->string('domain', 255)->nullable();
            $table->string('xserver_host', 255)->nullable();
            $table->string('xserver_ftp_user', 255)->nullable();
            $table->text('xserver_ftp_pass')->nullable();
            $table->string('xserver_deploy_path', 500)->default('/public_html');
            $table->string('gas_generator_url', 500)->nullable();
            $table->unsignedBigInteger('design_id')->nullable();
            $table->enum('status', ['active', 'maintenance', 'archived'])->default('active')->index();
            $table->timestamps();
        });

        // サイトデザイン
        Schema::create('site_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255)->default('default');
            $table->json('tokens')->nullable();
            $table->json('component_styles')->nullable();
            $table->json('layout_config')->nullable();
            $table->longText('custom_css')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('active');
            $table->timestamps();
        });

        // sites.design_id FK を後から追加（循環参照回避）
        Schema::table('sites', function (Blueprint $table) {
            $table->foreign('design_id')->references('id')->on('site_designs')->nullOnDelete();
        });

        // サイトユーザー中間テーブル
        Schema::create('site_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['site_id', 'user_id']);
        });

        // ページ
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 255);
            $table->string('title', 500);
            $table->enum('page_type', ['top', 'lower', 'blog', 'news', 'exception'])->default('lower');
            $table->string('treatment_key', 100)->nullable();
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('current_generation_id')->nullable();
            $table->enum('status', ['draft', 'ready', 'published', 'archived'])->default('draft')->index();
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
        });

        // ページ世代
        Schema::create('page_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('generation');
            $table->enum('source', ['ai_generated', 'manual', 'imported'])->default('ai_generated');
            $table->string('source_url', 500)->nullable();
            $table->longText('content_html');
            $table->longText('content_text')->nullable();
            $table->json('meta_json')->nullable();
            $table->json('human_patch')->nullable();
            $table->text('patch_reason')->nullable();
            $table->foreignId('patched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('patched_at')->nullable();
            $table->longText('final_html');
            $table->enum('status', ['received', 'ready', 'published', 'superseded', 'rolled_back'])->default('received')->index();
            $table->timestamps();

            $table->unique(['page_id', 'generation']);
        });

        // pages.current_generation_id の FK を追加
        Schema::table('pages', function (Blueprint $table) {
            $table->foreign('current_generation_id')->references('id')->on('page_generations')->nullOnDelete();
        });

        // デプロイ記録
        Schema::create('deploy_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->json('generation_snapshot');
            $table->string('build_path', 500)->nullable();
            $table->enum('deploy_status', ['pending', 'building', 'deploying', 'success', 'failed', 'rolled_back'])->default('pending');
            $table->foreignId('deployed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deployed_at')->nullable();
            $table->foreignId('rollback_of')->nullable()->constrained('deploy_records')->nullOnDelete();
            $table->text('error_log')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'deploy_status']);
        });

        // 例外コンテンツ
        Schema::create('exception_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->enum('content_type', ['case_study', 'medical_ad_gl', 'effect_claim', 'other']);
            $table->string('title', 500);
            $table->longText('content_html');
            $table->longText('ai_enhanced_html')->nullable();
            $table->boolean('use_ai_version')->default(false);
            $table->text('compliance_notes')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['current_generation_id']);
        });
        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign(['design_id']);
        });

        Schema::dropIfExists('exception_contents');
        Schema::dropIfExists('deploy_records');
        Schema::dropIfExists('page_generations');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('site_user');
        Schema::dropIfExists('site_designs');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('components');
        Schema::dropIfExists('design_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
