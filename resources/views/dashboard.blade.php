@extends('layouts.app')

@section('title', 'ダッシュボード')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">ダッシュボード</h1>
</div>

{{-- 統計カード --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">管理サイト数</div>
        <div class="text-3xl font-bold text-indigo-600 mt-1">{{ $sites->count() }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">承認待ち</div>
        <div class="text-3xl font-bold text-yellow-600 mt-1">{{ $pendingReviews }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">総ページ数</div>
        <div class="text-3xl font-bold text-green-600 mt-1">{{ $sites->sum('pages_count') }}</div>
    </div>
</div>

{{-- サイト一覧 --}}
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold">サイト一覧</h2>
        <a href="{{ route('sites.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">新規サイト</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">医院名</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ドメイン</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ページ数</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($sites as $site)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">{{ $site->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $site->domain ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $site->pages_count }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full
                                {{ $site->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $site->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $site->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ $site->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('sites.show', $site) }}" class="text-indigo-600 hover:underline text-sm">管理</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">まだサイトがありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 最近の公開 --}}
@if($recentPublishes->isNotEmpty())
<div class="bg-white rounded-lg shadow mt-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">最近の公開</h2>
    </div>
    <ul class="divide-y divide-gray-200">
        @foreach($recentPublishes as $pub)
            <li class="px-6 py-3 flex justify-between items-center">
                <span class="text-sm">{{ $pub->site->name }}</span>
                <span class="text-xs text-gray-500">{{ $pub->deployed_at?->format('Y-m-d H:i') }}</span>
            </li>
        @endforeach
    </ul>
</div>
@endif
@endsection
