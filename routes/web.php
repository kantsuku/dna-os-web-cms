<?php

use App\Http\Controllers\AuthController;
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
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// 認証
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', fn () => redirect('/dashboard'));

// 認証必須
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ════════════════════════════════════════
    // 戦略ダッシュボード (STR)
    // ════════════════════════════════════════
    Route::prefix('strategy')->name('strategy.')->group(function () {
        // 戦略タスク
        Route::get('/tasks', [StrategicTaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{strategicTask}', [StrategicTaskController::class, 'show'])->name('tasks.show');
        Route::post('/tasks/{strategicTask}/approve', [StrategicTaskController::class, 'approve'])->name('tasks.approve');
        Route::post('/tasks/{strategicTask}/reject', [StrategicTaskController::class, 'reject'])->name('tasks.reject');
        Route::post('/tasks/bulk-approve', [StrategicTaskController::class, 'bulkApprove'])->name('tasks.bulk-approve');

        // フリー入力修正依頼
        Route::get('/free-input', [FreeInputController::class, 'index'])->name('free-input.index');
        Route::post('/free-input', [FreeInputController::class, 'store'])->name('free-input.store');
        Route::get('/free-input/{freeInputRequest}', [FreeInputController::class, 'show'])->name('free-input.show');
        Route::post('/free-input/{freeInputRequest}/confirm', [FreeInputController::class, 'confirm'])->name('free-input.confirm');

        // DNA-OS更新差分
        Route::get('/dna-updates', [DnaOsUpdateController::class, 'index'])->name('dna-updates.index');
        Route::get('/dna-updates/{log}', [DnaOsUpdateController::class, 'show'])->name('dna-updates.show');

        // チャネル実行状況
        Route::get('/channel-status', [ChannelStatusController::class, 'index'])->name('channel-status.index');
    });

    // ════════════════════════════════════════
    // 承認 (APR)
    // ════════════════════════════════════════
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/history', [ApprovalController::class, 'history'])->name('approvals.history');

    // ════════════════════════════════════════
    // サイト管理 (SITE)
    // ════════════════════════════════════════
    Route::resource('sites', SiteController::class);
    Route::post('/sites/{site}/test-ftp', [SiteController::class, 'testFtp'])->name('sites.test-ftp');

    // ════════════════════════════════════════
    // ページ管理 (PAGE)
    // ════════════════════════════════════════
    Route::resource('sites.pages', PageController::class);
    Route::get('/sites/{site}/pages/{page}/preview', [PageController::class, 'preview'])->name('sites.pages.preview');

    // コンテンツ取り込み
    Route::get('/sites/{site}/pages/{page}/import', [PageController::class, 'importForm'])->name('sites.pages.import');
    Route::post('/sites/{site}/pages/{page}/import', [PageController::class, 'import']);

    // セクション管理
    Route::get('/sites/{site}/pages/{page}/sections', [PageController::class, 'sections'])->name('sites.pages.sections');
    Route::get('/sites/{site}/pages/{page}/sections/{sectionId}/edit', [PageController::class, 'editSection'])->name('sites.pages.sections.edit');
    Route::put('/sites/{site}/pages/{page}/sections/{sectionId}', [PageController::class, 'updateSection'])->name('sites.pages.sections.update');
    Route::post('/sites/{site}/pages/{page}/sections/{sectionId}/lock', [PageController::class, 'toggleLock'])->name('sites.pages.sections.lock');

    // 微細編集（v2互換）
    Route::get('/sites/{site}/pages/{page}/edit-content', [PageController::class, 'editContent'])->name('sites.pages.edit-content');
    Route::put('/sites/{site}/pages/{page}/update-content', [PageController::class, 'updateContent'])->name('sites.pages.update-content');

    // 世代管理
    Route::post('/sites/{site}/pages/{page}/generations/{generation}/ready', [PageController::class, 'markReady'])->name('sites.pages.generations.ready');
    Route::get('/sites/{site}/pages/{page}/compare', [PageController::class, 'compareGenerations'])->name('sites.pages.compare');

    // ════════════════════════════════════════
    // 公開管理 (PUB)
    // ════════════════════════════════════════
    Route::get('/sites/{site}/publish', [PublishController::class, 'index'])->name('sites.publish.index');
    Route::post('/sites/{site}/publish/deploy', [PublishController::class, 'deploy'])->name('sites.publish.deploy');
    Route::post('/sites/{site}/publish/{record}/rollback', [PublishController::class, 'rollback'])->name('sites.publish.rollback');

    // ════════════════════════════════════════
    // デザインシステム (DSN)
    // ════════════════════════════════════════
    Route::get('/design/tokens', [DesignController::class, 'tokens'])->name('design.tokens');
    Route::put('/design/tokens', [DesignController::class, 'updateTokens'])->name('design.tokens.update');
    Route::get('/design/components', [DesignController::class, 'components'])->name('design.components');
    Route::get('/design/components/{component}', [DesignController::class, 'componentShow'])->name('design.components.show');
    Route::get('/sites/{site}/design', [DesignController::class, 'siteDesign'])->name('design.site');
    Route::put('/sites/{site}/design', [DesignController::class, 'updateSiteDesign'])->name('design.site.update');
    Route::get('/sites/{site}/design/css', [DesignController::class, 'previewCss'])->name('design.site.css');
});
