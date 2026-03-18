<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\DesignToken;
use App\Models\Site;
use App\Models\SiteDesign;
use App\Services\DesignCssService;
use Illuminate\Http\Request;

class DesignController extends Controller
{
    /**
     * デザイントークン管理
     */
    public function tokens()
    {
        $tokens = DesignToken::orderBy('category')->orderBy('sort_order')->get()->groupBy('category');
        return view('design.tokens', compact('tokens'));
    }

    /**
     * デザイントークン更新
     */
    public function updateTokens(Request $request)
    {
        $updates = $request->input('tokens', []);

        foreach ($updates as $id => $value) {
            DesignToken::where('id', $id)->update(['value' => $value]);
        }

        return redirect()->route('design.tokens')->with('success', 'デザイントークンを更新しました');
    }

    /**
     * コンポーネント一覧
     */
    public function components()
    {
        $components = Component::orderBy('category')->orderBy('sort_order')->get()->groupBy('category');
        return view('design.components', compact('components'));
    }

    /**
     * コンポーネント詳細
     */
    public function componentShow(Component $component)
    {
        return view('design.component-show', compact('component'));
    }

    /**
     * サイトデザイン設定
     */
    public function siteDesign(Site $site)
    {
        $design = $site->design ?? SiteDesign::create(['site_id' => $site->id, 'name' => 'default', 'status' => 'active']);
        $globalTokens = DesignToken::orderBy('category')->orderBy('sort_order')->get()->groupBy('category');

        return view('design.site-design', compact('site', 'design', 'globalTokens'));
    }

    /**
     * サイトデザイン更新
     */
    public function updateSiteDesign(Request $request, Site $site)
    {
        $design = $site->design;

        $design->update([
            'tokens' => $request->input('tokens', []),
            'custom_css' => $request->input('custom_css', ''),
        ]);

        return redirect()->route('design.site', $site)->with('success', 'サイトデザインを更新しました');
    }

    /**
     * CSSプレビュー（API）
     */
    public function previewCss(Site $site, DesignCssService $cssService)
    {
        return response($cssService->generateCss($site), 200, ['Content-Type' => 'text/css']);
    }
}
