<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Page;
use App\Models\PageGeneration;
use App\Models\Site;
use App\Services\ContentImportService;
use App\Services\SiteBuildService;
use App\Services\Web\SectionParseService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Clinic $clinic, Site $site)
    {
        $pages = $site->pages()->with('currentGeneration')->withCount('generations')->get();
        return view('pages.index', compact('site', 'pages'));
    }

    public function create(Clinic $clinic, Site $site)
    {
        return view('pages.create', compact('site'));
    }

    public function store(Request $request, Clinic $clinic, Site $site)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:500'],
            'page_type' => ['required', 'in:top,lower,blog,news,exception,case'],
            'template_key' => ['nullable', 'string', 'max:100'],
            'treatment_key' => ['nullable', 'string', 'max:100'],
            'dna_source_key' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:pages,id'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['content_classification'] = Page::classifyByPageType($validated['page_type']);
        $validated['template_key'] = $validated['template_key'] ?? 'generic';
        $validated['sort_order'] = $validated['sort_order'] ?? ($site->pages()->max('sort_order') + 1);

        $page = $site->pages()->create($validated);
        return redirect()->route('clinic.sites.pages.show', [$clinic, $site, $page])->with('success', 'ページを作成しました');
    }

    public function show(Clinic $clinic, Site $site, Page $page)
    {
        $page->load([
            'generations' => fn ($q) => $q->orderByDesc('generation')->limit(10),
            'currentGeneration',
            'children.currentGeneration',
            'parent',
        ]);
        $sections = $page->currentGeneration?->sections ?? [];
        return view('pages.show', compact('site', 'page', 'sections'));
    }

    public function edit(Clinic $clinic, Site $site, Page $page)
    {
        return view('pages.edit', compact('site', 'page'));
    }

    public function update(Request $request, Clinic $clinic, Site $site, Page $page)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:500'],
            'page_type' => ['required', 'in:top,lower,blog,news,exception,case'],
            'template_key' => ['nullable', 'string', 'max:100'],
            'treatment_key' => ['nullable', 'string', 'max:100'],
            'dna_source_key' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'parent_id' => ['nullable', 'exists:pages,id'],
            'status' => ['nullable', 'in:draft,ready,published,archived'],
            'meta' => ['nullable', 'array'],
        ]);

        $page->update($validated);
        return redirect()->route('clinic.sites.pages.show', [$clinic, $site, $page])->with('success', 'ページを更新しました');
    }

    // ─── 原稿取り込み ───

    public function importForm(Clinic $clinic, Site $site, Page $page)
    {
        return view('pages.import', compact('site', 'page'));
    }

    public function import(Request $request, Clinic $clinic, Site $site, Page $page, ContentImportService $importService)
    {
        $request->validate([
            'source_url' => ['nullable', 'url'],
            'markup_text' => ['nullable', 'string'],
        ]);

        try {
            if ($request->filled('markup_text')) {
                $generation = $importService->importFromMarkupText(
                    $page, $request->input('markup_text'), auth()->id()
                );
            } elseif ($request->filled('source_url')) {
                $html = $importService->fetchFromUrl($request->input('source_url'));
                $generation = $importService->importToPage(
                    $page, $html, $request->input('source_url'), auth()->id()
                );
            } else {
                return redirect()->back()->with('error', 'URLまたはマークアップHTMLを入力してください')->withInput();
            }

            $sectionsCount = count($generation->sections ?? []);
            $skipped = $generation->meta_json['skipped_sections'] ?? [];
            $msg = "世代{$generation->generation}として取り込みました（{$sectionsCount}セクション）";
            if (!empty($skipped)) {
                $msg .= '。' . count($skipped) . 'セクションはロックのためスキップされました';
            }

            return redirect()->route('clinic.sites.pages.show', [$clinic, $site, $page])->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', '取り込み失敗: ' . $e->getMessage())->withInput();
        }
    }

    // ─── セクション管理 ───

    public function sections(Clinic $clinic, Site $site, Page $page)
    {
        $generation = $page->currentGeneration ?? $page->generations()->orderByDesc('generation')->first();
        $sections = $generation?->sections ?? [];

        return view('pages.sections', compact('site', 'page', 'generation', 'sections'));
    }

    public function editSection(Clinic $clinic, Site $site, Page $page, string $sectionId)
    {
        $generation = $page->currentGeneration ?? $page->generations()->orderByDesc('generation')->first();
        if (!$generation) {
            return redirect()->route('clinic.sites.pages.show', [$clinic, $site, $page])->with('error', '世代がありません');
        }

        $section = $generation->getSection($sectionId);
        if (!$section) {
            return redirect()->route('clinic.sites.pages.sections', [$clinic, $site, $page])->with('error', 'セクションが見つかりません');
        }

        return view('pages.edit-section', compact('site', 'page', 'generation', 'section', 'sectionId'));
    }

    public function updateSection(Request $request, Clinic $clinic, Site $site, Page $page, string $sectionId, ContentImportService $importService)
    {
        $request->validate([
            'content_html' => ['required', 'string'],
            'patch_reason' => ['required', 'string'],
            'lock_after_edit' => ['nullable', 'boolean'],
        ]);

        $generation = $page->currentGeneration ?? $page->generations()->orderByDesc('generation')->first();
        if (!$generation) {
            return redirect()->back()->with('error', '世代がありません');
        }

        $importService->applySectionPatch(
            $generation, $sectionId,
            $request->input('content_html'), $request->input('patch_reason'),
            auth()->id(), (bool) $request->input('lock_after_edit', false),
        );

        return redirect()->route('clinic.sites.pages.sections', [$clinic, $site, $page])->with('success', 'セクションを更新しました');
    }

    public function toggleLock(Request $request, Clinic $clinic, Site $site, Page $page, string $sectionId, ContentImportService $importService)
    {
        $request->validate(['lock_status' => ['required', 'in:unlocked,human_locked']]);

        $generation = $page->currentGeneration ?? $page->generations()->orderByDesc('generation')->first();
        if (!$generation) {
            return redirect()->back()->with('error', '世代がありません');
        }

        $importService->toggleSectionLock($generation, $sectionId, $request->input('lock_status'));
        $label = $request->input('lock_status') === 'human_locked' ? 'ロック' : 'アンロック';

        return redirect()->route('clinic.sites.pages.sections', [$clinic, $site, $page])->with('success', "セクションを{$label}しました");
    }

    // ─── 微細編集（v2互換） ───

    public function editContent(Clinic $clinic, Site $site, Page $page)
    {
        $generation = $page->currentGeneration ?? $page->generations()->orderByDesc('generation')->first();

        if ($generation && $generation->hasSections()) {
            return redirect()->route('clinic.sites.pages.sections', [$clinic, $site, $page]);
        }

        return view('pages.edit-content', compact('site', 'page', 'generation'));
    }

    public function updateContent(Request $request, Clinic $clinic, Site $site, Page $page, ContentImportService $importService)
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
        return redirect()->route('clinic.sites.pages.show', [$clinic, $site, $page])->with('success', '微細編集を保存しました');
    }

    // ─── 世代管理 ───

    public function markReady(Clinic $clinic, Site $site, Page $page, PageGeneration $generation)
    {
        $generation->update(['status' => 'ready']);
        $page->update(['current_generation_id' => $generation->id, 'status' => 'ready']);
        return redirect()->route('clinic.sites.pages.show', [$clinic, $site, $page])->with('success', '公開準備完了にしました');
    }

    public function preview(Clinic $clinic, Site $site, Page $page, SiteBuildService $buildService)
    {
        return response($buildService->previewPage($site, $page));
    }

    /**
     * ページコンテンツをcom-CSS付きで返す（iframe埋め込み用）
     */
    public function contentFrame(Clinic $clinic, Site $site, Page $page)
    {
        $gen = $page->currentGeneration;
        if (!$gen) {
            return response('<p style="color:#999;text-align:center;padding:40px">コンテンツがありません</p>');
        }

        $contentHtml = $gen->hasSections() ? $gen->buildFinalHtml() : ($gen->final_html ?? $gen->content_html ?? '');
        $css = app(\App\Services\DesignCssService::class)->generateCss($site);

        $editable = request()->boolean('editable') ? 'contenteditable="true"' : '';

        return response(<<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
{$css}
body { margin: 0; padding: 0; }
[contenteditable]:focus { outline: 2px solid #6366f1; outline-offset: 2px; border-radius: 4px; }
</style>
</head>
<body {$editable}>{$contentHtml}</body>
</html>
HTML);
    }

    /**
     * 個別セクションをcom-CSS付きで返す（iframe埋め込み用）
     */
    public function sectionFrame(Clinic $clinic, Site $site, Page $page, string $sectionId)
    {
        $gen = $page->currentGeneration;
        $section = $gen?->getSection($sectionId);
        $contentHtml = $section['content_html'] ?? '<p>セクションが見つかりません</p>';
        $css = app(\App\Services\DesignCssService::class)->generateCss($site);

        $editable = request()->boolean('editable') ? 'contenteditable="true"' : '';

        return response(<<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
{$css}
body { margin: 0; padding: 0; }
[contenteditable]:focus { outline: 2px solid #6366f1; outline-offset: 2px; }
</style>
</head>
<body {$editable}>{$contentHtml}</body>
</html>
HTML);
    }

    public function compareGenerations(Clinic $clinic, Site $site, Page $page, Request $request)
    {
        $gen1 = PageGeneration::findOrFail($request->input('gen1'));
        $gen2 = PageGeneration::findOrFail($request->input('gen2'));
        return view('pages.compare', compact('site', 'page', 'gen1', 'gen2'));
    }
}
