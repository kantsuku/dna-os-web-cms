@extends('layouts.app')
@section('title', $strategicTask->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.strategy.tasks.index', $clinic) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; タスク一覧に戻る</a>
</div>

<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
    <div class="flex justify-between items-start mb-4">
        <div>
            <span class="text-sm font-mono text-gray-400">{{ $strategicTask->id }}</span>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $strategicTask->title }}</h1>
        </div>
        @php
            $statusColors = [
                'draft' => 'gray', 'pending_approval' => 'yellow', 'approved' => 'blue',
                'in_progress' => 'indigo', 'completed' => 'green', 'cancelled' => 'red',
            ];
            $sc = $statusColors[$strategicTask->status] ?? 'gray';
        @endphp
        <span class="px-3 py-1 rounded text-sm bg-{{ $sc }}-100 text-{{ $sc }}-700 font-medium">{{ $strategicTask->status }}</span>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
        <div><span class="text-gray-500">トリガー:</span> {{ $strategicTask->trigger_type }}</div>
        <div><span class="text-gray-500">優先度:</span> {{ $strategicTask->priority }}</div>
        <div><span class="text-gray-500">リスク:</span> {{ $strategicTask->risk_level }}</div>
        <div><span class="text-gray-500">作成者:</span> {{ $strategicTask->created_by }}</div>
        <div><span class="text-gray-500">対象チャネル:</span> {{ implode(', ', $strategicTask->target_channels ?? []) }}</div>
        @if($strategicTask->approver)
            <div><span class="text-gray-500">承認者:</span> {{ $strategicTask->approver->name }} ({{ $strategicTask->approved_at?->format('Y/m/d H:i') }})</div>
        @endif
    </div>

    @if($strategicTask->description)
        <div class="mb-4">
            <h3 class="text-sm font-medium text-gray-700 mb-1">説明</h3>
            <p class="text-sm text-gray-600">{{ $strategicTask->description }}</p>
        </div>
    @endif

    @if($strategicTask->intent)
        <div class="mb-4">
            <h3 class="text-sm font-medium text-gray-700 mb-1">意図</h3>
            <p class="text-sm text-gray-600">{{ $strategicTask->intent }}</p>
        </div>
    @endif

    {{-- アクションボタン --}}
    @if($strategicTask->canBeApproved())
        <div class="flex space-x-3 mt-6 pt-4 border-t">
            <form method="POST" action="{{ route('clinic.strategy.tasks.approve', [$clinic, $strategicTask]) }}">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">承認する</button>
            </form>
            <form method="POST" action="{{ route('clinic.strategy.tasks.reject', [$clinic, $strategicTask]) }}" x-data="{ show: false }">
                @csrf
                <button type="button" @click="show = !show" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">却下する</button>
                <div x-show="show" class="mt-2">
                    <textarea name="comment" required class="w-full border-gray-300 rounded text-sm" placeholder="却下理由を入力"></textarea>
                    <button type="submit" class="mt-1 bg-red-600 text-white px-3 py-1 rounded text-xs">却下を確定</button>
                </div>
            </form>
        </div>
    @endif
</div>

{{-- チャネルタスク一覧 --}}
<h2 class="text-lg font-bold text-gray-900 mb-3">チャネルタスク</h2>
<div class="space-y-3">
    @forelse($strategicTask->channelTasks as $ct)
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs font-mono text-gray-400">{{ $ct->id }}</span>
                    <p class="font-medium text-gray-900">{{ $ct->title }}</p>
                    <p class="text-sm text-gray-500">{{ $ct->task_type }} | {{ $ct->channel }}</p>
                    @if($ct->targetPage)
                        <a href="{{ route('clinic.sites.pages.show', [$clinic, $ct->targetSite, $ct->targetPage]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                            {{ $ct->targetPage->title }} &rarr;
                        </a>
                    @endif
                </div>
                @php
                    $ctColors = [
                        'pending' => 'gray', 'in_progress' => 'blue', 'review_ready' => 'yellow',
                        'approved' => 'green', 'deployed' => 'indigo', 'completed' => 'green',
                        'rejected' => 'red', 'cancelled' => 'gray',
                    ];
                    $ctc = $ctColors[$ct->status] ?? 'gray';
                @endphp
                <span class="px-2 py-1 rounded text-xs bg-{{ $ctc }}-100 text-{{ $ctc }}-700">{{ $ct->status }}</span>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">チャネルタスクはまだ生成されていません</p>
    @endforelse
</div>

{{-- 承認履歴 --}}
@if($strategicTask->approvalRecords->isNotEmpty())
    <h2 class="text-lg font-bold text-gray-900 mt-6 mb-3">承認履歴</h2>
    <div class="bg-white rounded-lg border border-gray-200 divide-y">
        @foreach($strategicTask->approvalRecords as $record)
            <div class="p-3 text-sm">
                <span class="font-medium">{{ $record->approver->name }}</span>
                <span class="text-gray-500">が</span>
                <span class="font-medium {{ $record->approval_type === 'approve' ? 'text-green-600' : 'text-red-600' }}">{{ $record->approval_type }}</span>
                <span class="text-gray-400 ml-2">{{ $record->created_at->format('Y/m/d H:i') }}</span>
                @if($record->comment)
                    <p class="text-gray-600 mt-1">{{ $record->comment }}</p>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection
