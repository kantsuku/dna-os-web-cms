@extends('layouts.app')
@section('title', $page->title . ' - セクション編集')

@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.pages.sections', [$clinic, $site, $page]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; セクション一覧に戻る</a>
</div>

<h1 class="text-2xl font-bold text-gray-900 mb-2">セクション編集</h1>
<p class="text-sm text-gray-500 mb-6">
    {{ $page->title }} / {{ $sectionId }} — {{ $section['heading'] ?: '(見出しなし)' }}
    @if(($section['lock_status'] ?? 'unlocked') !== 'unlocked')
        <span class="ml-2 px-2 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700">{{ $section['lock_status'] }}</span>
    @endif
</p>

<form method="POST" action="{{ route('clinic.sites.pages.sections.update', [$clinic, $site, $page, $sectionId]) }}"
      x-data="sectionEditor(@js($section['content_html'] ?? ''))">
    @csrf
    @method('PUT')

    {{-- タブ切り替え --}}
    <div class="flex space-x-1 mb-3 border-b">
        <button type="button" @click="mode = 'visual'"
                :class="mode === 'visual' ? 'border-indigo-500 text-indigo-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">
            ビジュアル
        </button>
        <button type="button" @click="mode = 'html'"
                :class="mode === 'html' ? 'border-indigo-500 text-indigo-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">
            HTML
        </button>
        <button type="button" @click="mode = 'preview'"
                :class="mode === 'preview' ? 'border-indigo-500 text-indigo-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">
            プレビュー
        </button>
    </div>

    {{-- hidden input（実際の送信値） --}}
    <input type="hidden" name="content_html" :value="html">

    {{-- ビジュアルモード（WYSIWYG） --}}
    <div x-show="mode === 'visual'" style="display:none">
        <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
            <div id="wysiwyg-editor"
                 contenteditable="true"
                 class="p-6 min-h-[500px] prose prose-sm max-w-none focus:outline-none"
                 @input="html = $el.innerHTML"
                 x-html="html">
            </div>
        </div>
    </div>

    {{-- HTMLモード --}}
    <div x-show="mode === 'html'" style="display:none">
        <textarea x-model="html" rows="25"
                  class="w-full font-mono text-sm border-gray-300 rounded-lg p-4 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-900 text-green-400">
        </textarea>
    </div>

    {{-- プレビューモード --}}
    <div x-show="mode === 'preview'" style="display:none">
        <div class="border border-gray-200 rounded-lg p-6 bg-white min-h-[500px]">
            <div x-html="html" class="prose prose-sm max-w-none"></div>
        </div>
    </div>

    {{-- 保存オプション --}}
    <div class="mt-6 bg-gray-50 rounded-lg border border-gray-200 p-4">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">変更理由 <span class="text-red-500">*</span></label>
            <input type="text" name="patch_reason" required class="w-full border-gray-300 rounded text-sm"
                   placeholder="例：診療方針の変更に伴い説明文を修正">
        </div>

        <div class="mb-4">
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="lock_after_edit" value="1" class="rounded border-gray-300 text-indigo-600" checked>
                <span class="text-sm text-gray-700">編集後にこのセクションをロックする</span>
                <span class="text-xs text-gray-400">（AI再生成時にスキップされます）</span>
            </label>
        </div>

        <div class="flex space-x-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded text-sm hover:bg-indigo-700">保存する</button>
            <a href="{{ route('clinic.sites.pages.sections', [$clinic, $site, $page]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded text-sm hover:bg-gray-300">キャンセル</a>
        </div>
    </div>
</form>

<script>
function sectionEditor(initialHtml) {
    return {
        mode: 'visual',
        html: initialHtml,
    }
}
</script>
@endsection
