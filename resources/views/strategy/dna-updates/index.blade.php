@extends('layouts.app')
@section('title', 'DNA-OS更新差分')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">DNA-OS更新差分</h1>

<div class="space-y-3">
    @forelse($updates as $log)
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $log->detail['title'] ?? 'DNA-OS変更検出' }}</p>
                    <p class="text-xs text-gray-500 mt-1">Clinic: {{ $log->clinic_id }} | Source: {{ $log->source_id ?? '-' }}</p>
                    @if($log->detail)
                        <div class="mt-2 text-xs text-gray-600 bg-gray-50 rounded p-2">
                            <pre>{{ json_encode($log->detail, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                </div>
                <span class="text-xs text-gray-400">{{ $log->created_at->format('Y/m/d H:i') }}</span>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center text-gray-500">
            DNA-OS更新はまだ検出されていません
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $updates->links() }}</div>
@endsection
