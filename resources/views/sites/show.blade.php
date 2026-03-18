@extends('layouts.app')

@section('title', $site->name)

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="{{ route('sites.index') }}" class="text-sm text-gray-500 hover:underline">&larr; サイト一覧</a>
        <h1 class="text-2xl font-bold mt-1">{{ $site->name }}</h1>
        <p class="text-sm text-gray-500">{{ $site->domain ?? 'ドメイン未設定' }} / clinic_id: {{ $site->clinic_id }}</p>
    </div>
    <div class="flex space-x-2">
        <form method="POST" action="{{ route('sites.sync', $site) }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">DNA-OS同期</button>
        </form>
        <a href="{{ route('sites.publish.index', $site) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">公開管理</a>
        <a href="{{ route('sites.edit', $site) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">設定</a>
    </div>
</div>

{{-- サイト情報 --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">ステータス</div>
        <span class="inline-flex px-2 py-1 text-sm rounded-full mt-1
            {{ $site->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
            {{ $site->status }}
        </span>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">テンプレート</div>
        <div class="text-lg font-semibold mt-1">{{ $site->template_set }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">XServer</div>
        <div class="text-sm mt-1">{{ $site->xserver_host ?? '未設定' }}</div>
        @if($site->xserver_host)
            <form method="POST" action="{{ route('sites.test-ftp', $site) }}" class="mt-2">
                @csrf
                <button type="submit" class="text-xs text-indigo-600 hover:underline">接続テスト</button>
            </form>
        @endif
    </div>
</div>

{{-- ページ一覧 --}}
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold">ページ一覧</h2>
        <a href="{{ route('sites.pages.create', $site) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">ページ追加</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">タイトル</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">種別</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($site->pages as $page)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">{{ $page->title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $page->slug }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">{{ $page->page_type }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full
                                {{ $page->status === 'published' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $page->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $page->status === 'approved' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $page->status === 'pending_review' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                {{ $page->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="text-indigo-600 hover:underline text-sm">管理</a>
                            <a href="{{ route('sites.pages.preview', [$site, $page]) }}" class="text-gray-500 hover:underline text-sm" target="_blank">プレビュー</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">まだページがありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 同期ログ --}}
@if($site->syncLogs->isNotEmpty())
<div class="bg-white rounded-lg shadow mt-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">最近の同期ログ</h2>
    </div>
    <ul class="divide-y divide-gray-200">
        @foreach($site->syncLogs as $log)
            <li class="px-6 py-3 flex justify-between items-center">
                <div>
                    <span class="inline-flex px-2 py-1 text-xs rounded-full
                        {{ $log->status === 'success' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $log->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $log->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ $log->status }}
                    </span>
                    <span class="text-sm ml-2">更新: {{ $log->sections_updated }} / スキップ: {{ $log->sections_skipped }}</span>
                </div>
                <span class="text-xs text-gray-500">{{ $log->started_at?->format('Y-m-d H:i') }}</span>
            </li>
        @endforeach
    </ul>
</div>
@endif
@endsection
