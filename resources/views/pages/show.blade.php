@extends('layouts.app')
@section('title', $page->title . ' - ' . $site->name)
@section('content')

<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold">{{ $page->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">/{{ $page->slug }} | {{ $page->page_type }}
            @if($page->parent) &larr; {{ $page->parent->title }} @endif
        </p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('clinic.sites.pages.import', [$clinic, $site, $page]) }}" class="bg-yellow-500 text-white px-3 py-2 rounded text-sm hover:bg-yellow-600">原稿取り込み</a>
        <a href="{{ route('clinic.sites.pages.preview', [$clinic, $site, $page]) }}" class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700" target="_blank">プレビュー</a>
        <a href="{{ route('clinic.sites.pages.edit', [$clinic, $site, $page]) }}" class="bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-300">設定</a>
    </div>
</div>

{{-- ===== コンテンツ表示＆編集 ===== --}}
@if($page->currentGeneration)
    @php
        $gen = $page->currentGeneration;
        $hasSections = !empty($gen->sections);
    @endphp

    {{-- セクション単位表示（com-CSS適用） --}}
    @if($hasSections)
        <div class="space-y-4 mb-8">
            @foreach($gen->sections as $i => $section)
                @php
                    $lockStatus = $section['lock_status'] ?? 'unlocked';
                    $isLocked = in_array($lockStatus, ['human_locked', 'system_locked']);
                @endphp
                <div class="group relative bg-white rounded-lg shadow overflow-hidden"
                     x-data="{ editing: false }">

                    {{-- セクションヘッダー（ホバー時に表示） --}}
                    <div class="absolute top-0 left-0 right-0 z-10 bg-white/90 backdrop-blur px-4 py-2 border-b flex justify-between items-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="flex items-center space-x-2 text-xs">
                            <span class="font-mono text-gray-400">{{ $section['section_id'] }}</span>
                            <span class="font-medium text-gray-700">{{ $section['heading'] ?: '(見出しなし)' }}</span>
                            @if($isLocked)
                                <span class="px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700">ロック中</span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            {{-- ロック --}}
                            <form method="POST" action="{{ route('clinic.sites.pages.sections.lock', [$clinic, $site, $page, $section['section_id']]) }}" class="inline">
                                @csrf
                                @if(!$isLocked)
                                    <input type="hidden" name="lock_status" value="human_locked">
                                    <button type="submit" class="text-xs text-yellow-600 hover:text-yellow-800">ロック</button>
                                @else
                                    <input type="hidden" name="lock_status" value="unlocked">
                                    <button type="submit" class="text-xs text-green-600 hover:text-green-800">解除</button>
                                @endif
                            </form>
                            <a href="{{ route('clinic.sites.pages.sections.edit', [$clinic, $site, $page, $section['section_id']]) }}"
                               class="text-xs bg-indigo-600 text-white px-2 py-1 rounded hover:bg-indigo-700">編集</a>
                        </div>
                    </div>

                    {{-- コンテンツ表示（com-CSS適用のiframe） --}}
                    <iframe srcdoc="@php
                        $css = app(\App\Services\DesignCssService::class)->generateCss($site);
                        $escapedCss = e($css);
                        $escapedHtml = e($section['content_html'] ?? '');
                    @endphp<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width'><link href='https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap' rel='stylesheet'><style>{{ $escapedCss }}body{margin:0;padding:0;}</style></head><body>{{ $escapedHtml }}</body></html>"
                        class="w-full border-0"
                        style="min-height: 200px;"
                        onload="this.style.height = this.contentDocument.body.scrollHeight + 'px'"
                    ></iframe>
                </div>
            @endforeach
        </div>
    @else
        {{-- セクションなし（HTML一括表示） --}}
        <div class="bg-white rounded-lg shadow mb-8 overflow-hidden">
            <div class="px-4 py-2 border-b bg-gray-50 flex justify-between items-center">
                <span class="text-sm text-gray-500">世代 {{ $gen->generation }} | {{ $gen->status }}</span>
                <a href="{{ route('clinic.sites.pages.edit-content', [$clinic, $site, $page]) }}"
                   class="text-xs bg-indigo-600 text-white px-2 py-1 rounded hover:bg-indigo-700">編集</a>
            </div>
            <iframe srcdoc="@php
                $css = app(\App\Services\DesignCssService::class)->generateCss($site);
                $escapedCss = e($css);
                $escapedHtml = e($gen->final_html ?? $gen->content_html ?? '');
            @endphp<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width'><link href='https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap' rel='stylesheet'><style>{{ $escapedCss }}body{margin:0;padding:0;}</style></head><body>{{ $escapedHtml }}</body></html>"
                class="w-full border-0"
                style="min-height: 300px;"
                onload="this.style.height = this.contentDocument.body.scrollHeight + 'px'"
            ></iframe>
        </div>
    @endif

    {{-- 子ページ --}}
    @if($page->children->isNotEmpty())
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-3 border-b bg-gray-50">
                <h2 class="text-sm font-semibold">子ページ</h2>
            </div>
            <div class="divide-y">
                @foreach($page->children as $child)
                    <a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $child]) }}" class="block px-6 py-3 hover:bg-gray-50 flex justify-between items-center">
                        <span class="text-sm font-medium">{{ $child->title }}</span>
                        <span class="text-xs text-gray-400">/{{ $child->slug }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 世代履歴（折りたたみ） --}}
    <div class="bg-white rounded-lg shadow" x-data="{ open: false }">
        <button @click="open = !open" class="w-full px-6 py-3 border-b flex justify-between items-center hover:bg-gray-50">
            <h2 class="text-sm font-semibold">世代履歴</h2>
            <span class="text-xs text-gray-400" x-text="open ? '閉じる' : '{{ $page->generations->count() }}件'"></span>
        </button>
        <div x-show="open" style="display:none" class="divide-y">
            @foreach($page->generations as $g)
                <div class="px-6 py-3 flex justify-between items-center text-sm {{ $g->id === $page->current_generation_id ? 'bg-indigo-50' : '' }}">
                    <div class="flex items-center space-x-3">
                        <span class="font-mono text-gray-400">#{{ $g->generation }}</span>
                        <span class="text-gray-500">{{ $g->source }}</span>
                        @php $gs = ['draft'=>'gray','received'=>'yellow','ready'=>'blue','approved'=>'indigo','published'=>'green','superseded'=>'gray'][$g->status] ?? 'gray'; @endphp
                        <span class="px-1.5 py-0.5 rounded text-xs bg-{{ $gs }}-100 text-{{ $gs }}-700">{{ $g->status }}</span>
                        @if($g->id === $page->current_generation_id) <span class="text-xs text-indigo-600 font-medium">現在</span> @endif
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-gray-400">{{ $g->created_at->format('m/d H:i') }}</span>
                        @if($g->status === 'draft')
                            <form method="POST" action="{{ route('clinic.sites.pages.generations.ready', [$clinic, $site, $page, $g]) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs bg-blue-500 text-white px-2 py-0.5 rounded hover:bg-blue-600">readyにする</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@else
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        まだコンテンツがありません。
        <div class="mt-4">
            <a href="{{ route('clinic.sites.pages.import', [$clinic, $site, $page]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">原稿を取り込む</a>
        </div>
    </div>
@endif
@endsection
