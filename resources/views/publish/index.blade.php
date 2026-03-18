@extends('layouts.app')
@section('title', '公開管理 - ' . $site->name)
@section('content')
<div class="mb-6">
    <a href="{{ route('sites.show', $site) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $site->name }}に戻る</a>
</div>

<h1 class="text-2xl font-bold mb-6">公開管理 - {{ $site->name }}</h1>

{{-- Ready状態のページ一覧 --}}
<div class="bg-white rounded-lg shadow mb-8">
    <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-semibold">デプロイ対象ページ（ready状態）</h2>
    </div>

    @if($readyPages->isNotEmpty())
    <form method="POST" action="{{ route('sites.publish.deploy', $site) }}">
        @csrf
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left w-10">
                        <input type="checkbox" id="select-all" class="rounded border-gray-300"
                            onchange="document.querySelectorAll('.page-checkbox').forEach(c => c.checked = this.checked)">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ページ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">スラッグ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">世代</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">更新日時</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($readyPages as $page)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <input type="checkbox" name="page_ids[]" value="{{ $page->id }}" class="page-checkbox rounded border-gray-300" checked>
                    </td>
                    <td class="px-6 py-4 font-medium">{{ $page->title }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">/{{ $page->slug }}</td>
                    <td class="px-6 py-4 text-sm">#{{ $page->currentGeneration?->generation ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $page->currentGeneration?->updated_at?->format('Y-m-d H:i') ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t bg-gray-50">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md text-sm hover:bg-green-700"
                onclick="return confirm('選択したページをデプロイしますか？この操作は取り消せません。')">
                デプロイ実行
            </button>
        </div>
    </form>
    @else
    <div class="px-6 py-8 text-center text-gray-500">
        ready状態のページがありません
    </div>
    @endif
</div>

{{-- デプロイ履歴 --}}
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-semibold">デプロイ履歴</h2>
    </div>
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">日時</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">実行者</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">備考</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($history as $record)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-500">#{{ $record->id }}</td>
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
                <td class="px-6 py-4 text-sm text-gray-500">
                    @if($record->rollback_of)
                        ロールバック（#{{ $record->rollback_of }}）
                    @endif
                    @if($record->error_log)
                        <span class="text-red-500">エラーあり</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right">
                    @if($record->deploy_status === 'success' && !$record->rollback_of)
                    <form method="POST" action="{{ route('sites.publish.rollback', [$site, $record]) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline text-sm"
                            onclick="return confirm('このデプロイにロールバックしますか？')">
                            ロールバック
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">デプロイ履歴はありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($history->hasPages())
    <div class="px-6 py-4 border-t">
        {{ $history->links() }}
    </div>
    @endif
</div>
@endsection
