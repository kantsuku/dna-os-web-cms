@extends('layouts.app')
@section('title', $site->name)
@section('content')
<div class="mb-6">
    <a href="{{ route('sites.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; サイト一覧に戻る</a>
</div>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold">{{ $site->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $site->domain ?? 'ドメイン未設定' }}</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('sites.edit', $site) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300">サイト設定</a>
        <a href="{{ route('sites.publish.index', $site) }}" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">公開管理</a>
        <a href="{{ route('design.site', $site) }}" class="bg-purple-600 text-white px-4 py-2 rounded-md text-sm hover:bg-purple-700">デザイン設定</a>
    </div>
</div>

{{-- ページ一覧 --}}
<div class="bg-white rounded-lg shadow mb-8">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-semibold">ページ一覧</h2>
        <a href="{{ route('sites.pages.create', $site) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">ページ追加</a>
    </div>
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">並び順</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">タイトル</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">スラッグ</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">タイプ</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">世代</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($site->pages as $page)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-500">{{ $page->sort_order }}</td>
                <td class="px-6 py-4 font-medium">{{ $page->title }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">/{{ $page->slug }}</td>
                <td class="px-6 py-4 text-sm">{{ $page->page_type }}</td>
                <td class="px-6 py-4 text-sm">
                    {{ $page->currentGeneration?->generation ?? '-' }}
                </td>
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
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="text-indigo-600 hover:underline text-sm">管理</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">ページがまだありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- デプロイ履歴（最新5件） --}}
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-semibold">デプロイ履歴（最新5件）</h2>
    </div>
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">日時</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">実行者</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">備考</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($site->deployRecords->take(5) as $record)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">{{ $record->deployed_at?->format('Y-m-d H:i') ?? $record->created_at->format('Y-m-d H:i') }}</td>
                <td class="px-6 py-4">
                    @php
                        $deployColors = [
                            'success' => 'bg-green-100 text-green-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                        ];
                    @endphp
                    <span class="px-2 py-1 text-xs rounded-full {{ $deployColors[$record->deploy_status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $record->deploy_status }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $record->deployer?->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $record->rollback_of ? 'ロールバック' : '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">デプロイ履歴はありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
