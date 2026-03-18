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
