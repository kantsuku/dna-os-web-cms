@extends('layouts.app')

@section('title', '微調整 - ' . $section->section_key)

@section('content')
@php $site = $section->page->site; $page = $section->page; @endphp

<div class="mb-6">
    <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="text-sm text-gray-500 hover:underline">&larr; {{ $page->title }}</a>
    <h1 class="text-2xl font-bold mt-1">微調整: <span class="font-mono">{{ $section->section_key }}</span></h1>
</div>

<form method="POST" action="{{ route('sections.update', $section) }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- 原本（読み取り専用） --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-semibold text-gray-500 mb-3">原本（DNA-OS）</h2>
            <div class="bg-gray-50 p-4 rounded text-sm min-h-[300px] overflow-auto">
                @if($activeVariant?->original_content)
                    {!! $activeVariant->original_content !!}
                @else
                    <span class="text-gray-400">原本データなし</span>
                @endif
            </div>
        </div>

        {{-- 編集エリア --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-sm font-semibold text-gray-500 mb-3">編集</h2>
            <textarea name="content_html" rows="15"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 font-mono text-sm">{{ old('content_html', $activeVariant?->content_html ?? '') }}</textarea>
            @error('content_html') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">修正理由</label>
        <input type="text" name="edit_reason" value="{{ old('edit_reason') }}" placeholder="例: 院長メッセージの表現を修正"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
    </div>

    <div class="flex justify-end space-x-3 mt-6">
        <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm">キャンセル</a>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">保存（新バリアント作成）</button>
    </div>
</form>
@endsection
