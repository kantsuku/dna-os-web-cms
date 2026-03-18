@extends('layouts.app')
@section('title', 'チャネル実行状況')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">チャネル実行状況</h1>

{{-- ステータス内訳 --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    @foreach(['pending' => '待機中', 'in_progress' => '実行中', 'review_ready' => '承認待ち', 'completed' => '完了'] as $key => $label)
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $statusCounts[$key] ?? 0 }}</p>
            <p class="text-sm text-gray-500">{{ $label }}</p>
        </div>
    @endforeach
</div>

{{-- タスク一覧 --}}
<div class="space-y-3">
    @forelse($tasks as $task)
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs font-mono text-gray-400">{{ $task->id }}</span>
                    <p class="font-medium text-gray-900">{{ $task->title }}</p>
                    <p class="text-sm text-gray-500">{{ $task->task_type }} | {{ $task->channel }}</p>
                    @if($task->targetSite)
                        <span class="text-xs text-gray-400">{{ $task->targetSite->name }}</span>
                    @endif
                    @if($task->targetPage)
                        <a href="{{ route('sites.pages.show', [$task->targetSite, $task->targetPage]) }}" class="text-xs text-indigo-600 ml-2">{{ $task->targetPage->title }} &rarr;</a>
                    @endif
                </div>
                @php
                    $ctColors = [
                        'pending' => 'gray', 'in_progress' => 'blue', 'review_ready' => 'yellow',
                        'approved' => 'green', 'deployed' => 'indigo', 'completed' => 'green',
                        'rejected' => 'red', 'cancelled' => 'gray',
                    ];
                    $ctc = $ctColors[$task->status] ?? 'gray';
                @endphp
                <span class="px-2 py-1 rounded text-xs bg-{{ $ctc }}-100 text-{{ $ctc }}-700">{{ $task->status }}</span>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">チャネルタスクはありません</p>
    @endforelse
</div>
<div class="mt-4">{{ $tasks->links() }}</div>
@endsection
