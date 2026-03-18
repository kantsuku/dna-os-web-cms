@extends('layouts.app')
@section('title', $exception->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('sites.exceptions.index', $site) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; 例外コンテンツ一覧</a>
</div>

<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $exception->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $exception->page->title }} | {{ $exception->content_type }}</p>
    </div>
    <div class="flex items-center space-x-2">
        @php
            $sColors = [
                'draft' => 'gray', 'first_review' => 'yellow', 'final_review' => 'orange',
                'approved' => 'green', 'published' => 'blue', 'rejected' => 'red',
            ];
            $sc = $sColors[$exception->status] ?? 'gray';
        @endphp
        <span class="px-3 py-1 rounded text-sm bg-{{ $sc }}-100 text-{{ $sc }}-700 font-medium">{{ $exception->status }}</span>
        <a href="{{ route('sites.exceptions.edit', [$site, $exception]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">編集</a>
    </div>
</div>

{{-- コンプライアンスチェック結果 --}}
@if(!empty($complianceResults))
<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-medium text-gray-900 mb-4">コンプライアンスチェック結果</h2>
    <div class="space-y-2">
        @foreach($complianceResults as $result)
            @php
                $icon = match($result['status']) {
                    'ok' => '✓',
                    'ng' => '✗',
                    'warning' => '!',
                    default => '?',
                };
                $color = match($result['status']) {
                    'ok' => 'green',
                    'ng' => 'red',
                    'warning' => 'yellow',
                    default => 'gray',
                };
            @endphp
            <div class="flex items-start space-x-3 p-2 rounded bg-{{ $color }}-50">
                <span class="text-{{ $color }}-600 font-bold text-sm mt-0.5">{{ $icon }}</span>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $result['check'] }}</p>
                    <p class="text-xs text-gray-600">{{ $result['message'] }}</p>
                    @if(isset($result['gl_reference']))
                        <p class="text-xs text-gray-400 mt-0.5">参照: {{ $result['gl_reference'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- 構造化データ --}}
@if($exception->structured_data)
<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-medium text-gray-900 mb-4">症例構造化データ</h2>
    <div class="grid grid-cols-2 gap-3 text-sm">
        @foreach($exception->structured_data as $key => $value)
            @if($key !== 'images' && !empty($value))
                <div>
                    <span class="text-gray-500">{{ $key }}:</span>
                    <span class="font-medium">{{ $value }}</span>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endif

{{-- 本文 --}}
<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-medium text-gray-900 mb-4">本文</h2>
    <div class="prose prose-sm max-w-none">{!! $exception->content_html !!}</div>
</div>

{{-- アクションボタン --}}
<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-medium text-gray-900 mb-4">アクション</h2>

    @if($exception->status === 'draft')
        <form method="POST" action="{{ route('sites.exceptions.submit-review', [$site, $exception]) }}">
            @csrf
            <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded text-sm hover:bg-yellow-600">公開申請する（一次承認へ）</button>
        </form>
    @elseif($exception->status === 'first_review')
        <div class="flex space-x-3">
            <form method="POST" action="{{ route('sites.exceptions.first-approve', [$site, $exception]) }}">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">一次承認する</button>
            </form>
            <form method="POST" action="{{ route('sites.exceptions.reject', [$site, $exception]) }}" x-data="{ show: false }">
                @csrf
                <button type="button" @click="show = !show" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">却下</button>
                <div x-show="show" class="mt-2">
                    <textarea name="comment" required class="w-full border-gray-300 rounded text-sm" placeholder="却下理由"></textarea>
                    <button type="submit" class="mt-1 bg-red-600 text-white px-3 py-1 rounded text-xs">確定</button>
                </div>
            </form>
        </div>
        <p class="text-xs text-gray-400 mt-2">一次承認者: 管理者</p>
    @elseif($exception->status === 'final_review')
        <div class="flex space-x-3">
            <form method="POST" action="{{ route('sites.exceptions.final-approve', [$site, $exception]) }}">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">最終承認する</button>
            </form>
            <form method="POST" action="{{ route('sites.exceptions.reject', [$site, $exception]) }}" x-data="{ show: false }">
                @csrf
                <button type="button" @click="show = !show" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">却下</button>
                <div x-show="show" class="mt-2">
                    <textarea name="comment" required class="w-full border-gray-300 rounded text-sm" placeholder="却下理由"></textarea>
                    <button type="submit" class="mt-1 bg-red-600 text-white px-3 py-1 rounded text-xs">確定</button>
                </div>
            </form>
        </div>
        <p class="text-xs text-gray-400 mt-2">一次承認: {{ $exception->firstApprover?->name }} ({{ $exception->first_approved_at?->format('Y/m/d H:i') }})</p>
        <p class="text-xs text-gray-400">最終承認者: 院長/法務担当</p>
    @elseif($exception->status === 'approved')
        <p class="text-green-600 font-medium">承認済み — 公開可能です</p>
        <p class="text-xs text-gray-400 mt-1">一次承認: {{ $exception->firstApprover?->name }} | 最終承認: {{ $exception->finalApprover?->name }}</p>
    @endif
</div>

{{-- 承認履歴 --}}
@if($exception->approvalRecords->isNotEmpty())
<div class="bg-white rounded-lg border border-gray-200 p-6">
    <h2 class="text-lg font-medium text-gray-900 mb-4">承認履歴</h2>
    <div class="divide-y">
        @foreach($exception->approvalRecords as $record)
            <div class="py-2 text-sm">
                <span class="font-medium">{{ $record->approver->name }}</span>
                <span class="text-gray-500">— {{ $record->approval_level }}</span>
                <span class="{{ $record->approval_type === 'approve' ? 'text-green-600' : 'text-red-600' }} font-medium">{{ $record->approval_type }}</span>
                <span class="text-xs text-gray-400 ml-2">{{ $record->created_at->format('Y/m/d H:i') }}</span>
                @if($record->comment)
                    <p class="text-gray-600 ml-4 mt-1">{{ $record->comment }}</p>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection
