@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="{{ route('sites.show', $site) }}" class="text-sm text-gray-500 hover:underline">&larr; {{ $site->name }}</a>
        <h1 class="text-2xl font-bold mt-1">{{ $page->title }}</h1>
        <p class="text-sm text-gray-500">{{ $page->slug }} / {{ $page->page_type }} / テンプレート: {{ $page->template_name }}</p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('sites.pages.preview', [$site, $page]) }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700" target="_blank">プレビュー</a>
        <a href="{{ route('sites.pages.edit', [$site, $page]) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">ページ設定</a>
    </div>
</div>

{{-- ページ情報 --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <span class="text-sm text-gray-500">ステータス</span>
        <div class="mt-1">
            <span class="inline-flex px-2 py-1 text-sm rounded-full
                {{ $page->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                {{ $page->status }}
            </span>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <span class="text-sm text-gray-500">バージョン</span>
        <div class="text-lg font-semibold mt-1">v{{ $page->publish_version }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <span class="text-sm text-gray-500">セクション数</span>
        <div class="text-lg font-semibold mt-1">{{ $page->sections->count() }}</div>
    </div>
</div>

{{-- セクション一覧 --}}
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold">セクション</h2>
    </div>

    {{-- セクション追加フォーム --}}
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50" x-data="{ open: false }">
        <button @click="open = !open" class="text-sm text-indigo-600 hover:underline">+ セクション追加</button>
        <form method="POST" action="{{ route('sites.pages.sections.store', [$site, $page]) }}" x-show="open" x-cloak class="mt-4 grid grid-cols-3 gap-4">
            @csrf
            <div>
                <input type="text" name="section_key" placeholder="section_key (例: hero, faq)" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <select name="content_source_type" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="dna_os">DNA-OS</option>
                    <option value="manual">手動入力</option>
                    <option value="exception">例外コンテンツ</option>
                    <option value="client_post">クライアント投稿</option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">追加</button>
            </div>
        </form>
    </div>

    {{-- セクションリスト --}}
    <div class="divide-y divide-gray-200">
        @forelse($page->sections as $section)
            <div class="px-6 py-4">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-mono font-semibold text-sm">{{ $section->section_key }}</span>
                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">{{ $section->content_source_type }}</span>
                            @if($section->is_human_edited)
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700">修正済み</span>
                            @endif
                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-indigo-50 text-indigo-600">{{ $section->override_policy }}</span>
                        </div>

                        {{-- 最新バリアント --}}
                        @php $latestVariant = $section->variants->first(); @endphp
                        @if($latestVariant)
                            <div class="mt-2 text-sm text-gray-600 line-clamp-2">
                                {{ Str::limit(strip_tags($latestVariant->content_html), 200) }}
                            </div>
                            <div class="mt-1 text-xs text-gray-400">
                                v{{ $latestVariant->version }} / {{ $latestVariant->source_type }} / {{ $latestVariant->status }}
                            </div>
                        @else
                            <div class="mt-2 text-sm text-gray-400">コンテンツなし</div>
                        @endif
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('sections.edit', $section) }}" class="text-indigo-600 hover:underline text-sm">編集</a>
                        <a href="{{ route('sections.show', $section) }}" class="text-gray-500 hover:underline text-sm">詳細</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-8 text-center text-gray-500">セクションがありません</div>
        @endforelse
    </div>
</div>
@endsection
