@extends('layouts.app')

@section('title', $site->name . ' - ページ一覧')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="{{ route('sites.show', $site) }}" class="text-sm text-gray-500 hover:underline">&larr; {{ $site->name }}</a>
        <h1 class="text-2xl font-bold mt-1">ページ一覧</h1>
    </div>
    <a href="{{ route('sites.pages.create', $site) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">ページ追加</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">順序</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">タイトル</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">種別</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">セクション数</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($pages as $page)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $page->sort_order }}</td>
                    <td class="px-6 py-4 font-medium">{{ $page->title }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $page->slug }}</td>
                    <td class="px-6 py-4 text-xs">{{ $page->page_type }}</td>
                    <td class="px-6 py-4 text-sm">{{ $page->sections_count }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs rounded-full
                            {{ $page->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $page->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="text-indigo-600 hover:underline text-sm">管理</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
