<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 医院（Clinic）テーブル ──
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_id', 50)->unique(); // DNA-OS上のclinic_id
            $table->string('name', 255);
            $table->string('gas_webapp_url', 500)->nullable(); // DNA-OS GAS WebApp URL
            $table->json('settings')->nullable(); // 医院固有設定
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->timestamps();
        });

        // ── 医院トンマナ（ClinicDesign）テーブル ──
        Schema::create('clinic_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('name', 255)->default('default');
            $table->json('tokens')->nullable(); // {color-main: #xxx, font-primary: ...}
            $table->json('tone_and_manner')->nullable(); // {brand_voice, temperature, persona, ...}
            $table->json('prohibited_terms')->nullable(); // ["無痛", "絶対に", ...]
            $table->json('recommended_terms')->nullable(); // ["丁寧", "安心", ...]
            $table->timestamps();
        });

        // ── 医院独自コンポーネント ──
        Schema::create('clinic_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('key', 100);
            $table->string('name', 255);
            $table->string('category', 50);
            $table->text('html_template')->nullable();
            $table->json('default_styles')->nullable();
            $table->text('preview_html')->nullable();
            $table->text('description')->nullable();
            $table->json('variants')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['clinic_id', 'key']);
        });

        // ── 医院ユーザー中間テーブル ──
        Schema::create('clinic_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['clinic_id', 'user_id']);
        });

        // ── sites テーブル拡張 ──
        Schema::table('sites', function (Blueprint $table) {
            // clinic_idをFK化（clinicsテーブルへ）
            $table->unsignedBigInteger('clinic_ref_id')->nullable()->after('id');
            $table->enum('site_type', ['hp', 'specialty', 'recruitment', 'lp', 'other'])
                ->default('hp')->after('clinic_id');
            $table->string('site_label', 255)->nullable()->after('site_type'); // "インプラント専門サイト"等

            $table->foreign('clinic_ref_id')->references('id')->on('clinics')->nullOnDelete();
        });

        // ── page_generations.sections 内の component_type 対応 ──
        // JSON内のフィールド追加なのでマイグレーション不要
        // ドキュメントとしてのみ記載:
        // sections[].component_type: 'hero_slider' | 'hero_static' | 'features_grid' | ...
        // sections[].config: {} (コンポーネント固有設定)

        // ── 戦略タスク等をclinic_ref_idでも紐づけ可能に ──
        Schema::table('strategic_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('clinic_ref_id')->nullable()->after('clinic_id');
            $table->foreign('clinic_ref_id')->references('id')->on('clinics')->nullOnDelete();
        });

        Schema::table('improvement_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('clinic_ref_id')->nullable()->after('clinic_id');
            $table->foreign('clinic_ref_id')->references('id')->on('clinics')->nullOnDelete();
        });

        Schema::table('free_input_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('clinic_ref_id')->nullable()->after('clinic_id');
            $table->foreign('clinic_ref_id')->references('id')->on('clinics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('free_input_requests', function (Blueprint $table) {
            $table->dropForeign(['clinic_ref_id']);
            $table->dropColumn('clinic_ref_id');
        });
        Schema::table('improvement_reports', function (Blueprint $table) {
            $table->dropForeign(['clinic_ref_id']);
            $table->dropColumn('clinic_ref_id');
        });
        Schema::table('strategic_tasks', function (Blueprint $table) {
            $table->dropForeign(['clinic_ref_id']);
            $table->dropColumn('clinic_ref_id');
        });
        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign(['clinic_ref_id']);
            $table->dropColumn(['clinic_ref_id', 'site_type', 'site_label']);
        });

        Schema::dropIfExists('clinic_user');
        Schema::dropIfExists('clinic_components');
        Schema::dropIfExists('clinic_designs');
        Schema::dropIfExists('clinics');
    }
};
