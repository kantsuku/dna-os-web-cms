@extends('layouts.app')
@section('title', '承認待ち一覧')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">承認待ち一覧</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $pendingItems->count() }}件の承認待ち</p>
    </div>
    <a href="{{ route('approvals.history') }}" class="text-sm text-indigo-600 hover:text-indigo-800">承認履歴 &rarr;</a>
</div>

<div class="space-y-3">
    @forelse($pendingItems as $item)
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-1">
                        @php
                            $typeLabels = ['strategic_task' => '戦略タスク', 'channel_task' => 'Webタスク', 'exception_content' => '例外コンテンツ'];
                            $typeColors = ['strategic_task' => 'purple', 'channel_task' => 'blue', 'exception_content' => 'red'];
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs bg-{{ $typeColors[$item['type']] }}-100 text-{{ $typeColors[$item['type']] }}-700">{{ $typeLabels[$item['type']] }}</span>
                        <span class="text-xs font-mono text-gray-400">{{ $item['id'] }}</span>
                    </div>
                    <p class="font-medium text-gray-900">{{ $item['title'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $item['created_at']->format('Y/m/d H:i') }}</p>
                </div>
                <div class="text-right ml-4">
                    @php
                        $pc = ['critical' => 'red', 'high' => 'orange', 'medium' => 'blue', 'low' => 'gray'][$item['priority']] ?? 'gray';
                    @endphp
                    <span class="px-2 py-0.5 rounded text-xs bg-{{ $pc }}-100 text-{{ $pc }}-700">{{ $item['priority'] }}</span>

                    @if($item['type'] === 'strategic_task')
                        <div class="mt-2">
                            <a href="{{ route('strategy.tasks.show', $item['id']) }}" class="text-sm text-indigo-600 hover:text-indigo-800">詳細 &rarr;</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center text-gray-500">
            承認待ちはありません
        </div>
    @endforelse
</div>
@endsection
