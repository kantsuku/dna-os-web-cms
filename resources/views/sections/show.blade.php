@extends('layouts.app')

@section('title', $section->section_key . ' - セクション詳細')

@section('content')
@php $site = $section->page->site; $page = $section->page; @endphp

<div class="mb-6">
    <a href="{{ route('sites.pages.show', [$site, $page]) }}" class="text-sm text-gray-500 hover:underline">&larr; {{ $page->title }}</a>
    <h1 class="text-2xl font-bold mt-1">セクション: <span class="font-mono">{{ $section->section_key }}</span></h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">ソース種別</div>
        <div class="mt-1 font-semibold">{{ $section->content_source_type }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">上書き制御</div>
        <div class="mt-1 font-semibold">{{ $section->override_policy }}</div>
        @if($section->overrideRule)
            <div class="text-xs text-gray-400 mt-1">{{ $section->overrideRule->reason }}</div>
        @endif
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">人間修正</div>
        <div class="mt-1 font-semibold">{{ $section->is_human_edited ? 'あり' : 'なし' }}</div>
    </div>
</div>

{{-- 上書き制御ポリシー変更 --}}
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h2 class="text-lg font-semibold mb-4">上書き制御ポリシー</h2>
    <form method="POST" action="{{ route('sections.override-policy', $section) }}" class="flex items-end space-x-4">
        @csrf
        @method('PUT')
        <div class="flex-1">
            <select name="policy" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                @foreach(['auto_sync', 'confirm_before_sync', 'manual_only', 'locked'] as $policy)
                    <option value="{{ $policy }}" {{ $section->override_policy === $policy ? 'selected' : '' }}>{{ $policy }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <input type="text" name="reason" placeholder="変更理由"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">変更</button>
    </form>
</div>

{{-- バリアント履歴 --}}
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold">バリアント履歴</h2>
        <a href="{{ route('sections.edit', $section) }}" class="text-indigo-600 hover:underline text-sm">微調整</a>
    </div>
    <div class="divide-y divide-gray-200">
        @forelse($section->variants as $variant)
            <div class="px-6 py-4">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="font-semibold">v{{ $variant->version }}</span>
                        <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-gray-100 ml-2">{{ $variant->source_type }}</span>
                        <span class="inline-flex px-2 py-0.5 text-xs rounded-full ml-1
                            {{ $variant->status === 'published' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $variant->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $variant->status === 'approved' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $variant->status === 'superseded' ? 'bg-gray-50 text-gray-400' : '' }}">
                            {{ $variant->status }}
                        </span>
                    </div>
                    <span class="text-xs text-gray-400">{{ $variant->created_at->format('Y-m-d H:i') }}</span>
                </div>
                @if($variant->edit_reason)
                    <div class="text-xs text-gray-500 mt-1">理由: {{ $variant->edit_reason }}</div>
                @endif
                <div class="mt-2 text-sm text-gray-600 line-clamp-3 bg-gray-50 p-3 rounded">
                    {!! Str::limit($variant->content_html, 500) !!}
                </div>
            </div>
        @empty
            <div class="px-6 py-8 text-center text-gray-500">バリアントなし</div>
        @endforelse
    </div>
</div>
@endsection
