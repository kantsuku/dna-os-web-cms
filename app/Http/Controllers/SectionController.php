<?php

namespace App\Http\Controllers;

use App\Models\ContentVariant;
use App\Models\OverrideRule;
use App\Models\Section;
use App\Models\Site;
use App\Models\Page;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request, Site $site, Page $page)
    {
        $validated = $request->validate([
            'section_key' => ['required', 'string', 'max:100'],
            'content_source_type' => ['required', 'in:dna_os,manual,exception,client_post'],
            'content_source_ref' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $section = $page->sections()->create($validated);

        // デフォルトの上書き制御ルールを作成
        OverrideRule::create([
            'section_id' => $section->id,
            'policy' => $section->getDefaultPolicy(),
            'reason' => '自動設定',
            'set_by' => auth()->id(),
        ]);

        return redirect()->route('sites.pages.show', [$site, $page])->with('success', 'セクションを追加しました');
    }

    public function show(Section $section)
    {
        $section->load(['page.site', 'variants', 'overrideRule']);
        return view('sections.show', compact('section'));
    }

    public function edit(Section $section)
    {
        $section->load(['page.site', 'variants' => fn ($q) => $q->orderByDesc('version'), 'overrideRule']);
        $activeVariant = $section->variants->first();

        return view('sections.edit', compact('section', 'activeVariant'));
    }

    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'content_html' => ['required', 'string'],
            'edit_reason' => ['nullable', 'string'],
        ]);

        $nextVersion = ($section->variants()->max('version') ?? 0) + 1;

        $activeVariant = $section->variants()->orderByDesc('version')->first();

        ContentVariant::create([
            'section_id' => $section->id,
            'version' => $nextVersion,
            'source_type' => 'human_edit',
            'content_html' => $validated['content_html'],
            'content_raw' => strip_tags($validated['content_html']),
            'original_content' => $activeVariant?->original_content,
            'edited_by' => auth()->id(),
            'edit_reason' => $validated['edit_reason'] ?? null,
            'status' => 'draft',
        ]);

        // 人間修正フラグを立てる
        $section->update(['is_human_edited' => true]);

        // auto_sync → confirm_before_sync に自動昇格
        $rule = $section->overrideRule;
        if ($rule && $rule->policy === 'auto_sync') {
            $rule->update([
                'policy' => 'confirm_before_sync',
                'reason' => '人間修正が入ったため自動昇格',
                'set_by' => auth()->id(),
            ]);
        }

        $site = $section->page->site;
        $page = $section->page;

        return redirect()->route('sites.pages.show', [$site, $page])->with('success', 'コンテンツを更新しました');
    }

    public function updateOverridePolicy(Request $request, Section $section)
    {
        $validated = $request->validate([
            'policy' => ['required', 'in:auto_sync,confirm_before_sync,manual_only,locked'],
            'reason' => ['nullable', 'string'],
        ]);

        $section->overrideRule()->updateOrCreate(
            ['section_id' => $section->id],
            [
                'policy' => $validated['policy'],
                'reason' => $validated['reason'],
                'set_by' => auth()->id(),
            ],
        );

        return redirect()->back()->with('success', '上書き制御ポリシーを更新しました');
    }
}
