@extends('layouts.app')
@section('title', '例外コンテンツ - ' . $site->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; {{ $site->name }}</a>
</div>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">例外コンテンツ</h1>
    <a href="{{ route('clinic.sites.exceptions.create', [$clinic, $site]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">新規作成</a>
</div>

<div class="space-y-3">
    @forelse($exceptions as $exc)
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        @php
                            $typeLabels = ['case_study' => '症例', 'case' => '症例', 'medical_ad_gl' => '医療広告GL', 'effect_claim' => '効果訴求', 'compliance_text' => 'コンプライアンス', 'other' => 'その他'];
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-700">{{ $typeLabels[$exc->content_type] ?? $exc->content_type }}</span>
                        <span class="text-xs text-gray-400">{{ $exc->page->title }}</span>
                    </div>
                    <a href="{{ route('clinic.sites.exceptions.show', [$clinic, $site, $exc]) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $exc->title }}</a>
                </div>
                @php
                    $sColors = [
                        'draft' => 'gray', 'first_review' => 'yellow', 'final_review' => 'orange',
                        'approved' => 'green', 'published' => 'blue', 'rejected' => 'red', 'archived' => 'gray',
                    ];
                    $sc = $sColors[$exc->status] ?? 'gray';
                @endphp
                <span class="px-2 py-1 rounded text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ $exc->status }}</span>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center text-gray-500">
            例外コンテンツはありません
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $exceptions->links() }}</div>
@endsection
