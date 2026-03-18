@extends('layouts.app')
@section('title', $page->title . ' - セクション管理')

@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.pages.show', [$clinic, $site, $page]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; {{ $page->title }} に戻る</a>
</div>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">セクション管理</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $page->title }} — 世代{{ $generation?->generation ?? '-' }} — {{ count($sections) }}セクション</p>
    </div>
</div>

@if(empty($sections))
    <div class="bg-white rounded-lg border border-gray-200 p-8 text-center text-gray-500">
        セクションがありません。コンテンツを取り込んでください。
        <div class="mt-4">
            <a href="{{ route('clinic.sites.pages.import', [$clinic, $site, $page]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">取り込み画面へ</a>
        </div>
    </div>
@else
    <div class="space-y-3">
        @foreach($sections as $i => $section)
            @php
                $lockColors = [
                    'unlocked' => 'green',
                    'human_locked' => 'yellow',
                    'system_locked' => 'red',
                ];
                $lc = $lockColors[$section['lock_status'] ?? 'unlocked'] ?? 'green';
                $lockLabel = match($section['lock_status'] ?? 'unlocked') {
                    'human_locked' => 'ロック中',
                    'system_locked' => 'システムロック',
                    default => '未ロック',
                };
            @endphp
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="flex justify-between items-center px-4 py-3 bg-gray-50 border-b">
                    <div class="flex items-center space-x-3">
                        <span class="text-xs font-mono text-gray-400">{{ $section['section_id'] }}</span>
                        <span class="font-medium text-gray-900">{{ $section['heading'] ?: '(見出しなし)' }}</span>
                        <span class="px-2 py-0.5 rounded text-xs bg-{{ $lc }}-100 text-{{ $lc }}-700">{{ $lockLabel }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        {{-- ロック切り替え --}}
                        @if(($section['lock_status'] ?? 'unlocked') !== 'system_locked')
                            <form method="POST" action="{{ route('clinic.sites.pages.sections.lock', [$clinic, $site, $page, $section['section_id']]) }}">
                                @csrf
                                @if(($section['lock_status'] ?? 'unlocked') === 'unlocked')
                                    <input type="hidden" name="lock_status" value="human_locked">
                                    <button type="submit" class="text-xs text-yellow-600 hover:text-yellow-800 border border-yellow-300 px-2 py-1 rounded">ロックする</button>
                                @else
                                    <input type="hidden" name="lock_status" value="unlocked">
                                    <button type="submit" class="text-xs text-green-600 hover:text-green-800 border border-green-300 px-2 py-1 rounded">アンロック</button>
                                @endif
                            </form>
                        @endif

                        {{-- 編集ボタン --}}
                        <a href="{{ route('clinic.sites.pages.sections.edit', [$clinic, $site, $page, $section['section_id']]) }}"
                           class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-300 px-2 py-1 rounded">
                            編集
                        </a>
                    </div>
                </div>

                {{-- プレビュー --}}
                <div class="p-4 max-h-48 overflow-hidden relative">
                    <div class="prose prose-sm max-w-none text-gray-600 text-xs leading-relaxed">
                        {!! Str::limit(strip_tags($section['content_html'] ?? ''), 300) !!}
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-white to-transparent"></div>
                </div>

                <div class="px-4 py-2 bg-gray-50 border-t text-xs text-gray-400 flex justify-between">
                    <span>更新: {{ $section['last_modified_by'] ?? '-' }}</span>
                    <span>{{ isset($section['last_modified_at']) ? \Carbon\Carbon::parse($section['last_modified_at'])->format('Y/m/d H:i') : '-' }}</span>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
