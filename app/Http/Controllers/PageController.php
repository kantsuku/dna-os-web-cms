<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Site;
use App\Services\SiteBuildService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Site $site)
    {
        $pages = $site->pages()->withCount('sections')->get();
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
            'template_name' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $page = $site->pages()->create($validated);

        return redirect()->route('sites.pages.show', [$site, $page])->with('success', 'ページを作成しました');
    }

    public function show(Site $site, Page $page)
    {
        $page->load(['sections' => fn ($q) => $q->with(['variants' => fn ($q2) => $q2->orderByDesc('version')->limit(3), 'overrideRule'])]);
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
            'template_name' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:draft,pending_review,approved,published,archived'],
        ]);

        $page->update($validated);

        return redirect()->route('sites.pages.show', [$site, $page])->with('success', 'ページを更新しました');
    }

    public function preview(Site $site, Page $page, SiteBuildService $buildService)
    {
        $html = $buildService->previewPage($site, $page);
        return response($html);
    }
}
