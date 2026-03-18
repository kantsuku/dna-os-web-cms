<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Site;
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
     * ヘッダープレビュー（iframe用）
     */
    public function previewHeader(Clinic $clinic, Site $site)
    {
        $config = $site->header_config ?? $this->defaultHeader();
        $css = app(\App\Services\DesignCssService::class)->generateCss($site);

        $navHtml = '';
        foreach ($config['nav_items'] ?? [] as $item) {
            $navHtml .= '<a href="' . e($item['url'] ?? '#') . '" style="color:#333;text-decoration:none;font-size:14px;padding:0 12px;">' . e($item['label'] ?? '') . '</a>';
        }

        $logoText = e($config['logo_text'] ?? $site->name);
        $phone = e($config['phone'] ?? '');
        $ctaText = e($config['cta_text'] ?? '');

        return response(<<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>{$css} body{margin:0;}</style></head>
<body>
<header style="background:#fff;border-bottom:1px solid #eee;padding:16px 40px;display:flex;justify-content:space-between;align-items:center;">
    <div style="font-size:20px;font-weight:700;color:var(--color-main2, #2793EA);">{$logoText}</div>
    <nav style="display:flex;align-items:center;">{$navHtml}</nav>
    <div style="display:flex;align-items:center;gap:12px;">
        <span style="font-size:18px;font-weight:500;color:var(--color-main2-dark, #0057a1);">{$phone}</span>
        <a style="background:var(--color-main2);color:#fff;padding:8px 20px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;">{$ctaText}</a>
    </div>
</header>
</body></html>
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
