@extends('layouts.app')
@section('title', '修正依頼詳細')

@section('content')
<div class="mb-6">
    <a href="{{ route('strategy.free-input.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; 依頼一覧に戻る</a>
</div>

<h1 class="text-2xl font-bold text-gray-900 mb-6">修正依頼詳細</h1>

<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
    <div class="flex justify-between items-start mb-4">
        <div>
            <p class="text-sm text-gray-500">送信者: {{ $freeInputRequest->submitter->name }} | {{ $freeInputRequest->created_at->format('Y/m/d H:i') }}</p>
            <p class="text-sm text-gray-500">サイト: {{ $freeInputRequest->site?->name ?? '-' }}</p>
        </div>
        @php
            $isColors = ['pending' => 'gray', 'interpreted' => 'blue', 'confirmed' => 'green', 'rejected' => 'red'];
            $ic = $isColors[$freeInputRequest->interpretation_status] ?? 'gray';
        @endphp
        <span class="px-3 py-1 rounded text-sm bg-{{ $ic }}-100 text-{{ $ic }}-700">{{ $freeInputRequest->interpretation_status }}</span>
    </div>

    <div class="mb-4">
        <h3 class="text-sm font-medium text-gray-700 mb-1">元のテキスト</h3>
        <div class="bg-gray-50 rounded p-3 text-sm text-gray-800">{{ $freeInputRequest->raw_text }}</div>
    </div>
</div>

{{-- AI解釈結果 --}}
@if($freeInputRequest->ai_interpretation)
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">AI解釈結果</h2>

        @php $interp = $freeInputRequest->ai_interpretation; @endphp

        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
            <div>
                <span class="text-gray-500">対象ページ:</span>
                <span class="font-medium">{{ $interp['target_page_slug'] ?? '不明' }}</span>
            </div>
            <div>
                <span class="text-gray-500">対象セクション:</span>
                <span class="font-medium">{{ $interp['target_section'] ?? '不明' }}</span>
            </div>
            <div>
                <span class="text-gray-500">アクション:</span>
                <span class="font-medium">{{ $interp['action'] ?? '不明' }}</span>
            </div>
            <div>
                <span class="text-gray-500">タスクタイプ:</span>
                <span class="font-medium">{{ $interp['task_type'] ?? '不明' }}</span>
            </div>
            <div class="col-span-2">
                <span class="text-gray-500">説明:</span>
                <span class="font-medium">{{ $interp['description'] ?? '不明' }}</span>
            </div>
            @if(isset($interp['confidence']))
                <div>
                    <span class="text-gray-500">信頼度:</span>
                    <span class="font-medium">{{ number_format($interp['confidence'] * 100) }}%</span>
                </div>
            @endif
        </div>

        @if($freeInputRequest->interpretation_status === 'interpreted')
            <div class="flex space-x-3 pt-4 border-t">
                <form method="POST" action="{{ route('strategy.free-input.confirm', $freeInputRequest) }}">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">この解釈で合っている → タスク生成</button>
                </form>
                <form method="POST" action="{{ route('strategy.free-input.reject', $freeInputRequest) }}">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">却下する</button>
                </form>
            </div>
        @endif
    </div>
@elseif($freeInputRequest->interpretation_status === 'pending')
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded mb-6 text-sm">
        AI解釈が保留中です。しばらくお待ちください。
    </div>
@endif

{{-- 生成されたタスク --}}
@if($freeInputRequest->strategicTask)
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">生成されたタスク</h2>
        <a href="{{ route('strategy.tasks.show', $freeInputRequest->strategicTask) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
            {{ $freeInputRequest->strategicTask->id }}: {{ $freeInputRequest->strategicTask->title }} &rarr;
        </a>
        @if($freeInputRequest->strategicTask->channelTasks)
            <ul class="mt-2 text-sm text-gray-600 space-y-1">
                @foreach($freeInputRequest->strategicTask->channelTasks as $ct)
                    <li>{{ $ct->id }}: {{ $ct->title }} ({{ $ct->status }})</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
@endsection
