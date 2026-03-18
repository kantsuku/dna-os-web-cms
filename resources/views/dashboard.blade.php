@extends('layouts.app')
@section('title', 'ダッシュボード')
@section('content')
<h1 class="text-2xl font-bold mb-8">ダッシュボード</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">管理サイト数</div>
        <div class="text-3xl font-bold text-indigo-600 mt-1">{{ $sites->count() }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">未公開の新世代</div>
        <div class="text-3xl font-bold text-yellow-600 mt-1">{{ $newGenerations }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">総ページ数</div>
        <div class="text-3xl font-bold text-green-600 mt-1">{{ $sites->sum('pages_count') }}</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-semibold">サイト一覧</h2>
        <a href="{{ route('sites.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">新規サイト</a>
    </div>
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
        <tbody class="divide-y">
            @forelse($sites as $site)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium">{{ $site->name }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $site->domain ?? '-' }}</td>
                <td class="px-6 py-4 text-sm">{{ $site->pages_count }}</td>
                <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full {{ $site->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ $site->status }}</span></td>
                <td class="px-6 py-4 text-right"><a href="{{ route('sites.show', $site) }}" class="text-indigo-600 hover:underline text-sm">管理</a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">まだサイトがありません</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($recentDeploys->isNotEmpty())
<div class="bg-white rounded-lg shadow mt-8">
    <div class="px-6 py-4 border-b"><h2 class="text-lg font-semibold">最近のデプロイ</h2></div>
    <ul class="divide-y">
        @foreach($recentDeploys as $d)
        <li class="px-6 py-3 flex justify-between items-center">
            <span class="text-sm">{{ $d->site->name }}</span>
            <span class="text-xs text-gray-500">{{ $d->deployed_at?->format('Y-m-d H:i') }}</span>
        </li>
        @endforeach
    </ul>
</div>
@endif
@endsection
