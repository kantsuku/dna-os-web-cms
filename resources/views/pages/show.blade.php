@extends('layouts.app')
@section('title', $page->title . ' - ' . $site->name)
@section('content')
<div class="mb-6">
    <a href="{{ route('sites.show', $site) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $site->name }}に戻る</a>
</div>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold">{{ $page->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">/{{ $page->slug }} | タイプ: {{ $page->page_type }}</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('sites.pages.import', [$site, $page]) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-md text-sm hover:bg-yellow-600">原稿取り込み</a>
        <a href="{{ route('sites.pages.sections', [$site, $page]) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">セクション管理</a>
        <a href="{{ route('sites.pages.preview', [$site, $page]) }}" class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700" target="_blank">プレビュー</a>
        <a href="{{ route('sites.pages.edit', [$site, $page]) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300">設定編集</a>
    </div>
</div>

{{-- 現在の世代のコンテンツプレビュー --}}
@if($page->currentGeneration)
<div class="bg-white rounded-lg shadow mb-8">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-semibold">
            現在のコンテンツ（世代 {{ $page->currentGeneration->generation }}）
            @if(!empty($sections))
                <span class="text-sm font-normal text-gray-500 ml-2">{{ count($sections) }}セクション</span>
            @endif
        </h2>
        <span class="px-2 py-1 text-xs rounded-full {{ $page->currentGeneration->status === 'ready' ? 'bg-blue-100 text-blue-800' : ($page->currentGeneration->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
            {{ $page->currentGeneration->status }}
        </span>
    </div>
    <div class="p-6">
        <div class="border rounded-md p-4 bg-gray-50 max-h-96 overflow-y-auto prose prose-sm max-w-none">
            {!! $page->currentGeneration->final_html ?? $page->currentGeneration->content_html ?? '<p class="text-gray-400">コンテンツがありません</p>' !!}
        </div>
    </div>
</div>
@else
<div class="bg-white rounded-lg shadow mb-8 p-6 text-center text-gray-500">
    まだコンテンツがありません。「原稿取り込み」から始めてください。
</div>
@endif

{{-- 世代履歴テーブル --}}
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-semibold">世代履歴</h2>
    </div>
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">世代</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ソース</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ソースURL</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">手動パッチ</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">作成日時</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($page->generations as $gen)
            <tr class="hover:bg-gray-50 {{ $gen->id === $page->current_generation_id ? 'bg-indigo-50' : '' }}">
                <td class="px-6 py-4 text-sm font-medium">
                    #{{ $gen->generation }}
                    @if($gen->id === $page->current_generation_id)
                        <span class="ml-1 text-xs text-indigo-600">(現在)</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm">{{ $gen->source ?? '-' }}</td>
                <td class="px-6 py-4">
                    @php
                        $genColors = [
                            'draft' => 'bg-gray-100 text-gray-800',
                            'ready' => 'bg-blue-100 text-blue-800',
                            'published' => 'bg-green-100 text-green-800',
                        ];
                    @endphp
                    <span class="px-2 py-1 text-xs rounded-full {{ $genColors[$gen->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $gen->status }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    @if($gen->source_url)
                        <a href="{{ $gen->source_url }}" target="_blank" class="text-indigo-600 hover:underline truncate block max-w-xs">{{ Str::limit($gen->source_url, 40) }}</a>
                    @else
                        -
                    @endif
                </td>
                <td class="px-6 py-4 text-sm">
                    @if($gen->hasHumanPatch())
                        <span class="text-yellow-600 font-medium">あり</span>
                    @else
                        <span class="text-gray-400">なし</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $gen->created_at->format('Y-m-d H:i') }}</td>
                <td class="px-6 py-4 text-right">
                    @if($gen->status === 'draft')
                    <form method="POST" action="{{ route('sites.pages.generations.ready', [$site, $page, $gen]) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600"
                            onclick="return confirm('この世代をready状態にしますか？')">
                            readyにする
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">世代履歴がありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
