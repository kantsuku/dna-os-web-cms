<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── pages テーブル拡張 ──
        Schema::table('pages', function (Blueprint $table) {
            $table->enum('content_classification', ['standard', 'assisted', 'exception'])
                ->default('standard')->after('page_type');
            $table->string('template_key', 100)->default('generic')->after('content_classification');
            $table->json('meta')->nullable()->after('template_key');
            $table->string('dna_source_key', 100)->nullable()->after('meta');
            $table->boolean('is_published')->default(false)->after('sort_order');
        });

        // page_type に 'case' を追加（既存 'exception' との共存）
        // MySQL の ENUM 変更は直接 ALTER を使用
        \DB::statement("ALTER TABLE pages MODIFY COLUMN page_type ENUM('top','lower','blog','news','exception','case') DEFAULT 'lower'");

        // ── page_generations テーブル拡張 ──
        Schema::table('page_generations', function (Blueprint $table) {
            $table->json('sections')->nullable()->after('source_url');
            $table->string('source_task_id', 24)->nullable()->after('source_url');
            // status に 'approved' を追加
        });

        \DB::statement("ALTER TABLE page_generations MODIFY COLUMN source ENUM('ai_generated','manual','imported','partial_regen') DEFAULT 'ai_generated'");
        \DB::statement("ALTER TABLE page_generations MODIFY COLUMN status ENUM('draft','received','ready','approved','published','superseded','rolled_back') DEFAULT 'draft'");

        // ── exception_contents テーブル拡張 ──
        Schema::table('exception_contents', function (Blueprint $table) {
            $table->json('structured_data')->nullable()->after('content_html');
            $table->json('compliance_check')->nullable()->after('compliance_notes');
            $table->enum('visibility', ['private', 'limited', 'public'])->default('private')->after('status');
            $table->timestamp('publish_expires_at')->nullable()->after('visibility');
            $table->unsignedBigInteger('first_approved_by')->nullable()->after('publish_expires_at');
            $table->timestamp('first_approved_at')->nullable()->after('first_approved_by');
            $table->unsignedBigInteger('final_approved_by')->nullable()->after('first_approved_at');
            $table->timestamp('final_approved_at')->nullable()->after('final_approved_by');

            $table->foreign('first_approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('final_approved_by')->references('id')->on('users')->nullOnDelete();
        });

        \DB::statement("ALTER TABLE exception_contents MODIFY COLUMN content_type ENUM('case_study','medical_ad_gl','effect_claim','other','case','compliance_text') DEFAULT 'other'");
        \DB::statement("ALTER TABLE exception_contents MODIFY COLUMN status ENUM('draft','first_review','final_review','approved','published','rejected','archived') DEFAULT 'draft'");

        // ── 戦略タスク ──
        Schema::create('strategic_tasks', function (Blueprint $table) {
            $table->char('id', 20)->primary(); // ST-YYYYMMDD-NNN
            $table->string('clinic_id', 50)->index();
            $table->enum('trigger_type', [
                'dna_update', 'improvement', 'free_input', 'new_page', 'scheduled_check',
            ]);
            $table->string('trigger_source_id', 100)->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('intent')->nullable();
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->enum('risk_level', ['high', 'medium', 'low'])->default('medium');
            $table->json('target_channels'); // ["web"]
            $table->enum('status', [
                'draft', 'pending_approval', 'approved', 'in_progress', 'completed', 'cancelled',
            ])->default('draft');
            $table->string('created_by', 100); // 'ai_chief' or 'human:{user_id}'
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
            $table->index(['status', 'priority']);
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── チャネルタスク ──
        Schema::create('channel_tasks', function (Blueprint $table) {
            $table->char('id', 24)->primary(); // CT-WEB-YYYYMMDD-NNN
            $table->char('strategic_task_id', 20);
            $table->enum('channel', ['web', 'gbp', 'sns', 'line'])->default('web');
            $table->enum('task_type', [
                'update_content', 'update_meta', 'new_page', 'delete_page',
                'check_quality', 'fix_links', 'update_design',
                'blog_review', 'case_review', 'compliance_check',
            ]);
            $table->string('title', 255);
            $table->text('instruction')->nullable();
            $table->unsignedBigInteger('target_site_id')->nullable();
            $table->unsignedBigInteger('target_page_id')->nullable();
            $table->json('target_sections')->nullable();
            $table->json('input_data')->nullable();
            $table->enum('status', [
                'pending', 'in_progress', 'review_ready', 'approved',
                'deployed', 'completed', 'rejected', 'cancelled',
            ])->default('pending');
            $table->json('execution_log')->nullable();
            $table->json('result')->nullable();
            $table->string('assigned_to', 100)->default('ai');
            $table->timestamps();

            $table->foreign('strategic_task_id')->references('id')->on('strategic_tasks');
            $table->foreign('target_site_id')->references('id')->on('sites')->nullOnDelete();
            $table->foreign('target_page_id')->references('id')->on('pages')->nullOnDelete();
            $table->index(['strategic_task_id']);
            $table->index(['target_site_id', 'status']);
            $table->index(['target_page_id', 'status']);
        });

        // ── 改善レポート ──
        Schema::create('improvement_reports', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_id', 50)->index();
            $table->unsignedBigInteger('site_id');
            $table->enum('report_type', [
                'seo', 'content_quality', 'performance', 'compliance', 'comprehensive',
            ]);
            $table->string('title', 255);
            $table->text('summary')->nullable();
            $table->json('findings')->nullable();
            $table->string('generated_by', 100)->nullable();
            $table->enum('status', ['draft', 'reviewed', 'actioned', 'archived'])->default('draft');
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('sites');
        });

        // ── フリー入力修正依頼 ──
        Schema::create('free_input_requests', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_id', 50)->index();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->text('raw_text');
            $table->json('ai_interpretation')->nullable();
            $table->enum('interpretation_status', [
                'pending', 'interpreted', 'confirmed', 'rejected',
            ])->default('pending');
            $table->char('strategic_task_id', 20)->nullable();
            $table->unsignedBigInteger('submitted_by');
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
            $table->foreign('submitted_by')->references('id')->on('users');
            $table->foreign('strategic_task_id')->references('id')->on('strategic_tasks')->nullOnDelete();
        });

        // ── 承認記録 ──
        Schema::create('approval_records', function (Blueprint $table) {
            $table->id();
            $table->string('approvable_type', 100); // 'strategic_task' / 'channel_task' / 'exception_content'
            $table->string('approvable_id', 100);
            $table->enum('approval_type', ['approve', 'reject', 'send_back']);
            $table->enum('approval_level', ['standard', 'first_review', 'final_review'])->default('standard');
            $table->unsignedBigInteger('approved_by');
            $table->text('comment')->nullable();
            $table->json('diff_snapshot')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['approvable_type', 'approvable_id']);
            $table->foreign('approved_by')->references('id')->on('users');
        });

        // ── オーケストレーションログ ──
        Schema::create('orchestration_logs', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_id', 50);
            $table->enum('event_type', [
                'dna_change_detected', 'task_generated', 'task_converted',
                'report_generated', 'interpretation_completed',
                'approval_requested', 'deployment_triggered',
            ]);
            $table->string('source_type', 100)->nullable();
            $table->string('source_id', 100)->nullable();
            $table->json('detail')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['clinic_id', 'event_type']);
            $table->index(['created_at']);
        });

        // ── 生成コンテンツ元記録 ──
        Schema::create('generated_content_sources', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_id', 50)->index();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->enum('source_type', ['google_docs', 'markup_txt', 'gas_api']);
            $table->string('source_url', 500)->nullable();
            $table->json('source_meta')->nullable();
            $table->longText('fetched_html')->nullable();
            $table->timestamp('fetched_at');
            $table->unsignedBigInteger('page_generation_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('page_id')->references('id')->on('pages')->nullOnDelete();
            $table->foreign('page_generation_id')->references('id')->on('page_generations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_content_sources');
        Schema::dropIfExists('orchestration_logs');
        Schema::dropIfExists('approval_records');
        Schema::dropIfExists('free_input_requests');
        Schema::dropIfExists('improvement_reports');
        Schema::dropIfExists('channel_tasks');
        Schema::dropIfExists('strategic_tasks');

        // exception_contents 拡張カラム削除
        Schema::table('exception_contents', function (Blueprint $table) {
            $table->dropForeign(['first_approved_by']);
            $table->dropForeign(['final_approved_by']);
            $table->dropColumn([
                'structured_data', 'compliance_check', 'visibility',
                'publish_expires_at', 'first_approved_by', 'first_approved_at',
                'final_approved_by', 'final_approved_at',
            ]);
        });

        // page_generations 拡張カラム削除
        Schema::table('page_generations', function (Blueprint $table) {
            $table->dropColumn(['sections', 'source_task_id']);
        });

        // pages 拡張カラム削除
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'content_classification', 'template_key', 'meta',
                'dna_source_key', 'is_published',
            ]);
        });
    }
};
