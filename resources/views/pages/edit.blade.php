@extends('layouts.app')

@section('title', $page->title . ' - 設定')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="text-sm text-gray-500 hover:underline">&larr; {{ $page->title }}</a>
    <h1 class="text-2xl font-bold mt-1 mb-6">ページ設定</h1>

    <form method="POST" action="{{ route('sites.pages.update', [$site, $page]) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">タイトル</label>
            <input type="text" name="title" value="{{ old('title', $page->title) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ページ種別</label>
                <select name="page_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @foreach(['top', 'lower', 'blog', 'news', 'exception'] as $type)
                        <option value="{{ $type }}" {{ $page->page_type === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @foreach(['draft', 'pending_review', 'approved', 'published', 'archived'] as $status)
                        <option value="{{ $status }}" {{ $page->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">テンプレート</label>
            <select name="template_name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                @foreach(['default', 'treatment', 'about', 'staff', 'access', 'generic', 'blog_index', 'blog_single'] as $tmpl)
                    <option value="{{ $tmpl }}" {{ $page->template_name === $tmpl ? 'selected' : '' }}>{{ $tmpl }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
            <textarea name="meta_description" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('meta_description', $page->meta_description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">表示順序</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}"
                   class="w-32 px-3 py-2 border border-gray-300 rounded-md">
        </div>

        <div class="flex justify-end space-x-3 pt-4">
            <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm">キャンセル</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">保存</button>
        </div>
    </form>
</div>
@endsection
