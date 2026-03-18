@extends('layouts.app')
@section('title', 'DNA-OS更新差分')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">DNA-OS更新差分</h1>
</div>

{{-- 手動同期 --}}
@if($sites->isNotEmpty())
<div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
    <h2 class="text-sm font-medium text-gray-700 mb-3">手動同期</h2>
    <form method="POST" action="{{ route('strategy.dna-updates.sync') }}" class="flex items-center space-x-3">
        @csrf
        <select name="site_id" required class="border-gray-300 rounded text-sm">
            <option value="">サイトを選択</option>
            @foreach($sites as $site)
                <option value="{{ $site->id }}">{{ $site->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">DNA-OSから同期</button>
    </form>
</div>
@endif

{{-- 更新履歴 --}}
<div class="space-y-3">
    @forelse($updates as $log)
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $log->detail['destination_sheet'] ?? 'DNA-OS変更検出' }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Clinic: {{ $log->clinic_id }}
                        @if($log->source_id) | Proposal: {{ $log->source_id }} @endif
                    </p>
                    @if($log->detail)
                        @php $d = $log->detail; @endphp
                        <div class="mt-2 text-xs space-y-1">
                            @if(isset($d['destination_field']))
                                <p><span class="text-gray-500">フィールド:</span> {{ $d['destination_field'] }}</p>
                            @endif
                            @if(isset($d['proposed_value']))
                                <p><span class="text-gray-500">新しい値:</span> {{ Str::limit($d['proposed_value'], 100) }}</p>
                            @endif
                        </div>
                    @endif
                </div>
                <span class="text-xs text-gray-400">{{ $log->created_at->format('Y/m/d H:i') }}</span>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center text-gray-500">
            DNA-OS更新はまだ検出されていません。「DNA-OSから同期」ボタンで手動同期できます。
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $updates->links() }}</div>
@endsection
