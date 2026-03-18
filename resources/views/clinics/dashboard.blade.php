@extends('layouts.app')
@section('title', $clinic->name . ' — C&C 作戦本部')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ $clinic->name }}</h1>
    <p class="text-sm text-gray-500 mt-1">指揮統制ダッシュボード</p>
</div>

{{-- KPI --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-5">
        <div class="text-xs text-gray-500">チャネル数</div>
        <div class="text-2xl font-bold text-indigo-600 mt-1">{{ $clinic->sites->count() }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <div class="text-xs text-gray-500">総ページ</div>
        <div class="text-2xl font-bold text-green-600 mt-1">{{ $totalPages }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <div class="text-xs text-gray-500">未公開世代</div>
        <div class="text-2xl font-bold text-yellow-600 mt-1">{{ $newGenerations }}</div>
    </div>
    <a href="{{ route('clinic.approvals.index', $clinic) }}" class="bg-white rounded-lg shadow p-5 hover:ring-2 hover:ring-red-300 transition">
        <div class="text-xs text-gray-500">承認待ち</div>
        <div class="text-2xl font-bold text-red-600 mt-1">{{ $totalPendingApprovals }}</div>
    </a>
    <a href="{{ route('clinic.strategy.tasks.index', $clinic) }}" class="bg-white rounded-lg shadow p-5 hover:ring-2 hover:ring-blue-300 transition">
        <div class="text-xs text-gray-500">進行中タスク</div>
        <div class="text-2xl font-bold text-blue-600 mt-1">{{ $activeTasks }}</div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        {{-- 戦略タスク --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h2 class="text-lg font-semibold">戦略タスク</h2>
                <a href="{{ route('clinic.strategy.tasks.index', $clinic) }}" class="text-sm text-indigo-600 hover:text-indigo-800">全て見る &rarr;</a>
            </div>
            <div class="divide-y">
                @forelse($recentTasks as $task)
                    <a href="{{ route('clinic.strategy.tasks.show', [$clinic, $task]) }}" class="flex justify-between items-center px-5 py-3 hover:bg-gray-50">
                        <div class="flex-1 min-w-0 mr-4">
                            <p class="text-sm text-gray-900 truncate">{{ $task->title }}</p>
                            <p class="text-xs text-gray-400">{{ $task->created_at->format('m/d H:i') }} | {{ $task->trigger_type }}</p>
                        </div>
                        @php $sc = ['pending_approval'=>'yellow','approved'=>'blue','in_progress'=>'indigo','completed'=>'green','cancelled'=>'gray'][$task->status] ?? 'gray'; @endphp
                        <span class="px-2 py-0.5 rounded text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 flex-shrink-0">{{ $task->status }}</span>
                    </a>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-500">タスクなし</div>
                @endforelse
            </div>
        </div>

        {{-- チャネル一覧 --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h2 class="text-lg font-semibold">チャネル</h2>
                <a href="{{ route('clinic.sites.create', $clinic) }}" class="text-sm text-indigo-600 hover:text-indigo-800">+ 追加</a>
            </div>
            <div class="divide-y">
                @php
                    $webChannels = $clinic->sites->whereIn('site_type', ['hp','specialty','recruitment','lp']);
                    $otherChannels = $clinic->sites->whereNotIn('site_type', ['hp','specialty','recruitment','lp']);
                    $bc = ['hp'=>'bg-blue-500','specialty'=>'bg-purple-500','recruitment'=>'bg-green-500','lp'=>'bg-yellow-500','gbp'=>'bg-red-500','instagram'=>'bg-pink-500','blog_media'=>'bg-orange-500'];
                @endphp

                {{-- Webサイト --}}
                @if($webChannels->isNotEmpty())
                    <div class="px-5 py-2 bg-gray-50">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Web サイト</span>
                    </div>
                    @foreach($webChannels as $s)
                        <a href="{{ route('clinic.sites.show', [$clinic, $s]) }}" class="flex items-center px-5 py-3 hover:bg-gray-50">
                            <span class="w-2.5 h-2.5 rounded-full {{ $bc[$s->site_type] ?? 'bg-gray-400' }} mr-3"></span>
                            <div class="flex-1">
                                <span class="text-sm font-medium text-gray-900">{{ $s->name }}</span>
                                <span class="text-xs text-gray-400 ml-2">{{ $s->getSiteTypeLabel() }}</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $s->pages_count ?? 0 }}ページ</span>
                            <span class="text-gray-300 ml-3">&rarr;</span>
                        </a>
                    @endforeach
                @endif

                {{-- 非Webチャネル --}}
                @if($otherChannels->isNotEmpty())
                    <div class="px-5 py-2 bg-gray-50">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">その他チャネル</span>
                    </div>
                    @foreach($otherChannels as $s)
                        <div class="flex items-center px-5 py-3">
                            <span class="w-2.5 h-2.5 rounded-full {{ $bc[$s->site_type] ?? 'bg-gray-400' }} mr-3"></span>
                            <div class="flex-1">
                                <span class="text-sm font-medium text-gray-900">{{ $s->name }}</span>
                                <span class="text-xs text-gray-400 ml-2">{{ $s->getSiteTypeLabel() }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-500">準備中</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- サイドバー --}}
    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">クイックアクション</h3>
            <div class="space-y-2">
                <a href="{{ route('clinic.strategy.free-input.index', $clinic) }}" class="block w-full text-left px-4 py-2.5 bg-indigo-50 text-indigo-700 rounded-lg text-sm hover:bg-indigo-100 font-medium">修正依頼を送る</a>
                <a href="{{ route('clinic.strategy.dna-updates.index', $clinic) }}" class="block w-full text-left px-4 py-2.5 bg-gray-50 text-gray-700 rounded-lg text-sm hover:bg-gray-100">DNA-OS更新を確認</a>
                <a href="{{ route('clinic.approvals.index', $clinic) }}" class="block w-full text-left px-4 py-2.5 bg-gray-50 text-gray-700 rounded-lg text-sm hover:bg-gray-100">承認待ちを確認</a>
            </div>
        </div>

        @if($clinic->design)
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">医院トンマナ</h3>
                <p class="text-xs text-gray-500 mb-2">{{ $clinic->design->tone_and_manner['brand_voice'] ?? '未設定' }}</p>
                @if($clinic->design->tokens)
                    <div class="flex space-x-1 mt-2">
                        @foreach(array_slice($clinic->design->tokens, 0, 5) as $key => $val)
                            @if(str_starts_with($key, 'color-'))
                                <span class="w-6 h-6 rounded-full border border-gray-200" style="background:{{ $val }}" title="{{ $key }}"></span>
                            @endif
                        @endforeach
                    </div>
                @endif
                <a href="{{ route('clinic.design.tokens', $clinic) }}" class="text-xs text-indigo-600 mt-3 inline-block">設定 &rarr;</a>
            </div>
        @endif
    </div>
</div>
@endsection
