@extends('layouts.app')
@section('title', $clinic->name . ' - 作戦本部')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ $clinic->name }}</h1>
    <p class="text-sm text-gray-500 mt-1">作戦本部ダッシュボード</p>
</div>

{{-- KPIサマリー --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-5">
        <div class="text-xs text-gray-500">サイト数</div>
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
    <a href="{{ route('clinic.approvals.index', $clinic) }}" class="bg-white rounded-lg shadow p-5 hover:ring-2 hover:ring-indigo-300 transition">
        <div class="text-xs text-gray-500">承認待ち</div>
        <div class="text-2xl font-bold text-red-600 mt-1">{{ $totalPendingApprovals }}</div>
    </a>
    <a href="{{ route('clinic.strategy.tasks.index', $clinic) }}" class="bg-white rounded-lg shadow p-5 hover:ring-2 hover:ring-indigo-300 transition">
        <div class="text-xs text-gray-500">進行中タスク</div>
        <div class="text-2xl font-bold text-blue-600 mt-1">{{ $activeTasks }}</div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- サイト一覧（媒体一覧） --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h2 class="text-lg font-semibold">サイト（媒体）</h2>
                <a href="{{ route('clinic.sites.create', $clinic) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">+ 追加</a>
            </div>
            <div class="divide-y">
                @forelse($clinic->sites as $site)
                    <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="block px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium text-gray-900">{{ $site->name }}</span>
                                    @php
                                        $typeColors = ['hp' => 'blue', 'specialty' => 'purple', 'recruitment' => 'green', 'lp' => 'yellow'];
                                        $tc = $typeColors[$site->site_type] ?? 'gray';
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-xs bg-{{ $tc }}-100 text-{{ $tc }}-700">{{ $site->getSiteTypeLabel() }}</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">{{ $site->domain ?? '未設定' }} | {{ $site->pages_count }}ページ</p>
                            </div>
                            <span class="text-gray-400">&rarr;</span>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">サイトがありません</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 戦略タスク直近 --}}
    <div>
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h2 class="text-lg font-semibold">直近のタスク</h2>
                <a href="{{ route('clinic.strategy.tasks.index', $clinic) }}" class="text-sm text-indigo-600 hover:text-indigo-800">全て見る &rarr;</a>
            </div>
            <div class="divide-y">
                @forelse($recentTasks as $task)
                    <a href="{{ route('clinic.strategy.tasks.show', [$clinic, $task]) }}" class="block px-4 py-3 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 truncate">{{ $task->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $task->created_at->format('m/d H:i') }}</p>
                            </div>
                            @php
                                $sc = ['pending_approval' => 'yellow', 'approved' => 'blue', 'in_progress' => 'indigo', 'completed' => 'green', 'cancelled' => 'gray'][$task->status] ?? 'gray';
                            @endphp
                            <span class="px-1.5 py-0.5 rounded text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 ml-2 flex-shrink-0">{{ $task->status }}</span>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-gray-500">タスクなし</div>
                @endforelse
            </div>
        </div>

        {{-- クイックアクション --}}
        <div class="bg-white rounded-lg shadow mt-4">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">クイックアクション</h2>
            </div>
            <div class="p-4 space-y-2">
                <a href="{{ route('clinic.strategy.free-input.index', $clinic) }}" class="block w-full text-left px-4 py-2 bg-indigo-50 text-indigo-700 rounded text-sm hover:bg-indigo-100">修正依頼を送る</a>
                <a href="{{ route('clinic.strategy.dna-updates.index', $clinic) }}" class="block w-full text-left px-4 py-2 bg-gray-50 text-gray-700 rounded text-sm hover:bg-gray-100">DNA-OS更新を確認</a>
            </div>
        </div>
    </div>
</div>
@endsection
