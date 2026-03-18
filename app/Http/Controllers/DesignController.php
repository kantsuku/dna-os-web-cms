<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Component;
use App\Models\DesignToken;
use App\Models\Site;
use App\Models\SiteDesign;
use App\Services\DesignCssService;
use Illuminate\Http\Request;

class DesignController extends Controller
{
    public function tokens(Clinic $clinic)
    {
        $tokens = DesignToken::orderBy('category')->orderBy('sort_order')->get()->groupBy('category');
        return view('design.tokens', compact('tokens'));
    }

    public function updateTokens(Request $request, Clinic $clinic)
    {
        $updates = $request->input('tokens', []);
        foreach ($updates as $id => $value) {
            DesignToken::where('id', $id)->update(['value' => $value]);
        }
        return redirect()->route('clinic.design.tokens', $clinic)->with('success', 'デザイントークンを更新しました');
    }

    public function components(Clinic $clinic)
    {
        $components = Component::orderBy('category')->orderBy('sort_order')->get()->groupBy('category');
        return view('design.components', compact('components'));
    }

    public function componentShow(Clinic $clinic, Component $component)
    {
        return view('design.component-show', compact('component'));
    }

    /**
     * コンポーネントプレビュー（com-CSS適用、iframe用）
     */
    public function componentPreviewFrame(Clinic $clinic, Component $component)
    {
        $previewHtml = $component->preview_html ?? $component->html_template ?? '<p style="color:#999">プレビューなし</p>';
        $baseCss = file_get_contents(resource_path('site-assets/default/css/theme-base.css'));

        return response(<<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
{$baseCss}
body { margin: 0; padding: 16px; }
</style>
</head>
<body>{$previewHtml}</body>
</html>
HTML);
    }

    public function componentEdit(Clinic $clinic, Component $component)
    {
        return view('design.component-edit', compact('component'));
    }

    public function componentUpdate(Request $request, Clinic $clinic, Component $component)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'html_template' => 'nullable|string',
            'preview_html' => 'nullable|string',
            'variants' => 'nullable|string', // カンマ区切り
        ]);

        $variants = $validated['variants']
            ? array_map('trim', explode(',', $validated['variants']))
            : null;

        $component->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'html_template' => $validated['html_template'],
            'preview_html' => $validated['preview_html'],
            'variants' => $variants,
        ]);

        return redirect()->route('clinic.design.components', $clinic)->with('success', 'コンポーネントを更新しました');
    }

    public function siteDesign(Clinic $clinic, Site $site)
    {
        $design = $site->design ?? SiteDesign::create(['site_id' => $site->id, 'name' => 'default', 'status' => 'active']);
        $globalTokens = DesignToken::orderBy('category')->orderBy('sort_order')->get()->groupBy('category');
        return view('design.site-design', compact('site', 'design', 'globalTokens'));
    }

    public function updateSiteDesign(Request $request, Clinic $clinic, Site $site)
    {
        $design = $site->design;
        $design->update([
            'tokens' => $request->input('tokens', []),
            'custom_css' => $request->input('custom_css', ''),
        ]);
        return redirect()->route('clinic.design.site', [$clinic, $site])->with('success', 'サイトデザインを更新しました');
    }

    public function previewCss(Clinic $clinic, Site $site, DesignCssService $cssService)
    {
        return response($cssService->generateCss($site), 200, ['Content-Type' => 'text/css']);
    }
}
