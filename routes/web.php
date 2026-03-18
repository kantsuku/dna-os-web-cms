<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublishController;
use App\Http\Controllers\SiteController;
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

    // サイト管理
    Route::resource('sites', SiteController::class);
    Route::post('/sites/{site}/test-ftp', [SiteController::class, 'testFtp'])->name('sites.test-ftp');

    // ページ管理
    Route::resource('sites.pages', PageController::class);
    Route::get('/sites/{site}/pages/{page}/preview', [PageController::class, 'preview'])->name('sites.pages.preview');

    // コンテンツ取り込み
    Route::get('/sites/{site}/pages/{page}/import', [PageController::class, 'importForm'])->name('sites.pages.import');
    Route::post('/sites/{site}/pages/{page}/import', [PageController::class, 'import']);

    // 微細編集
    Route::get('/sites/{site}/pages/{page}/edit-content', [PageController::class, 'editContent'])->name('sites.pages.edit-content');
    Route::put('/sites/{site}/pages/{page}/update-content', [PageController::class, 'updateContent'])->name('sites.pages.update-content');

    // 世代管理
    Route::post('/sites/{site}/pages/{page}/generations/{generation}/ready', [PageController::class, 'markReady'])->name('sites.pages.generations.ready');
    Route::get('/sites/{site}/pages/{page}/compare', [PageController::class, 'compareGenerations'])->name('sites.pages.compare');

    // 公開管理
    Route::get('/sites/{site}/publish', [PublishController::class, 'index'])->name('sites.publish.index');
    Route::post('/sites/{site}/publish/deploy', [PublishController::class, 'deploy'])->name('sites.publish.deploy');
    Route::post('/sites/{site}/publish/{record}/rollback', [PublishController::class, 'rollback'])->name('sites.publish.rollback');

    // デザインシステム
    Route::get('/design/tokens', [DesignController::class, 'tokens'])->name('design.tokens');
    Route::put('/design/tokens', [DesignController::class, 'updateTokens'])->name('design.tokens.update');
    Route::get('/design/components', [DesignController::class, 'components'])->name('design.components');
    Route::get('/design/components/{component}', [DesignController::class, 'componentShow'])->name('design.components.show');
    Route::get('/sites/{site}/design', [DesignController::class, 'siteDesign'])->name('design.site');
    Route::put('/sites/{site}/design', [DesignController::class, 'updateSiteDesign'])->name('design.site.update');
    Route::get('/sites/{site}/design/css', [DesignController::class, 'previewCss'])->name('design.site.css');
});
