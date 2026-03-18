@extends('layouts.app')
@section('title', $site->name)
@section('content')

<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $site->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $site->domain ?? '未設定' }} | {{ $site->getSiteTypeLabel() }}</p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('clinic.sites.pages.create', [$clinic, $site]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">+ ページ追加</a>
        <a href="{{ route('clinic.sites.edit', [$clinic, $site]) }}" class="bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-300">設定</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    {{-- サイトマップ（3カラム分） --}}
    <div class="lg:col-span-3">
        <div class="bg-white rounded-lg shadow">
            <div class="px-5 py-3 border-b bg-gray-50 flex justify-between items-center">
                <h2 class="text-sm font-semibold">サイトマップ</h2>
                <div class="text-xs text-gray-400">
                    <span class="inline-block w-2 h-2 rounded-full bg-green-400 mr-1"></span>公開中
                    <span class="inline-block w-2 h-2 rounded-full bg-blue-400 mr-1 ml-2"></span>公開準備
                    <span class="inline-block w-2 h-2 rounded-full bg-gray-300 mr-1 ml-2"></span>下書き
                </div>
            </div>

            @if($site->pages->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    <p>ページがまだありません。</p>
                    <a href="{{ route('clinic.sites.pages.create', [$clinic, $site]) }}" class="text-indigo-600 hover:underline mt-2 inline-block">最初のページを作成</a>
                </div>
            @else
                @php
                    $rootPages = $site->pages->whereNull('parent_id')->sortBy('sort_order');
                    $childMap = $site->pages->whereNotNull('parent_id')->groupBy('parent_id');
                @endphp

                <div class="divide-y">
                    @foreach($rootPages as $page)
                        @include('sites._sitemap_row', ['page' => $page, 'depth' => 0])

                        @if($childMap->has($page->id))
                            @foreach($childMap[$page->id]->sortBy('sort_order') as $child)
                                @include('sites._sitemap_row', ['page' => $child, 'depth' => 1])

                                @if($childMap->has($child->id))
                                    @foreach($childMap[$child->id]->sortBy('sort_order') as $grandchild)
                                        @include('sites._sitemap_row', ['page' => $grandchild, 'depth' => 2])
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- サイドバー --}}
    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">サイト情報</h3>
            <dl class="text-sm space-y-2">
                <div><dt class="text-gray-400">タイプ</dt><dd class="font-medium">{{ $site->getSiteTypeLabel() }}</dd></div>
                <div><dt class="text-gray-400">ドメイン</dt><dd class="font-medium">{{ $site->domain ?? '未設定' }}</dd></div>
                <div><dt class="text-gray-400">ページ数</dt><dd class="font-medium">{{ $site->pages->count() }}</dd></div>
                <div><dt class="text-gray-400">ステータス</dt>
                    <dd><span class="px-2 py-0.5 text-xs rounded-full {{ $site->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $site->status }}</span></dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">デプロイ</h3>
            @forelse($site->deployRecords->take(3) as $record)
                <div class="flex justify-between items-center text-xs py-1.5 {{ !$loop->last ? 'border-b' : '' }}">
                    <span class="text-gray-500">{{ $record->deployed_at?->format('m/d H:i') ?? $record->created_at->format('m/d H:i') }}</span>
                    @php $dc = ['success' => 'text-green-600', 'failed' => 'text-red-600'][$record->deploy_status] ?? 'text-gray-500'; @endphp
                    <span class="{{ $dc }} font-medium">{{ $record->deploy_status }}</span>
                </div>
            @empty
                <p class="text-xs text-gray-400">未デプロイ</p>
            @endforelse
            <a href="{{ route('clinic.sites.publish.index', [$clinic, $site]) }}" class="text-xs text-indigo-600 hover:text-indigo-800 mt-2 inline-block">公開管理 &rarr;</a>
        </div>
    </div>
</div>
@endsection
