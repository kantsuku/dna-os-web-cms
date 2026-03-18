@extends('layouts.app')
@section('title', '戦略タスク一覧')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">戦略タスク一覧</h1>
        <p class="text-sm text-gray-500 mt-1">承認待ち: {{ $pendingCount }}件</p>
    </div>
</div>

{{-- フィルタ --}}
<div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
    <form method="GET" class="flex items-center space-x-4">
        <select name="status" class="border-gray-300 rounded text-sm">
            <option value="">全ステータス</option>
            @foreach(['draft','pending_approval','approved','in_progress','completed','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">絞り込み</button>
        <a href="{{ route('strategy.tasks.index') }}" class="text-sm text-gray-500 hover:text-gray-700">リセット</a>
    </form>
</div>

{{-- タスク一覧 --}}
<div class="space-y-3">
    @forelse($tasks as $task)
        <div class="bg-white rounded-lg border border-gray-200 p-4 hover:border-indigo-300 transition">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-1">
                        <span class="text-xs font-mono text-gray-400">{{ $task->id }}</span>
                        @php
                            $priorityColors = ['critical' => 'red', 'high' => 'orange', 'medium' => 'blue', 'low' => 'gray'];
                            $pc = $priorityColors[$task->priority] ?? 'gray';
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs bg-{{ $pc }}-100 text-{{ $pc }}-700">{{ $task->priority }}</span>
                        <span class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">{{ $task->trigger_type }}</span>
                    </div>
                    <a href="{{ route('strategy.tasks.show', $task) }}" class="text-lg font-medium text-gray-900 hover:text-indigo-600">{{ $task->title }}</a>
                    <p class="text-sm text-gray-500 mt-1">{{ Str::limit($task->description, 100) }}</p>
                </div>
                <div class="text-right ml-4">
                    @php
                        $statusColors = [
                            'draft' => 'gray', 'pending_approval' => 'yellow', 'approved' => 'blue',
                            'in_progress' => 'indigo', 'completed' => 'green', 'cancelled' => 'red',
                        ];
                        $sc = $statusColors[$task->status] ?? 'gray';
                    @endphp
                    <span class="px-2 py-1 rounded text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ $task->status }}</span>
                    <p class="text-xs text-gray-400 mt-2">{{ $task->created_at->format('Y/m/d H:i') }}</p>
                    <p class="text-xs text-gray-400">チャネルタスク: {{ $task->channelTasks->count() }}件</p>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center text-gray-500">
            タスクはありません
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $tasks->links() }}</div>
@endsection
