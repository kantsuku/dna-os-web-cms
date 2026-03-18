<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Site;
use App\Services\SiteBuildService;
use Illuminate\Http\Request;

class SitePartsController extends Controller
{
    public function edit(Clinic $clinic, Site $site)
    {
        $headerConfig = $site->header_config ?? $this->defaultHeader();
        $footerConfig = $site->footer_config ?? $this->defaultFooter($site);

        return view('sites.parts-edit', compact('clinic', 'site', 'headerConfig', 'footerConfig'));
    }

    public function update(Request $request, Clinic $clinic, Site $site)
    {
        $site->update([
            'header_config' => [
                'logo_text' => $request->input('header.logo_text', $site->name),
                'logo_image' => $request->input('header.logo_image'),
                'phone' => $request->input('header.phone'),
                'cta_text' => $request->input('header.cta_text', 'ご予約・お問い合わせ'),
                'cta_url' => $request->input('header.cta_url'),
                'nav_items' => $this->parseNavItems($request->input('header.nav_items_json', '[]')),
            ],
            'footer_config' => [
                'clinic_name' => $request->input('footer.clinic_name', $site->name),
                'address' => $request->input('footer.address'),
                'phone' => $request->input('footer.phone'),
                'hours' => $request->input('footer.hours'),
                'closed_day' => $request->input('footer.closed_day'),
                'copyright' => $request->input('footer.copyright'),
                'nav_items' => $this->parseNavItems($request->input('footer.nav_items_json', '[]')),
            ],
        ]);

        return redirect()->route('clinic.sites.parts.edit', [$clinic, $site])->with('success', '共通パーツを保存しました');
    }

    /**
     * ヘッダープレビュー（iframe用）— com-CSSクラスで描画
     */
    public function previewHeader(Clinic $clinic, Site $site, SiteBuildService $buildService)
    {
        $css = app(\App\Services\DesignCssService::class)->generateCss($site);
        $headerHtml = $buildService->renderHeader($site);

        return response(<<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>{$css} body{margin:0;}</style></head>
<body>{$headerHtml}</body></html>
HTML);
    }

    /**
     * フッタープレビュー（iframe用）
     */
    public function previewFooter(Clinic $clinic, Site $site, SiteBuildService $buildService)
    {
        $css = app(\App\Services\DesignCssService::class)->generateCss($site);
        $footerHtml = $buildService->renderFooter($site);

        return response(<<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>{$css} body{margin:0;}</style></head>
<body>{$footerHtml}</body></html>
HTML);
    }

    private function defaultHeader(): array
    {
        return [
            'logo_text' => '', 'logo_image' => '', 'phone' => '',
            'cta_text' => 'ご予約・お問い合わせ', 'cta_url' => '/contact',
            'nav_items' => [
                ['label' => '医院紹介', 'url' => '/about'],
                ['label' => '診療内容', 'url' => '/treatment'],
                ['label' => 'アクセス', 'url' => '/access'],
            ],
        ];
    }

    private function defaultFooter(Site $site): array
    {
        return [
            'clinic_name' => $site->name, 'address' => '', 'phone' => '',
            'hours' => '', 'closed_day' => '',
            'copyright' => '© ' . date('Y') . ' ' . $site->name,
            'nav_items' => [],
        ];
    }

    private function parseNavItems(string $json): array
    {
        $items = json_decode($json, true);
        return is_array($items) ? $items : [];
    }
}
