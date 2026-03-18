<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Site;
use Illuminate\Support\Facades\File;

class SiteBuildService
{
    private string $buildBasePath;

    public function __construct(
        private DesignCssService $cssService,
    ) {
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

        // CSS生成
        $css = $this->cssService->generateCss($site);
        File::put($buildPath . '/assets/css/style.css', $css);

        // JS
        File::put($buildPath . '/assets/js/main.js', $this->getDefaultJs());

        // ページビルド
        $pages = $site->pages()
            ->whereIn('status', ['ready', 'published'])
            ->with('currentGeneration')
            ->get();

        foreach ($pages as $page) {
            if ($page->currentGeneration) {
                $this->buildPage($site, $page, $buildPath);
            }
        }

        // .htaccess
        File::put($buildPath . '/.htaccess', $this->getHtaccess());

        return $buildPath;
    }

    /**
     * 1ページのHTMLをビルド
     */
    public function buildPage(Site $site, Page $page, string $buildPath): void
    {
        $generation = $page->currentGeneration;

        // v3: sectionsがある場合はそこからfinal_htmlを再構築
        if ($generation->hasSections()) {
            $contentHtml = $generation->buildFinalHtml();
        } else {
            $contentHtml = $generation->final_html;
        }

        $html = $this->wrapInLayout($site, $page, $contentHtml);

        $pagePath = $page->slug === '/' ? '/index.html' : '/' . trim($page->slug, '/') . '/index.html';
        $fullPath = $buildPath . $pagePath;
        File::ensureDirectoryExists(dirname($fullPath));
        File::put($fullPath, $html);
    }

    /**
     * プレビュー用HTML生成
     */
    public function previewPage(Site $site, Page $page, ?string $html = null): string
    {
        $generation = $page->currentGeneration;
        if ($html) {
            $contentHtml = $html;
        } elseif ($generation && $generation->hasSections()) {
            $contentHtml = $generation->buildFinalHtml();
        } else {
            $contentHtml = $generation?->final_html ?? '<p>コンテンツなし</p>';
        }

        $css = $this->cssService->generateCss($site);

        return $this->wrapInLayout($site, $page, $contentHtml, $css);
    }

    /**
     * コンテンツをページレイアウトで包む
     * com-section が自身でレイアウトを持つため container で包まない
     */
    private function wrapInLayout(Site $site, Page $page, string $contentHtml, ?string $inlineCss = null): string
    {
        $cssLink = $inlineCss
            ? "<style>{$inlineCss}</style>"
            : '<link rel="stylesheet" href="/assets/css/style.css">';

        $meta = $page->meta ?? [];
        $description = $meta['description'] ?? '';
        $ogImage = $meta['og_image'] ?? '';

        return <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$page->title} | {$site->name}</title>
    <meta name="description" content="{$description}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    {$cssLink}
</head>
<body>
    <main>
        {$contentHtml}
    </main>

    <script src="/assets/js/main.js"></script>
</body>
</html>
HTML;
    }

    private function getDefaultJs(): string
    {
        return <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var t = document.querySelector(this.getAttribute('href'));
            if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
        });
    });
});
JS;
    }

    private function getHtaccess(): string
    {
        return <<<'HTACCESS'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /$1/index.html [L]

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript
</IfModule>
HTACCESS;
    }
}
