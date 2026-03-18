<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublishController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SiteController;
use App\Http\Middleware\EnsureSiteAccess;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// 認証
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', fn () => redirect('/dashboard'));

// 認証必須
Route::middleware('auth')->group(function () {
    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // サイト管理（admin, editor）
    Route::middleware(RoleMiddleware::class . ':admin,editor,reviewer')->group(function () {
        Route::resource('sites', SiteController::class);
        Route::post('/sites/{site}/sync', [SiteController::class, 'sync'])->name('sites.sync');
        Route::post('/sites/{site}/test-ftp', [SiteController::class, 'testFtp'])->name('sites.test-ftp');

        // ページ管理
        Route::middleware(EnsureSiteAccess::class)->group(function () {
            Route::resource('sites.pages', PageController::class);
            Route::get('/sites/{site}/pages/{page}/preview', [PageController::class, 'preview'])->name('sites.pages.preview');

            // セクション
            Route::post('/sites/{site}/pages/{page}/sections', [SectionController::class, 'store'])->name('sites.pages.sections.store');
        });

        // セクション詳細（サイトコンテキスト外）
        Route::get('/sections/{section}', [SectionController::class, 'show'])->name('sections.show');
        Route::get('/sections/{section}/edit', [SectionController::class, 'edit'])->name('sections.edit');
        Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::put('/sections/{section}/override-policy', [SectionController::class, 'updateOverridePolicy'])->name('sections.override-policy');

        // 公開管理
        Route::get('/sites/{site}/publish', [PublishController::class, 'index'])->name('sites.publish.index');
        Route::post('/sites/{site}/publish/deploy', [PublishController::class, 'deploy'])->name('sites.publish.deploy');
        Route::post('/sites/{site}/publish/{record}/rollback', [PublishController::class, 'rollback'])->name('sites.publish.rollback');
    });

    // ユーザー管理（admin のみ）
    Route::middleware(RoleMiddleware::class . ':admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', fn () => view('admin.users'))->name('users');
    });
});
