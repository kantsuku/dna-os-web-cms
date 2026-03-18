@extends('layouts.app')
@section('title', '原稿取り込み - ' . $page->title)
@section('content')
<div class="mb-6">
    <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $page->title }}に戻る</a>
</div>

<h1 class="text-2xl font-bold mb-2">原稿取り込み</h1>
<p class="text-sm text-gray-500 mb-6">{{ $site->name }} / {{ $page->title }}</p>

<div class="bg-white rounded-lg shadow p-6 max-w-3xl" x-data="{ mode: 'url' }">
    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-6 text-sm">
        取り込んだHTMLは <code>com-section</code> タグを境界にセクション分割されます。
        ロック済みセクションは自動的にスキップされます。
    </div>

    {{-- モード切り替え --}}
    <div class="flex space-x-4 mb-6 border-b">
        <button @click="mode = 'url'" :class="mode === 'url' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'"
                class="pb-2 border-b-2 text-sm font-medium">URL取り込み</button>
        <button @click="mode = 'paste'" :class="mode === 'paste' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'"
                class="pb-2 border-b-2 text-sm font-medium">マークアップHTML直接入力</button>
    </div>

    <form method="POST" action="{{ route('sites.pages.import', [$site, $page]) }}">
        @csrf

        {{-- URL モード --}}
        <div x-show="mode === 'url'">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">原稿URL</label>
                <input type="url" name="source_url" value="{{ old('source_url') }}"
                    placeholder="https://docs.google.com/document/d/... または Google Drive URL"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <p class="text-xs text-gray-400 mt-1">Google Docs / Google Drive / GAS WebApp の URL に対応</p>
            </div>
        </div>

        {{-- マークアップHTML直接入力モード --}}
        <div x-show="mode === 'paste'" style="display:none">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">マークアップHTML</label>
                <textarea name="markup_text" rows="15"
                    class="w-full font-mono text-xs border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="<section class='com-section'>...</section>">{{ old('markup_text') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">clinic-page-generator の _markup.txt の内容を貼り付けてください</p>
            </div>
        </div>

        @if($page->currentGeneration && !empty($page->currentGeneration->sections))
            @php
                $lockedSections = collect($page->currentGeneration->sections)
                    ->filter(fn($s) => in_array($s['lock_status'] ?? 'unlocked', ['human_locked', 'system_locked']));
            @endphp
            @if($lockedSections->isNotEmpty())
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded mt-4 text-sm">
                    <strong>注意:</strong> 以下のセクションはロック中のため、再生成時にスキップされます。
                    <ul class="mt-1 ml-4 list-disc text-xs">
                        @foreach($lockedSections as $ls)
                            <li>{{ $ls['section_id'] }}: {{ $ls['heading'] ?: '(見出しなし)' }} ({{ $ls['lock_status'] }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif

        <div class="mt-6 flex space-x-3">
            <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded-md text-sm hover:bg-yellow-600">取り込み開始</button>
            <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md text-sm hover:bg-gray-300">キャンセル</a>
        </div>
    </form>
</div>
@endsection
