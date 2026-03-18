<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Site;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class SiteBuildService
{
    private string $buildBasePath;

    public function __construct()
    {
        $this->buildBasePath = storage_path('app/builds');
    }

    /**
     * サイト全体をビルド
     */
    public function buildSite(Site $site): string
    {
        $buildPath = $this->buildBasePath . '/' . $site->id . '_' . time();
        File::ensureDirectoryExists($buildPath);
        File::ensureDirectoryExists($buildPath . '/assets/css');
        File::ensureDirectoryExists($buildPath . '/assets/js');
        File::ensureDirectoryExists($buildPath . '/assets/images');

        $pages = $site->pages()
            ->whereIn('status', ['approved', 'published'])
            ->with(['sections.variants' => fn ($q) => $q->whereIn('status', ['approved', 'published'])->orderByDesc('version')])
            ->get();

        foreach ($pages as $page) {
            $this->buildPage($site, $page, $buildPath);
        }

        // 共通アセットのコピー
        $this->copyAssets($site, $buildPath);

        // .htaccess 生成
        $this->generateHtaccess($buildPath);

        return $buildPath;
    }

    /**
     * 1ページをビルド
     */
    public function buildPage(Site $site, Page $page, string $buildPath): string
    {
        $sections = [];
        foreach ($page->sections as $section) {
            $variant = $section->variants
                ->whereIn('status', ['published', 'approved'])
                ->sortByDesc('version')
                ->first();

            $sections[$section->section_key] = [
                'key' => $section->section_key,
                'html' => $variant?->content_html ?? '',
                'raw' => $variant?->content_raw ?? '',
            ];
        }

        $templateName = $this->resolveTemplateName($page);

        $html = $this->renderTemplate($templateName, [
            'site' => $site,
            'page' => $page,
            'sections' => $sections,
        ]);

        // ファイル配置
        $pagePath = $page->slug === '/' ? '/index.html' : '/' . trim($page->slug, '/') . '/index.html';
        $fullPath = $buildPath . $pagePath;
        File::ensureDirectoryExists(dirname($fullPath));
        File::put($fullPath, $html);

        return $fullPath;
    }

    /**
     * テンプレート名を解決
     */
    private function resolveTemplateName(Page $page): string
    {
        $template = $page->template_name;

        if ($page->page_type === 'top') {
            return 'site-templates.top.' . ($template ?: 'default');
        }

        return 'site-templates.lower.' . ($template ?: 'generic');
    }

    /**
     * Bladeテンプレートをレンダリング
     */
    private function renderTemplate(string $templateName, array $data): string
    {
        if (!View::exists($templateName)) {
            $templateName = 'site-templates.lower.generic';
        }

        return View::make($templateName, $data)->render();
    }

    /**
     * 共通アセットをビルドディレクトリにコピー
     */
    private function copyAssets(Site $site, string $buildPath): void
    {
        $assetsSource = resource_path('site-assets/' . $site->template_set);

        if (!File::isDirectory($assetsSource)) {
            $assetsSource = resource_path('site-assets/default');
        }

        if (File::isDirectory($assetsSource)) {
            File::copyDirectory($assetsSource, $buildPath . '/assets');
        }
    }

    /**
     * .htaccess 生成
     */
    private function generateHtaccess(string $buildPath): void
    {
        $htaccess = <<<'HTACCESS'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /$1/index.html [L]

# キャッシュ設定
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
</IfModule>

# Gzip
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript
</IfModule>
HTACCESS;

        File::put($buildPath . '/.htaccess', $htaccess);
    }

    /**
     * プレビュー用：1ページだけレンダリングしてHTMLを返す
     */
    public function previewPage(Site $site, Page $page): string
    {
        $sections = [];
        foreach ($page->sections()->with('variants')->get() as $section) {
            $variant = $section->variants
                ->whereIn('status', ['published', 'approved', 'draft'])
                ->sortByDesc('version')
                ->first();

            $sections[$section->section_key] = [
                'key' => $section->section_key,
                'html' => $variant?->content_html ?? '',
                'raw' => $variant?->content_raw ?? '',
            ];
        }

        $templateName = $this->resolveTemplateName($page);

        return $this->renderTemplate($templateName, [
            'site' => $site,
            'page' => $page,
            'sections' => $sections,
        ]);
    }
}
