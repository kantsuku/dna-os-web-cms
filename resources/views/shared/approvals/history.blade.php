@extends('layouts.app')
@section('title', '承認履歴')

@section('content')
<div class="mb-6">
    <a href="{{ route('approvals.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; 承認待ちに戻る</a>
</div>

<h1 class="text-2xl font-bold text-gray-900 mb-6">承認履歴</h1>

<div class="bg-white rounded-lg border border-gray-200 divide-y">
    @forelse($records as $record)
        <div class="p-4 text-sm">
            <div class="flex justify-between items-start">
                <div>
                    <span class="font-medium">{{ $record->approver?->name ?? '不明' }}</span>
                    <span class="text-gray-500">が</span>
                    <span class="font-mono text-gray-400">{{ $record->approvable_type }}:{{ $record->approvable_id }}</span>
                    <span class="text-gray-500">を</span>
                    <span class="font-medium {{ $record->approval_type === 'approve' ? 'text-green-600' : 'text-red-600' }}">{{ $record->approval_type }}</span>
                </div>
                <span class="text-xs text-gray-400">{{ $record->created_at->format('Y/m/d H:i') }}</span>
            </div>
            @if($record->comment)
                <p class="text-gray-600 mt-1 ml-4">{{ $record->comment }}</p>
            @endif
        </div>
    @empty
        <div class="p-8 text-center text-gray-500">承認履歴はありません</div>
    @endforelse
</div>
<div class="mt-4">{{ $records->links() }}</div>
@endsection
