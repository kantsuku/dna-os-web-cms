<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageGeneration;
use App\Models\Site;
use App\Services\ContentImportService;
use App\Services\SiteBuildService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Site $site)
    {
        $pages = $site->pages()->with('currentGeneration')->withCount('generations')->get();
        return view('pages.index', compact('site', 'pages'));
    }

    public function create(Site $site)
    {
        return view('pages.create', compact('site'));
    }

    public function store(Request $request, Site $site)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:500'],
            'page_type' => ['required', 'in:top,lower,blog,news,exception'],
            'treatment_key' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $page = $site->pages()->create($validated);
        return redirect()->route('sites.pages.show', [$site, $page])->with('success', 'ページを作成しました');
    }

    public function show(Site $site, Page $page)
    {
        $page->load(['generations' => fn ($q) => $q->orderByDesc('generation')->limit(10), 'currentGeneration']);
        return view('pages.show', compact('site', 'page'));
    }

    public function edit(Site $site, Page $page)
    {
        return view('pages.edit', compact('site', 'page'));
    }

    public function update(Request $request, Site $site, Page $page)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:500'],
            'page_type' => ['required', 'in:top,lower,blog,news,exception'],
            'treatment_key' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:draft,ready,published,archived'],
        ]);

        $page->update($validated);
        return redirect()->route('sites.pages.show', [$site, $page])->with('success', 'ページを更新しました');
    }

    /**
     * 原稿取り込み画面
     */
    public function importForm(Site $site, Page $page)
    {
        return view('pages.import', compact('site', 'page'));
    }

    /**
     * 原稿取り込み実行
     */
    public function import(Request $request, Site $site, Page $page, ContentImportService $importService)
    {
        $request->validate([
            'source_url' => ['required', 'url'],
        ]);

        try {
            $html = $importService->fetchFromUrl($request->input('source_url'));
            $generation = $importService->importToPage($page, $html, $request->input('source_url'), auth()->id());

            return redirect()->route('sites.pages.show', [$site, $page])->with('success', "世代{$generation->generation}として取り込みました");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', '取り込み失敗: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * 微細編集画面
     */
    public function editContent(Site $site, Page $page)
    {
        $generation = $page->currentGeneration ?? $page->generations()->orderByDesc('generation')->first();
        return view('pages.edit-content', compact('site', 'page', 'generation'));
    }

    /**
     * 微細編集保存
     */
    public function updateContent(Request $request, Site $site, Page $page, ContentImportService $importService)
    {
        $request->validate([
            'final_html' => ['required', 'string'],
            'patch_reason' => ['required', 'string'],
        ]);

        $generation = $page->currentGeneration ?? $page->generations()->orderByDesc('generation')->first();
        if (!$generation) {
            return redirect()->back()->with('error', '世代がありません');
        }

        $importService->applyPatch($generation, $request->input('final_html'), $request->input('patch_reason'), auth()->id());

        return redirect()->route('sites.pages.show', [$site, $page])->with('success', '微細編集を保存しました');
    }

    /**
     * 世代をreadyに
     */
    public function markReady(Site $site, Page $page, PageGeneration $generation)
    {
        $generation->update(['status' => 'ready']);
        $page->update(['current_generation_id' => $generation->id, 'status' => 'ready']);

        return redirect()->route('sites.pages.show', [$site, $page])->with('success', '公開準備完了にしました');
    }

    /**
     * プレビュー
     */
    public function preview(Site $site, Page $page, SiteBuildService $buildService)
    {
        return response($buildService->previewPage($site, $page));
    }

    /**
     * 世代比較
     */
    public function compareGenerations(Site $site, Page $page, Request $request)
    {
        $gen1 = PageGeneration::findOrFail($request->input('gen1'));
        $gen2 = PageGeneration::findOrFail($request->input('gen2'));

        return view('pages.compare', compact('site', 'page', 'gen1', 'gen2'));
    }
}
