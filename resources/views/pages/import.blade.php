@extends('layouts.app')
@section('title', '原稿取り込み - ' . $page->title)
@section('content')
<div class="mb-6">
    <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $page->title }}に戻る</a>
</div>

<h1 class="text-2xl font-bold mb-2">原稿取り込み</h1>
<p class="text-sm text-gray-500 mb-6">{{ $site->name }} / {{ $page->title }}</p>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-6 text-sm">
        Google Docs URLまたはDrive URLを貼り付けてください。原稿のHTMLが自動的に取り込まれ、新しい世代として保存されます。
    </div>

    <form method="POST" action="{{ route('sites.pages.import', [$site, $page]) }}">
        @csrf

        <div class="space-y-5">
            <div>
                <label for="source_url" class="block text-sm font-medium text-gray-700 mb-1">原稿URL</label>
                <input type="url" name="source_url" id="source_url" value="{{ old('source_url') }}" required
                    placeholder="https://docs.google.com/document/d/..."
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('source_url') border-red-500 @enderror">
                @error('source_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded-md text-sm hover:bg-yellow-600">取り込み開始</button>
            <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md text-sm hover:bg-gray-300">キャンセル</a>
        </div>
    </form>
</div>
@endsection
