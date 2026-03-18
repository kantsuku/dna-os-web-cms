<?php

namespace App\Services;

use App\Models\ClinicComponent;
use App\Models\ClinicDesign;
use App\Models\Component;
use App\Models\DesignToken;
use App\Models\Site;
use App\Models\SiteDesign;
use Illuminate\Support\Facades\File;

class DesignCssService
{
    /**
     * サイト用の完全なCSSを生成
     *
     * 構成:
     * 1. ベースCSS（既存テーマのコンパイル済みCSS）
     * 2. CSS Custom Properties（デザイントークン → :root変数）
     * 3. サイト固有のトークン上書き
     * 4. コンポーネント個別スタイル上書き
     * 5. カスタムCSS
     *
     * 将来の移行パス:
     * - ベースCSSをCSS Custom Properties対応版に置き換え
     * - com- → acms- のクラス名マッピングレイヤー追加
     */
    /**
     * CSS生成（4層マージ: グローバル → 医院トンマナ → サイト固有 → カスタム）
     */
    public function generateCss(Site $site): string
    {
        $css = [];

        // 1. ベースCSS（既存テーマ）
        $css[] = $this->getBaseThemeCss($site);

        // 2. デザイントークン → CSS Custom Properties（グローバルデフォルト）
        $css[] = $this->generateTokenVariables();

        // 3. 医院トンマナ上書き（v3.1新規）
        $clinic = $site->clinic;
        if ($clinic && $clinic->design) {
            $css[] = $this->generateClinicOverrides($clinic->design);
        }

        // 4. グローバルコンポーネントCSS
        $css[] = $this->generateGlobalComponentCss();

        // 5. 医院固有コンポーネントCSS
        if ($clinic) {
            $css[] = $this->generateClinicComponentCss($clinic->id);
        }

        // 6. サイト固有トークン上書き
        $design = $site->design;
        if ($design) {
            $css[] = $this->generateSiteOverrides($design);
            // 7. コンポーネント個別スタイル
            $css[] = $this->generateComponentOverrides($design);
            // 8. カスタムCSS
            if ($design->custom_css) {
                $css[] = "/* サイト固有カスタムCSS */\n" . $design->custom_css;
            }
        }

        return implode("\n\n", array_filter($css));
    }

    /**
     * ベーステーマCSS
     * 既存のstyle-color1.css等をそのまま使用
     */
    private function getBaseThemeCss(Site $site): string
    {
        // サイトデザインで指定されたテーマベースがあればそれを使う
        $baseFile = resource_path('site-assets/default/css/theme-base.css');

        if (File::exists($baseFile)) {
            return "/* テーマベースCSS */\n" . File::get($baseFile);
        }

        return '';
    }

    /**
     * グローバルデザイントークンをCSS Custom Properties として出力
     *
     * 現時点ではベースCSSが$変数（コンパイル済みベタ値）なので
     * このCSS変数は「パッチ」として使う。
     * 将来ベースCSSをvar()対応に書き換えれば、
     * ここのトークン変更だけで全コンポーネントに反映される。
     */
    private function generateTokenVariables(): string
    {
        $tokens = DesignToken::orderBy('sort_order')->get();
        if ($tokens->isEmpty()) {
            return '';
        }

        $lines = [];
        foreach ($tokens as $token) {
            $lines[] = "  --{$token->key}: {$token->value};";
        }

        return "/* デザイントークン（CSS Custom Properties） */\n:root {\n" . implode("\n", $lines) . "\n}";
    }

    /**
     * 医院トンマナ上書き
     */
    private function generateClinicOverrides(ClinicDesign $clinicDesign): string
    {
        $tokens = $clinicDesign->tokens ?? [];
        if (empty($tokens)) {
            return '';
        }

        $lines = [];
        foreach ($tokens as $key => $value) {
            $lines[] = "  --{$key}: {$value};";
        }

        return "/* 医院トンマナ上書き */\n:root {\n" . implode("\n", $lines) . "\n}";
    }

    /**
     * サイト固有のトークン上書き
     */
    private function generateSiteOverrides(SiteDesign $design): string
    {
        $tokens = $design->tokens ?? [];
        if (empty($tokens)) {
            return '';
        }

        $lines = [];
        foreach ($tokens as $key => $value) {
            $lines[] = "  --{$key}: {$value};";
        }

        return "/* サイト固有トークン上書き */\n:root {\n" . implode("\n", $lines) . "\n}";
    }

    /**
     * グローバルコンポーネント（components テーブル）のcustom_css + default_styles
     */
    private function generateGlobalComponentCss(): string
    {
        $components = Component::whereNotNull('custom_css')
            ->orWhereNotNull('default_styles')
            ->orderBy('sort_order')
            ->get();

        if ($components->isEmpty()) {
            return '';
        }

        $css = "/* グローバルコンポーネントCSS */\n";
        foreach ($components as $comp) {
            if ($comp->custom_css) {
                $css .= $comp->custom_css . "\n";
            }
            if (!empty($comp->default_styles)) {
                $css .= $this->stylesToCss(".{$comp->active_key}", $comp->default_styles);
            }
        }
        return $css;
    }

    /**
     * 医院固有コンポーネント（clinic_components テーブル）のCSS
     */
    private function generateClinicComponentCss(int $clinicId): string
    {
        $components = ClinicComponent::where('clinic_id', $clinicId)
            ->whereNotNull('default_styles')
            ->orderBy('sort_order')
            ->get();

        if ($components->isEmpty()) {
            return '';
        }

        $css = "/* 医院固有コンポーネントCSS */\n";
        foreach ($components as $comp) {
            if (!empty($comp->default_styles)) {
                $css .= $this->stylesToCss(".{$comp->key}", $comp->default_styles);
            }
        }
        return $css;
    }

    /**
     * default_styles配列をCSSルールに変換
     */
    private function stylesToCss(string $selector, array $styles): string
    {
        $lines = [];
        foreach ($styles as $prop => $value) {
            $lines[] = "  {$prop}: {$value};";
        }
        return "{$selector} {\n" . implode("\n", $lines) . "\n}\n";
    }

    /**
     * コンポーネント個別スタイル上書き
     * com- クラス名をそのまま使用
     */
    private function generateComponentOverrides(SiteDesign $design): string
    {
        $styles = $design->component_styles ?? [];
        if (empty($styles)) {
            return '';
        }

        $css = "/* コンポーネント個別スタイル上書き */\n";
        foreach ($styles as $componentKey => $props) {
            $lines = [];
            foreach ($props as $prop => $value) {
                $lines[] = "  {$prop}: {$value};";
            }
            $css .= ".{$componentKey} {\n" . implode("\n", $lines) . "\n}\n";
        }

        return $css;
    }
}
