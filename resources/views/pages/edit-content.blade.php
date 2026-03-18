@extends('layouts.app')
@section('title', '微細編集 - ' . $page->title)
@section('content')
<div class="mb-6">
    <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $page->title }}に戻る</a>
</div>

<h1 class="text-2xl font-bold mb-2">微細編集</h1>
<p class="text-sm text-gray-500 mb-6">{{ $site->name }} / {{ $page->title }} / 世代 #{{ $generation->generation }}</p>

<form method="POST" action="{{ route('sites.pages.update-content', [$site, $page]) }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- 左: content_html（読み取り専用） --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">元のコンテンツ（content_html）</h2>
                <span class="text-xs text-gray-400">読み取り専用</span>
            </div>
            <div class="p-4">
                <textarea readonly
                    class="w-full h-[500px] font-mono text-xs border-gray-300 rounded-md bg-gray-50 resize-y">{{ $generation->content_html }}</textarea>
            </div>
        </div>

        {{-- 右: final_html（編集可能） --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b bg-indigo-50">
                <h2 class="text-sm font-semibold text-gray-700">最終HTML（final_html）</h2>
                <span class="text-xs text-gray-400">編集して保存してください</span>
            </div>
            <div class="p-4">
                <textarea name="final_html" id="final_html"
                    class="w-full h-[500px] font-mono text-xs border-gray-300 rounded-md resize-y focus:ring-indigo-500 focus:border-indigo-500 @error('final_html') border-red-500 @enderror">{{ old('final_html', $generation->final_html ?? $generation->content_html) }}</textarea>
                @error('final_html')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- patch_reason --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6 max-w-2xl">
        <label for="patch_reason" class="block text-sm font-medium text-gray-700 mb-1">修正理由</label>
        <input type="text" name="patch_reason" id="patch_reason" value="{{ old('patch_reason') }}"
            placeholder="例: 誤字修正、料金表更新、表現の微調整"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('patch_reason') border-red-500 @enderror">
        @error('patch_reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex space-x-3">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md text-sm hover:bg-indigo-700">保存</button>
        <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md text-sm hover:bg-gray-300">キャンセル</a>
    </div>
</form>
@endsection
