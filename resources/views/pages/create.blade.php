@extends('layouts.app')

@section('title', $site->name . ' - ページ追加')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('sites.show', $site) }}" class="text-sm text-gray-500 hover:underline">&larr; {{ $site->name }}</a>
    <h1 class="text-2xl font-bold mt-1 mb-6">ページ追加</h1>

    <form method="POST" action="{{ route('sites.pages.store', $site) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">タイトル</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug（URLパス）</label>
            <input type="text" name="slug" value="{{ old('slug') }}" required placeholder="/implant"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ページ種別</label>
            <select name="page_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="top">TOP</option>
                <option value="lower" selected>下層ページ</option>
                <option value="blog">ブログ</option>
                <option value="news">お知らせ</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">テンプレート</label>
            <select name="template_name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="default">TOP - default</option>
                <option value="treatment">下層 - 診療科目</option>
                <option value="about">下層 - 医院紹介</option>
                <option value="staff">下層 - スタッフ</option>
                <option value="access">下層 - アクセス</option>
                <option value="generic" selected>下層 - 汎用</option>
                <option value="blog_index">ブログ一覧</option>
                <option value="blog_single">ブログ記事</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
            <textarea name="meta_description" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">{{ old('meta_description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">表示順序</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                   class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="flex justify-end space-x-3 pt-4">
            <a href="{{ route('sites.show', $site) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">キャンセル</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">作成</button>
        </div>
    </form>
</div>
@endsection
