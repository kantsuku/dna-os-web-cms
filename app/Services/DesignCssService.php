<?php

namespace App\Services;

use App\Models\Component;
use App\Models\DesignToken;
use App\Models\Site;
use App\Models\SiteDesign;

class DesignCssService
{
    /**
     * サイト用の完全なCSSを生成
     */
    public function generateCss(Site $site): string
    {
        $css = [];

        // 1. グローバルデザイントークン
        $css[] = $this->generateGlobalTokens();

        // 2. サイト固有のトークン上書き
        $design = $site->design;
        if ($design) {
            $css[] = $this->generateSiteTokens($design);
            // 3. コンポーネント個別スタイル
            $css[] = $this->generateComponentOverrides($design);
        }

        // 4. コンポーネント基本CSS
        $css[] = $this->generateComponentCss();

        // 5. サイト固有カスタムCSS
        if ($design?->custom_css) {
            $css[] = "/* カスタムCSS */\n" . $design->custom_css;
        }

        return implode("\n\n", array_filter($css));
    }

    /**
     * グローバルデザイントークン → :root CSS変数
     */
    private function generateGlobalTokens(): string
    {
        return DesignToken::toCssVariables();
    }

    /**
     * サイト固有トークン → :root 上書き
     */
    private function generateSiteTokens(SiteDesign $design): string
    {
        $tokens = $design->tokens ?? [];
        if (empty($tokens)) {
            return '';
        }

        $lines = [];
        foreach ($tokens as $key => $value) {
            $lines[] = "  --acms-{$key}: {$value};";
        }

        return "/* サイト固有トークン */\n:root {\n" . implode("\n", $lines) . "\n}";
    }

    /**
     * コンポーネント個別スタイル上書き
     */
    private function generateComponentOverrides(SiteDesign $design): string
    {
        $styles = $design->component_styles ?? [];
        if (empty($styles)) {
            return '';
        }

        $css = "/* コンポーネント個別スタイル */\n";
        foreach ($styles as $componentKey => $vars) {
            $lines = [];
            foreach ($vars as $varName => $value) {
                $lines[] = "  {$varName}: {$value};";
            }
            $css .= ".{$componentKey} {\n" . implode("\n", $lines) . "\n}\n";
        }

        return $css;
    }

    /**
     * コンポーネント基本CSSを生成
     */
    private function generateComponentCss(): string
    {
        // 基本的なコンポーネントスタイルを返す
        // 実際のプロダクションではファイルから読み込むが、MVPではインラインで生成
        return <<<'CSS'
/* ACMS コンポーネント基本スタイル */
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: var(--acms-font-base, 'Noto Sans JP', sans-serif);
    color: var(--acms-color-text, #1f2937);
    line-height: 1.8;
    background: var(--acms-color-bg, #ffffff);
}

.container {
    max-width: var(--acms-max-width, 1100px);
    margin: 0 auto;
    padding: 0 20px;
}

.acms-section {
    padding: var(--acms-spacing-section, 60px) 0;
}

.acms-section--alt {
    padding: var(--acms-spacing-section, 60px) 0;
    background: var(--acms-color-bg-alt, #f9fafb);
}

.acms-page-header {
    padding: 40px 0;
    border-bottom: 2px solid var(--acms-color-primary, #2563eb);
}

.acms-page-header h1 {
    font-size: 1.75rem;
    font-family: var(--acms-font-heading, var(--acms-font-base));
}

.acms-page-header .lead {
    color: var(--acms-color-text-light, #6b7280);
    margin-top: 8px;
}

.acms-h2 {
    font-size: var(--acms-h2-font-size, 1.5rem);
    font-weight: var(--acms-h2-font-weight, 700);
    color: var(--acms-h2-color, var(--acms-color-text));
    border-bottom: var(--acms-h2-border-width, 2px) solid var(--acms-h2-border-color, var(--acms-color-primary));
    padding-bottom: var(--acms-h2-padding-bottom, 0.5rem);
    margin-bottom: var(--acms-h2-margin-bottom, 1.5rem);
}

.acms-h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 2rem 0 0.75rem;
}

.acms-h4 {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 1.5rem 0 0.5rem;
}

.acms-media {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    margin: 1.5rem 0;
}

.acms-media--right { flex-direction: row; }
.acms-media--left { flex-direction: row-reverse; }

.acms-media img {
    max-width: 40%;
    height: auto;
    border-radius: var(--acms-radius-base, 8px);
}

.acms-grid { display: grid; gap: 1.5rem; margin: 1.5rem 0; }
.acms-grid--col-2 { grid-template-columns: repeat(2, 1fr); }
.acms-grid--col-3 { grid-template-columns: repeat(3, 1fr); }

.acms-checklist {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.acms-checklist li {
    padding: 0.5rem 0 0.5rem 2rem;
    position: relative;
}

.acms-checklist li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: var(--acms-color-primary);
    font-weight: bold;
}

.acms-list {
    padding-left: 1.5rem;
    margin: 1rem 0;
}

.acms-list li {
    margin-bottom: 0.5rem;
}

.acms-flow {
    counter-reset: flow-step;
    list-style: none;
    padding: 0;
    margin: 1.5rem 0;
}

.acms-flow li {
    counter-increment: flow-step;
    padding: 1rem 1rem 1rem 3.5rem;
    position: relative;
    border-left: 2px solid var(--acms-color-border, #e5e7eb);
    margin-bottom: 0.5rem;
}

.acms-flow li::before {
    content: counter(flow-step);
    position: absolute;
    left: -0.75rem;
    width: 1.5rem;
    height: 1.5rem;
    background: var(--acms-color-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
}

.acms-faq { margin: 1.5rem 0; }

.acms-faq dt {
    font-weight: 600;
    padding: 0.75rem 0;
    cursor: pointer;
}

.acms-faq dt::before {
    content: 'Q. ';
    color: var(--acms-color-primary);
    font-weight: bold;
}

.acms-faq dd {
    padding: 0 0 1rem 1.5rem;
    color: var(--acms-color-text-light);
}

.acms-callout {
    padding: 1.5rem;
    border-radius: var(--acms-radius-base, 8px);
    margin: 1.5rem 0;
}

.acms-callout--point {
    background: var(--acms-color-primary-light, #eff6ff);
    border-left: 4px solid var(--acms-color-primary);
}

.acms-callout--comment {
    background: var(--acms-color-bg-alt, #f9fafb);
    border-left: 4px solid var(--acms-color-text-light);
}

.acms-note {
    font-size: 0.875rem;
    color: var(--acms-color-text-light);
    padding: 1rem;
    background: var(--acms-color-bg-alt);
    border-radius: var(--acms-radius-base);
    margin: 1rem 0;
}

.acms-cta {
    background: var(--acms-color-primary);
    color: white;
    text-align: center;
    padding: 48px 20px;
    margin: 2rem 0;
    border-radius: var(--acms-radius-base);
}

.acms-cta a {
    display: inline-block;
    background: white;
    color: var(--acms-color-primary);
    padding: 12px 32px;
    border-radius: var(--acms-radius-base);
    text-decoration: none;
    font-weight: 700;
    margin-top: 16px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

th, td {
    border: 1px solid var(--acms-color-border, #e5e7eb);
    padding: 0.5rem 0.75rem;
    text-align: left;
}

th { background: var(--acms-color-bg-alt); font-weight: 600; }

p { margin-bottom: 1rem; }

img { max-width: 100%; height: auto; }

/* レスポンシブ */
@media (max-width: 768px) {
    .acms-media { flex-direction: column !important; }
    .acms-media img { max-width: 100%; }
    .acms-grid--col-2, .acms-grid--col-3 { grid-template-columns: 1fr; }
    .acms-page-header h1 { font-size: 1.375rem; }
}

/* ヘッダー・フッター */
.site-header {
    background: var(--acms-color-bg);
    border-bottom: 1px solid var(--acms-color-border, #e5e7eb);
    padding: 16px 0;
}

.site-header .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.site-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--acms-color-primary);
    text-decoration: none;
}

.site-footer {
    background: var(--acms-color-text);
    color: #9ca3af;
    padding: 24px 0;
    text-align: center;
    font-size: 0.75rem;
}

.breadcrumb ol {
    list-style: none;
    display: flex;
    gap: 8px;
    font-size: 0.75rem;
    color: var(--acms-color-text-light);
    padding: 12px 0;
}

.breadcrumb a { color: var(--acms-color-primary); text-decoration: none; }
.breadcrumb li + li::before { content: '>'; margin-right: 8px; }
CSS;
    }
}
