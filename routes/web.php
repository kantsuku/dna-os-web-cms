<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublishController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\Strategy\StrategicTaskController;
use App\Http\Controllers\Strategy\FreeInputController;
use App\Http\Controllers\Strategy\DnaOsUpdateController;
use App\Http\Controllers\Strategy\ChannelStatusController;
use App\Http\Controllers\Shared\ApprovalController;
use App\Http\Controllers\Web\ExceptionContentController;
use App\Http\Controllers\Web\MediaController;
use App\Http\Controllers\Web\SitePartsController;
use Illuminate\Support\Facades\Route;

// 認証
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', fn () => redirect('/clinics'));

// プレビュー系（iframe埋め込み用、auth不要）
Route::prefix('clinics/{clinic}')->middleware(\App\Http\Middleware\InjectClinic::class)->group(function () {
    Route::get('/sites/{site}/pages/{page}/content-frame', [PageController::class, 'contentFrame'])->name('public.content-frame');
    Route::get('/sites/{site}/pages/{page}/section-frame/{sectionId}', [PageController::class, 'sectionFrame'])->name('public.section-frame');
    Route::get('/design/components/{component}/preview-frame', [DesignController::class, 'componentPreviewFrame'])->name('public.component-preview');
    Route::get('/sites/{site}/parts/preview-header', [SitePartsController::class, 'previewHeader'])->name('clinic.sites.parts.preview-header');
});

// 認証必須
Route::middleware('auth')->group(function () {

    // ════════════════════════════════════════
    // 医院選択 + 医院管理
    // ════════════════════════════════════════
    Route::get('/clinics', [ClinicController::class, 'select'])->name('clinics.select');
    Route::get('/clinics/create', [ClinicController::class, 'create'])->name('clinics.create');
    Route::post('/clinics', [ClinicController::class, 'store'])->name('clinics.store');

    // ════════════════════════════════════════
    // 医院コンテキスト内（全てclinicスコープ）
    // ════════════════════════════════════════
    Route::prefix('clinics/{clinic}')->name('clinic.')
        ->middleware(\App\Http\Middleware\InjectClinic::class)
        ->group(function () {

        // 医院ダッシュボード（作戦本部）
        Route::get('/', [ClinicController::class, 'dashboard'])->name('dashboard');

        // ── 戦略 (STR) ──
        Route::prefix('strategy')->name('strategy.')->group(function () {
            Route::get('/tasks', [StrategicTaskController::class, 'index'])->name('tasks.index');
            Route::get('/tasks/{strategicTask}', [StrategicTaskController::class, 'show'])->name('tasks.show');
            Route::post('/tasks/{strategicTask}/approve', [StrategicTaskController::class, 'approve'])->name('tasks.approve');
            Route::post('/tasks/{strategicTask}/reject', [StrategicTaskController::class, 'reject'])->name('tasks.reject');
            Route::post('/tasks/bulk-approve', [StrategicTaskController::class, 'bulkApprove'])->name('tasks.bulk-approve');

            Route::get('/free-input', [FreeInputController::class, 'index'])->name('free-input.index');
            Route::post('/free-input', [FreeInputController::class, 'store'])->name('free-input.store');
            Route::get('/free-input/{freeInputRequest}', [FreeInputController::class, 'show'])->name('free-input.show');
            Route::post('/free-input/{freeInputRequest}/confirm', [FreeInputController::class, 'confirm'])->name('free-input.confirm');
            Route::post('/free-input/{freeInputRequest}/reject', [FreeInputController::class, 'reject'])->name('free-input.reject');

            Route::get('/dna-updates', [DnaOsUpdateController::class, 'index'])->name('dna-updates.index');
            Route::post('/dna-updates/sync', [DnaOsUpdateController::class, 'sync'])->name('dna-updates.sync');

            Route::get('/channel-status', [ChannelStatusController::class, 'index'])->name('channel-status.index');
        });

        // ── 承認 (APR) ──
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/history', [ApprovalController::class, 'history'])->name('approvals.history');

        // ── サイト管理 (SITE) ──
        Route::resource('sites', SiteController::class);
        Route::post('/sites/{site}/test-ftp', [SiteController::class, 'testFtp'])->name('sites.test-ftp');

        // ── ページ管理 (PAGE) ──
        Route::resource('sites.pages', PageController::class);
        Route::get('/sites/{site}/pages/{page}/preview', [PageController::class, 'preview'])->name('sites.pages.preview');
        // content-frame, section-frame は public ルートに移動済み

        // コンテンツ取り込み
        Route::get('/sites/{site}/pages/{page}/import', [PageController::class, 'importForm'])->name('sites.pages.import');
        Route::post('/sites/{site}/pages/{page}/import', [PageController::class, 'import']);

        // セクション管理
        Route::get('/sites/{site}/pages/{page}/sections', [PageController::class, 'sections'])->name('sites.pages.sections');
        Route::get('/sites/{site}/pages/{page}/sections/{sectionId}/edit', [PageController::class, 'editSection'])->name('sites.pages.sections.edit');
        Route::put('/sites/{site}/pages/{page}/sections/{sectionId}', [PageController::class, 'updateSection'])->name('sites.pages.sections.update');
        Route::post('/sites/{site}/pages/{page}/sections/{sectionId}/lock', [PageController::class, 'toggleLock'])->name('sites.pages.sections.lock');
        Route::post('/sites/{site}/pages/{page}/sections/add', [PageController::class, 'addSection'])->name('sites.pages.sections.add');
        Route::delete('/sites/{site}/pages/{page}/sections/{sectionId}', [PageController::class, 'deleteSection'])->name('sites.pages.sections.delete');
        Route::post('/sites/{site}/pages/{page}/sections/reorder', [PageController::class, 'reorderSections'])->name('sites.pages.sections.reorder');

        // 微細編集（v2互換）
        Route::get('/sites/{site}/pages/{page}/edit-content', [PageController::class, 'editContent'])->name('sites.pages.edit-content');
        Route::put('/sites/{site}/pages/{page}/update-content', [PageController::class, 'updateContent'])->name('sites.pages.update-content');

        // 世代管理
        Route::post('/sites/{site}/pages/{page}/generations/{generation}/ready', [PageController::class, 'markReady'])->name('sites.pages.generations.ready');
        Route::get('/sites/{site}/pages/{page}/compare', [PageController::class, 'compareGenerations'])->name('sites.pages.compare');

        // ── 共通パーツ (PARTS) ──
        Route::get('/sites/{site}/parts', [SitePartsController::class, 'edit'])->name('sites.parts.edit');
        Route::put('/sites/{site}/parts', [SitePartsController::class, 'update'])->name('sites.parts.update');

        // ── メディア管理 (MEDIA) ──
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
        Route::post('/media/folder', [MediaController::class, 'createFolder'])->name('media.folder');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::get('/media/picker', [MediaController::class, 'picker'])->name('media.picker');

        // ── 例外コンテンツ (EXC) ──
        Route::get('/sites/{site}/exceptions', [ExceptionContentController::class, 'index'])->name('sites.exceptions.index');
        Route::get('/sites/{site}/exceptions/create', [ExceptionContentController::class, 'create'])->name('sites.exceptions.create');
        Route::post('/sites/{site}/exceptions', [ExceptionContentController::class, 'store'])->name('sites.exceptions.store');
        Route::get('/sites/{site}/exceptions/{exception}', [ExceptionContentController::class, 'show'])->name('sites.exceptions.show');
        Route::get('/sites/{site}/exceptions/{exception}/edit', [ExceptionContentController::class, 'edit'])->name('sites.exceptions.edit');
        Route::put('/sites/{site}/exceptions/{exception}', [ExceptionContentController::class, 'update'])->name('sites.exceptions.update');
        Route::post('/sites/{site}/exceptions/{exception}/submit-review', [ExceptionContentController::class, 'submitForReview'])->name('sites.exceptions.submit-review');
        Route::post('/sites/{site}/exceptions/{exception}/first-approve', [ExceptionContentController::class, 'firstApprove'])->name('sites.exceptions.first-approve');
        Route::post('/sites/{site}/exceptions/{exception}/final-approve', [ExceptionContentController::class, 'finalApprove'])->name('sites.exceptions.final-approve');
        Route::post('/sites/{site}/exceptions/{exception}/reject', [ExceptionContentController::class, 'reject'])->name('sites.exceptions.reject');

        // ── 公開管理 (PUB) ──
        Route::get('/sites/{site}/publish', [PublishController::class, 'index'])->name('sites.publish.index');
        Route::post('/sites/{site}/publish/deploy', [PublishController::class, 'deploy'])->name('sites.publish.deploy');
        Route::post('/sites/{site}/publish/{record}/rollback', [PublishController::class, 'rollback'])->name('sites.publish.rollback');

        // ── デザインシステム (DSN) ──
        Route::get('/design/tokens', [DesignController::class, 'tokens'])->name('design.tokens');
        Route::put('/design/tokens', [DesignController::class, 'updateTokens'])->name('design.tokens.update');
        Route::get('/design/components', [DesignController::class, 'components'])->name('design.components');
        Route::get('/design/components/{component}', [DesignController::class, 'componentShow'])->name('design.components.show');
        // component preview-frame は public ルートに移動済み
        Route::get('/design/components/{component}/edit', [DesignController::class, 'componentEdit'])->name('design.components.edit');
        Route::put('/design/components/{component}', [DesignController::class, 'componentUpdate'])->name('design.components.update');
        Route::get('/sites/{site}/design', [DesignController::class, 'siteDesign'])->name('design.site');
        Route::put('/sites/{site}/design', [DesignController::class, 'updateSiteDesign'])->name('design.site.update');
        Route::get('/sites/{site}/design/css', [DesignController::class, 'previewCss'])->name('design.site.css');
    });
});
