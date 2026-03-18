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

<form method="POST" action="{{ route('clinic.sites.pages.sections.update', [$clinic, $site, $page, $sectionId]) }}" x-data="{ html: @js($section['content_html'] ?? '') }">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- エディタ --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">HTML</label>
            <textarea name="content_html" rows="25"
                      class="w-full font-mono text-sm border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500"
                      x-model="html"
            >{{ $section['content_html'] ?? '' }}</textarea>
        </div>

        {{-- プレビュー --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">プレビュー</label>
            <div class="border border-gray-200 rounded-lg p-4 min-h-[500px] bg-white overflow-auto">
                <div x-html="html" class="prose prose-sm max-w-none"></div>
            </div>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-lg border border-gray-200 p-4">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">変更理由 <span class="text-red-500">*</span></label>
            <input type="text" name="patch_reason" required class="w-full border-gray-300 rounded text-sm" placeholder="例：診療方針の変更に伴い説明文を修正">
        </div>

        <div class="mb-4">
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="lock_after_edit" value="1" class="rounded border-gray-300 text-indigo-600" checked>
                <span class="text-sm text-gray-700">編集後にこのセクションをロックする（AI再生成時にスキップされます）</span>
            </label>
        </div>

        <div class="flex space-x-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded text-sm hover:bg-indigo-700">保存する</button>
            <a href="{{ route('clinic.sites.pages.sections', [$clinic, $site, $page]) }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded text-sm hover:bg-gray-200">キャンセル</a>
        </div>
    </div>
</form>
@endsection
