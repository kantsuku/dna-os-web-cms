@extends('layouts.app')

@section('title', $site->name . ' - 公開管理')

@section('content')
<div class="mb-6">
    <a href="{{ route('sites.show', $site) }}" class="text-sm text-gray-500 hover:underline">&larr; {{ $site->name }}</a>
    <h1 class="text-2xl font-bold mt-1">公開管理</h1>
</div>

{{-- デプロイ対象ページ --}}
<div class="bg-white rounded-lg shadow mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">デプロイ対象ページ</h2>
        <p class="text-sm text-gray-500">approved / published ステータスのページが対象です</p>
    </div>

    @if($approvedPages->isNotEmpty())
        <form method="POST" action="{{ route('sites.publish.deploy', $site) }}">
            @csrf
            <div class="divide-y divide-gray-200">
                @foreach($approvedPages as $page)
                    <label class="px-6 py-3 flex items-center hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="page_ids[]" value="{{ $page->id }}" checked
                               class="rounded border-gray-300 text-indigo-600 mr-3">
                        <div>
                            <span class="font-medium text-sm">{{ $page->title }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ $page->slug }}</span>
                        </div>
                        <span class="ml-auto inline-flex px-2 py-0.5 text-xs rounded-full
                            {{ $page->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $page->status }}
                        </span>
                    </label>
                @endforeach
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700"
                        onclick="return confirm('デプロイを実行しますか？')">
                    デプロイ実行
                </button>
            </div>
        </form>
    @else
        <div class="px-6 py-8 text-center text-gray-500">デプロイ対象のページがありません</div>
    @endif
</div>

{{-- 公開履歴 --}}
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">公開履歴</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">実行者</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">デプロイ日時</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($history as $record)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono">#{{ $record->id }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full
                                {{ $record->deploy_status === 'success' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $record->deploy_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $record->deploy_status === 'rolled_back' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ in_array($record->deploy_status, ['pending', 'building', 'deploying']) ? 'bg-blue-100 text-blue-800' : '' }}">
                                {{ $record->deploy_status }}
                            </span>
                            @if($record->rollback_of)
                                <span class="text-xs text-gray-400 ml-1">(ロールバック元: #{{ $record->rollback_of }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $record->deployer?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $record->deployed_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($record->deploy_status === 'success' && $record->snapshot_path)
                                <form method="POST" action="{{ route('sites.publish.rollback', [$site, $record]) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-yellow-600 hover:underline text-sm"
                                            onclick="return confirm('この状態にロールバックしますか？')">
                                        ロールバック
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">公開履歴なし</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4">
        {{ $history->links() }}
    </div>
</div>
@endsection
