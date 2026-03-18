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
        $description = e($meta['description'] ?? '');

        $headerHtml = $this->renderHeader($site);
        $footerHtml = $this->renderFooter($site);
        $hamburgerJs = $this->getHamburgerJs();

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
    {$headerHtml}

    <main>
        {$contentHtml}
    </main>

    {$footerHtml}

    <script src="/assets/js/main.js"></script>
    <script>{$hamburgerJs}</script>
</body>
</html>
HTML;
    }

    /**
     * ヘッダーHTMLを生成（com-CSSクラス使用）
     */
    public function renderHeader(Site $site): string
    {
        $config = $site->header_config ?? [];
        $navItems = $site->getNavItems();

        $logoText = e($config['logo_text'] ?? $site->name);
        $logoImage = $config['logo_image'] ?? '';
        $phone = e($config['phone'] ?? '');
        $ctaText = e($config['cta_text'] ?? 'ご予約・お問い合わせ');
        $ctaUrl = e($config['cta_url'] ?? '/contact');

        // ロゴ: 画像があれば画像、なければテキスト
        $logoHtml = $logoImage
            ? '<img src="' . e($logoImage) . '" alt="' . $logoText . '">'
            : $logoText;

        // デスクトップナビ
        $navHtml = '';
        foreach ($navItems as $item) {
            $navHtml .= '<a href="' . e($item['url'] ?? '#') . '">' . e($item['label'] ?? '') . '</a>';
        }

        // モバイルナビ（ドロワー）
        $mobileNavHtml = '';
        foreach ($navItems as $item) {
            $mobileNavHtml .= '<a href="' . e($item['url'] ?? '#') . '">' . e($item['label'] ?? '') . '</a>';
        }
        if ($phone) {
            $mobileNavHtml .= '<a href="tel:' . preg_replace('/[^\d+]/', '', $phone) . '" class="com-mobile-nav-phone">' . $phone . '</a>';
        }
        if ($ctaText) {
            $mobileNavHtml .= '<a href="' . $ctaUrl . '" class="com-mobile-nav-cta">' . $ctaText . '</a>';
        }

        // 電話番号（デスクトップ）
        $phoneHtml = $phone
            ? '<a href="tel:' . preg_replace('/[^\d+]/', '', $phone) . '" class="com-header-phone">' . $phone . '</a>'
            : '';

        // CTAボタン
        $ctaHtml = $ctaText
            ? '<a href="' . $ctaUrl . '" class="com-header-cta">' . $ctaText . '</a>'
            : '';

        return <<<HTML
<header class="com-header">
    <div class="com-header-inner">
        <a href="/" class="com-header-logo">{$logoHtml}</a>
        <nav class="com-header-nav">{$navHtml}</nav>
        <div class="com-header-right">
            {$phoneHtml}
            {$ctaHtml}
            <button class="com-hamburger" id="js-hamburger" aria-label="メニュー"><span></span></button>
        </div>
    </div>
</header>
<div class="com-mobile-nav" id="js-mobile-nav">
    <div class="com-mobile-nav-panel">
        {$mobileNavHtml}
    </div>
</div>
HTML;
    }

    /**
     * フッターHTMLを生成
     */
    public function renderFooter(Site $site): string
    {
        $config = $site->footer_config ?? [];
        $navItems = $site->getNavItems();

        $clinicName = e($config['clinic_name'] ?? $site->name);
        $address = e($config['address'] ?? '');
        $phone = e($config['phone'] ?? '');
        $hours = e($config['hours'] ?? '');
        $closedDay = e($config['closed_day'] ?? '');
        $copyright = e($config['copyright'] ?? '© ' . date('Y') . ' ' . $site->name);

        // 医院情報
        $infoHtml = '<h3>' . $clinicName . '</h3>';
        if ($address) $infoHtml .= '<p>' . $address . '</p>';
        if ($phone) $infoHtml .= '<p>TEL: <a href="tel:' . preg_replace('/[^\d+]/', '', $phone) . '" style="color:rgba(255,255,255,0.8)">' . $phone . '</a></p>';
        if ($hours) $infoHtml .= '<p>診療時間: ' . $hours . '</p>';
        if ($closedDay) $infoHtml .= '<p>休診日: ' . $closedDay . '</p>';

        // フッターナビ
        $footerNavItems = $config['nav_items'] ?? [];
        $validFooterNav = array_filter($footerNavItems, fn($item) => !empty($item['label']));
        // フッター用navが空ならヘッダーと同じnavを使う
        if (empty($validFooterNav)) {
            $validFooterNav = $navItems;
        }
        $navHtml = '';
        foreach ($validFooterNav as $item) {
            $navHtml .= '<a href="' . e($item['url'] ?? '#') . '">' . e($item['label'] ?? '') . '</a>';
        }

        return <<<HTML
<footer class="com-footer">
    <div class="com-footer-inner">
        <div class="com-footer-info">
            {$infoHtml}
        </div>
        <nav class="com-footer-nav">
            {$navHtml}
        </nav>
    </div>
    <div class="com-footer-bottom">{$copyright}</div>
</footer>
HTML;
    }

    /**
     * ハンバーガーメニュー用JavaScript
     */
    private function getHamburgerJs(): string
    {
        return <<<'JS'
(function(){
    var btn = document.getElementById('js-hamburger');
    var nav = document.getElementById('js-mobile-nav');
    if (!btn || !nav) return;
    btn.addEventListener('click', function() {
        btn.classList.toggle('is-open');
        nav.classList.toggle('is-open');
        document.body.style.overflow = nav.classList.contains('is-open') ? 'hidden' : '';
    });
    nav.addEventListener('click', function(e) {
        if (e.target === nav) {
            btn.classList.remove('is-open');
            nav.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    });
    nav.querySelectorAll('a').forEach(function(a) {
        a.addEventListener('click', function() {
            btn.classList.remove('is-open');
            nav.classList.remove('is-open');
            document.body.style.overflow = '';
        });
    });
})();
JS;
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
