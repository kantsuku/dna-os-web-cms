@extends('layouts.app')
@section('title', $page->title . ' - ' . $site->name)
@section('content')

<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold">{{ $page->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">/{{ $page->slug }}
            @if($page->parent) &larr; {{ $page->parent->title }} @endif
        </p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('clinic.sites.pages.import', [$clinic, $site, $page]) }}" class="bg-yellow-500 text-white px-3 py-2 rounded text-sm hover:bg-yellow-600">原稿取り込み</a>
        <a href="{{ route('clinic.sites.pages.preview', [$clinic, $site, $page]) }}" class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700" target="_blank">全体プレビュー</a>
        <a href="{{ route('clinic.sites.pages.edit', [$clinic, $site, $page]) }}" class="bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-300">設定</a>
    </div>
</div>

@if($page->currentGeneration)
    @php $gen = $page->currentGeneration; $hasSections = !empty($gen->sections); @endphp

    {{-- ===== セクション単位表示 ===== --}}
    @if($hasSections)
        <div class="space-y-4 mb-8">
            @foreach($gen->sections as $section)
                @php
                    $sid = $section['section_id'];
                    $isLocked = in_array($section['lock_status'] ?? 'unlocked', ['human_locked', 'system_locked']);
                @endphp
                <div class="group relative bg-white rounded-lg shadow overflow-hidden">
                    {{-- ホバー操作バー --}}
                    <div class="absolute top-2 right-2 z-10 flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        @if(!$isLocked)
                            <form method="POST" action="{{ route('clinic.sites.pages.sections.lock', [$clinic, $site, $page, $sid]) }}" class="inline">
                                @csrf
                                <input type="hidden" name="lock_status" value="human_locked">
                                <button class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs hover:bg-yellow-200 shadow-sm">ロック</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('clinic.sites.pages.sections.lock', [$clinic, $site, $page, $sid]) }}" class="inline">
                                @csrf
                                <input type="hidden" name="lock_status" value="unlocked">
                                <button class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs hover:bg-green-200 shadow-sm">解除</button>
                            </form>
                        @endif
                        <a href="{{ route('clinic.sites.pages.sections.edit', [$clinic, $site, $page, $sid]) }}"
                           class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700 shadow-sm">編集</a>
                    </div>

                    {{-- セクションラベル --}}
                    <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="bg-black/60 text-white px-2 py-0.5 rounded text-xs">
                            {{ $sid }} {{ $section['heading'] ? '— '.$section['heading'] : '' }}
                            @if($isLocked) 🔒 @endif
                        </span>
                    </div>

                    {{-- コンテンツ（iframe src方式 = CSS確実適用） --}}
                    <iframe src="{{ route('clinic.sites.pages.section-frame', [$clinic, $site, $page, $sid]) }}"
                            class="w-full border-0"
                            style="min-height: 120px;"
                            onload="this.style.height = this.contentDocument.documentElement.scrollHeight + 'px'"
                    ></iframe>
                </div>
            @endforeach
        </div>
    @else
        {{-- セクションなし（全体1枚） --}}
        <div class="bg-white rounded-lg shadow mb-8 overflow-hidden">
            <div class="px-4 py-2 border-b bg-gray-50 flex justify-between items-center">
                <span class="text-sm text-gray-500">世代 {{ $gen->generation }} | {{ $gen->status }}</span>
                <a href="{{ route('clinic.sites.pages.edit-content', [$clinic, $site, $page]) }}"
                   class="text-xs bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">編集</a>
            </div>
            <iframe src="{{ route('clinic.sites.pages.content-frame', [$clinic, $site, $page]) }}"
                    class="w-full border-0"
                    style="min-height: 300px;"
                    onload="this.style.height = this.contentDocument.documentElement.scrollHeight + 'px'"
            ></iframe>
        </div>
    @endif

    {{-- 子ページ --}}
    @if($page->children->isNotEmpty())
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-5 py-3 border-b bg-gray-50"><h2 class="text-sm font-semibold">子ページ</h2></div>
            <div class="divide-y">
                @foreach($page->children as $child)
                    <a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $child]) }}" class="flex justify-between items-center px-5 py-3 hover:bg-gray-50">
                        <span class="text-sm font-medium">{{ $child->title }}</span>
                        <span class="text-xs text-gray-400">/{{ $child->slug }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 世代履歴 --}}
    <details class="bg-white rounded-lg shadow">
        <summary class="px-5 py-3 cursor-pointer hover:bg-gray-50 text-sm font-semibold flex justify-between items-center">
            世代履歴 <span class="text-xs text-gray-400 font-normal">{{ $page->generations->count() }}件</span>
        </summary>
        <div class="divide-y border-t">
            @foreach($page->generations as $g)
                <div class="px-5 py-2.5 flex justify-between items-center text-sm {{ $g->id === $page->current_generation_id ? 'bg-indigo-50' : '' }}">
                    <div class="flex items-center space-x-3">
                        <span class="font-mono text-gray-400">#{{ $g->generation }}</span>
                        <span class="text-gray-500">{{ $g->source }}</span>
                        @php $gs = ['draft'=>'gray','ready'=>'blue','published'=>'green','superseded'=>'gray'][$g->status] ?? 'gray'; @endphp
                        <span class="px-1.5 py-0.5 rounded text-xs bg-{{ $gs }}-100 text-{{ $gs }}-700">{{ $g->status }}</span>
                        @if($g->id === $page->current_generation_id) <span class="text-xs text-indigo-600 font-medium">現在</span> @endif
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-gray-400">{{ $g->created_at->format('m/d H:i') }}</span>
                        @if($g->status === 'draft')
                            <form method="POST" action="{{ route('clinic.sites.pages.generations.ready', [$clinic, $site, $page, $g]) }}" class="inline">
                                @csrf
                                <button class="text-xs bg-blue-500 text-white px-2 py-0.5 rounded hover:bg-blue-600">公開準備へ</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </details>
@else
    <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
        <p class="text-lg mb-4">まだコンテンツがありません</p>
        <a href="{{ route('clinic.sites.pages.import', [$clinic, $site, $page]) }}" class="bg-indigo-600 text-white px-6 py-3 rounded text-sm hover:bg-indigo-700">原稿を取り込む</a>
    </div>
@endif
@endsection
