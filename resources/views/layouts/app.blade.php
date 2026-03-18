<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ACMS')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">

    {{-- ===== メインヘッダー ===== --}}
    <header class="bg-white border-b border-gray-200 relative z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-14">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('clinics.select') }}" class="text-xl font-bold text-indigo-600">ACMS</a>
                    @if(isset($clinic))
                        <span class="text-gray-300">/</span>
                        <a href="{{ route('clinic.dashboard', $clinic) }}" class="text-sm font-semibold text-gray-900 hover:text-indigo-600">{{ $clinic->name }}</a>
                    @endif
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600">ログアウト</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- ===== サブヘッダー（医院コンテキスト時のみ）===== --}}
    @if(isset($clinic))
    <nav class="bg-gray-800 relative z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-11 space-x-1 text-sm">

                {{-- 作戦本部 --}}
                <a href="{{ route('clinic.dashboard', $clinic) }}"
                   class="px-3 py-1.5 rounded text-white hover:bg-gray-700 whitespace-nowrap {{ request()->routeIs('clinic.dashboard') ? 'bg-gray-700 font-medium' : 'text-gray-300' }}">
                    作戦本部
                </a>

                <span class="text-gray-600 mx-0.5">|</span>

                {{-- 戦略実行 (サイト管理を含む) --}}
                <div class="relative" x-data="{ open: false, sub: null }">
                    <button @click="open = !open"
                            class="px-3 py-1.5 rounded hover:bg-gray-700 whitespace-nowrap {{ request()->routeIs('clinic.strategy.*', 'clinic.sites.*') ? 'bg-gray-700 font-medium text-white' : 'text-gray-300' }}">
                        戦略実行 ▾
                    </button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute left-0 top-full mt-1 w-64 bg-white rounded-lg shadow-xl border border-gray-200 text-gray-800 py-1"
                         style="display:none">
                        <div class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wide">戦略</div>
                        <a href="{{ route('clinic.strategy.tasks.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">タスク一覧</a>
                        <a href="{{ route('clinic.strategy.free-input.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">修正依頼</a>
                        <a href="{{ route('clinic.strategy.dna-updates.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">DNA-OS更新</a>
                        <a href="{{ route('clinic.strategy.channel-status.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">実行状況</a>

                        <div class="border-t my-1"></div>
                        <div class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wide">サイト管理</div>
                        @foreach($clinic->sites as $s)
                            <a href="{{ route('clinic.sites.show', [$clinic, $s]) }}" class="flex items-center px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">
                                @php
                                    $badgeColors = ['hp' => 'bg-blue-100 text-blue-700', 'specialty' => 'bg-purple-100 text-purple-700', 'recruitment' => 'bg-green-100 text-green-700', 'lp' => 'bg-yellow-100 text-yellow-700'];
                                    $badgeLabels = ['hp' => 'HP', 'specialty' => '専門', 'recruitment' => '採用', 'lp' => 'LP'];
                                @endphp
                                <span class="inline-block w-8 text-center text-xs font-semibold px-1 py-0.5 rounded {{ $badgeColors[$s->site_type] ?? 'bg-gray-100 text-gray-600' }} mr-2">{{ $badgeLabels[$s->site_type] ?? '他' }}</span>
                                <span>{{ $s->name }}</span>
                            </a>
                        @endforeach
                        <a href="{{ route('clinic.sites.create', $clinic) }}" class="block px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 font-medium">+ サイト追加</a>
                    </div>
                </div>

                <span class="text-gray-600 mx-0.5">|</span>

                {{-- 承認 --}}
                <a href="{{ route('clinic.approvals.index', $clinic) }}"
                   class="px-3 py-1.5 rounded hover:bg-gray-700 whitespace-nowrap {{ request()->routeIs('clinic.approvals.*') ? 'bg-gray-700 font-medium text-white' : 'text-gray-300' }}">
                    承認
                </a>

                <span class="text-gray-600 mx-0.5">|</span>

                {{-- デザイン --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="px-3 py-1.5 rounded hover:bg-gray-700 whitespace-nowrap {{ request()->routeIs('clinic.design.*') ? 'bg-gray-700 font-medium text-white' : 'text-gray-300' }}">
                        デザイン ▾
                    </button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute left-0 top-full mt-1 w-52 bg-white rounded-lg shadow-xl border border-gray-200 text-gray-800 py-1"
                         style="display:none">
                        <a href="{{ route('clinic.design.tokens', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">医院トンマナ / トークン</a>
                        <a href="{{ route('clinic.design.components', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">コンポーネント</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- ===== パンくず/コンテキストバー ===== --}}
    @if(isset($site) && request()->routeIs('clinic.sites.pages.*', 'clinic.sites.exceptions.*', 'clinic.sites.publish.*', 'clinic.sites.show', 'clinic.sites.edit'))
    <div class="bg-gray-100 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-9 text-xs space-x-3">
                @php
                    $badgeColors = ['hp' => 'bg-blue-100 text-blue-700', 'specialty' => 'bg-purple-100 text-purple-700', 'recruitment' => 'bg-green-100 text-green-700', 'lp' => 'bg-yellow-100 text-yellow-700'];
                    $badgeLabels = ['hp' => 'HP', 'specialty' => '専門', 'recruitment' => '採用', 'lp' => 'LP'];
                @endphp
                <span class="font-semibold px-1.5 py-0.5 rounded {{ $badgeColors[$site->site_type] ?? 'bg-gray-100 text-gray-600' }}">{{ $badgeLabels[$site->site_type] ?? '他' }}</span>
                <span class="font-medium text-gray-700">{{ $site->name }}</span>
                <span class="text-gray-400">{{ $site->domain ?? '' }}</span>

                <span class="text-gray-300 mx-1">|</span>

                <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}"
                   class="px-2 py-1 rounded {{ request()->routeIs('clinic.sites.show') ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">概要</a>
                <a href="{{ route('clinic.sites.pages.index', [$clinic, $site]) }}"
                   class="px-2 py-1 rounded {{ request()->routeIs('clinic.sites.pages.*') ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">ページ</a>
                <a href="{{ route('clinic.sites.exceptions.index', [$clinic, $site]) }}"
                   class="px-2 py-1 rounded {{ request()->routeIs('clinic.sites.exceptions.*') ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">例外コンテンツ</a>
                <a href="{{ route('clinic.sites.publish.index', [$clinic, $site]) }}"
                   class="px-2 py-1 rounded {{ request()->routeIs('clinic.sites.publish.*') ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">公開管理</a>
                <a href="{{ route('clinic.design.site', [$clinic, $site]) }}"
                   class="px-2 py-1 rounded text-gray-500 hover:text-gray-700">デザイン設定</a>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- ===== フラッシュメッセージ ===== --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded" x-data="{ show: true }" x-show="show">
                {{ session('success') }}
                <button @click="show = false" class="float-right text-green-500">&times;</button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
        </div>
    @endif

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
