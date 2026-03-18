@extends('layouts.app')
@section('title', $site->name . ' - ページ一覧')
@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $site->name }}に戻る</a>
</div>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">{{ $site->name }} - ページ一覧</h1>
    <a href="{{ route('clinic.sites.pages.create', [$clinic, $site]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">ページ追加</a>
</div>

<div class="bg-white rounded-lg shadow">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">並び順</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">タイトル</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">スラッグ</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">タイプ</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">施術キー</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">世代</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($pages as $page)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-500">{{ $page->sort_order }}</td>
                <td class="px-6 py-4 font-medium">{{ $page->title }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">/{{ $page->slug }}</td>
                <td class="px-6 py-4 text-sm">{{ $page->page_type }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $page->treatment_key ?? '-' }}</td>
                <td class="px-6 py-4 text-sm">{{ $page->currentGeneration?->generation ?? '-' }}</td>
                <td class="px-6 py-4">
                    @php
                        $status = $page->currentGeneration?->status ?? $page->status ?? 'draft';
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-800',
                            'ready' => 'bg-blue-100 text-blue-800',
                            'published' => 'bg-green-100 text-green-800',
                        ];
                    @endphp
                    <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $status }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $page]) }}" class="text-indigo-600 hover:underline text-sm">詳細</a>
                    <a href="{{ route('clinic.sites.pages.edit', [$clinic, $site, $page]) }}" class="text-gray-600 hover:underline text-sm">編集</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">ページがまだありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
