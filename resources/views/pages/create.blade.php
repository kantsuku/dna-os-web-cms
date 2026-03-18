@extends('layouts.app')
@section('title', $site->name . ' - ページ作成')
@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $site->name }}に戻る</a>
</div>
<h1 class="text-2xl font-bold mb-6">ページ作成</h1>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('clinic.sites.pages.store', [$clinic, $site]) }}">
        @csrf

        <div class="space-y-5">
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">スラッグ</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required placeholder="top, about, treatment/implant"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('slug') border-red-500 @enderror">
                @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">タイトル</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="page_type" class="block text-sm font-medium text-gray-700 mb-1">ページタイプ</label>
                <select name="page_type" id="page_type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="top" @selected(old('page_type') === 'top')>トップ</option>
                    <option value="treatment" @selected(old('page_type') === 'treatment')>施術ページ</option>
                    <option value="about" @selected(old('page_type') === 'about')>医院紹介</option>
                    <option value="access" @selected(old('page_type') === 'access')>アクセス</option>
                    <option value="other" @selected(old('page_type') === 'other')>その他</option>
                </select>
                @error('page_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="treatment_key" class="block text-sm font-medium text-gray-700 mb-1">施術キー</label>
                <input type="text" name="treatment_key" id="treatment_key" value="{{ old('treatment_key') }}" placeholder="施術ページの場合のみ入力"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('treatment_key') border-red-500 @enderror">
                @error('treatment_key')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">並び順</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                    class="w-32 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('sort_order') border-red-500 @enderror">
                @error('sort_order')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md text-sm hover:bg-indigo-700">作成</button>
            <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md text-sm hover:bg-gray-300">キャンセル</a>
        </div>
    </form>
</div>
@endsection
