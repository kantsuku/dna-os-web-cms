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

    {{-- メインヘッダー --}}
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-14">
                <div class="flex items-center space-x-6">
                    <a href="{{ route('clinics.select') }}" class="text-xl font-bold text-indigo-600">ACMS</a>
                    @if(isset($clinic))
                        <span class="text-gray-300">|</span>
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
    </nav>

    {{-- サブヘッダー（医院コンテキスト時のみ） --}}
    @if(isset($clinic))
    <nav class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-10 space-x-1 text-sm overflow-x-auto">

                {{-- 作戦本部 --}}
                <a href="{{ route('clinic.dashboard', $clinic) }}"
                   class="px-3 py-1.5 rounded hover:bg-gray-700 whitespace-nowrap {{ request()->routeIs('clinic.dashboard') ? 'bg-gray-700 font-medium' : '' }}">
                    作戦本部
                </a>

                <span class="text-gray-600">|</span>

                {{-- 戦略実行 --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="px-3 py-1.5 rounded hover:bg-gray-700 whitespace-nowrap {{ request()->routeIs('clinic.strategy.*') ? 'bg-gray-700 font-medium' : '' }}">
                        戦略実行 ▾
                    </button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute z-20 mt-1 w-52 bg-white border border-gray-200 rounded shadow-lg text-gray-800" style="display:none">
                        <a href="{{ route('clinic.strategy.tasks.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-gray-50">タスク一覧</a>
                        <a href="{{ route('clinic.strategy.free-input.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-gray-50">修正依頼</a>
                        <a href="{{ route('clinic.strategy.dna-updates.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-gray-50">DNA-OS更新</a>
                        <a href="{{ route('clinic.strategy.channel-status.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-gray-50">実行状況</a>
                    </div>
                </div>

                {{-- サイト管理 --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="px-3 py-1.5 rounded hover:bg-gray-700 whitespace-nowrap {{ request()->routeIs('clinic.sites.*') ? 'bg-gray-700 font-medium' : '' }}">
                        サイト管理 ▾
                    </button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute z-20 mt-1 w-60 bg-white border border-gray-200 rounded shadow-lg text-gray-800" style="display:none">
                        @if(isset($clinic) && $clinic->sites->count() > 0)
                            @foreach($clinic->sites as $s)
                                <a href="{{ route('clinic.sites.show', [$clinic, $s]) }}" class="block px-4 py-2 text-sm hover:bg-gray-50">
                                    @php
                                        $typeIcons = ['hp' => 'HP', 'specialty' => '専門', 'recruitment' => '採用', 'lp' => 'LP'];
                                    @endphp
                                    <span class="inline-block w-8 text-center text-xs font-medium px-1 py-0.5 rounded bg-indigo-100 text-indigo-700 mr-1">{{ $typeIcons[$s->site_type] ?? '他' }}</span>
                                    {{ $s->name }}
                                </a>
                            @endforeach
                            <div class="border-t">
                                <a href="{{ route('clinic.sites.create', $clinic) }}" class="block px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50">+ サイト追加</a>
                            </div>
                        @else
                            <a href="{{ route('clinic.sites.create', $clinic) }}" class="block px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50">+ 最初のサイトを作成</a>
                        @endif
                    </div>
                </div>

                {{-- 承認 --}}
                <a href="{{ route('clinic.approvals.index', $clinic) }}"
                   class="px-3 py-1.5 rounded hover:bg-gray-700 whitespace-nowrap {{ request()->routeIs('clinic.approvals.*') ? 'bg-gray-700 font-medium' : '' }}">
                    承認
                </a>

                {{-- デザイン --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="px-3 py-1.5 rounded hover:bg-gray-700 whitespace-nowrap {{ request()->routeIs('clinic.design.*') ? 'bg-gray-700 font-medium' : '' }}">
                        デザイン ▾
                    </button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute z-20 mt-1 w-48 bg-white border border-gray-200 rounded shadow-lg text-gray-800" style="display:none">
                        <a href="{{ route('clinic.design.tokens', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-gray-50">トークン</a>
                        <a href="{{ route('clinic.design.components', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-gray-50">コンポーネント</a>
                    </div>
                </div>

                {{-- サイトコンテキストナビ（サイト選択中の場合） --}}
                @if(isset($site) && request()->routeIs('clinic.sites.pages.*', 'clinic.sites.exceptions.*', 'clinic.sites.publish.*'))
                    <span class="text-gray-600">|</span>
                    <span class="text-gray-400 text-xs">{{ $site->name }}:</span>
                    <a href="{{ route('clinic.sites.pages.index', [$clinic, $site]) }}"
                       class="px-2 py-1 rounded hover:bg-gray-700 text-xs whitespace-nowrap {{ request()->routeIs('clinic.sites.pages.*') ? 'bg-gray-700' : '' }}">
                        ページ
                    </a>
                    <a href="{{ route('clinic.sites.exceptions.index', [$clinic, $site]) }}"
                       class="px-2 py-1 rounded hover:bg-gray-700 text-xs whitespace-nowrap {{ request()->routeIs('clinic.sites.exceptions.*') ? 'bg-gray-700' : '' }}">
                        例外
                    </a>
                    <a href="{{ route('clinic.sites.publish.index', [$clinic, $site]) }}"
                       class="px-2 py-1 rounded hover:bg-gray-700 text-xs whitespace-nowrap {{ request()->routeIs('clinic.sites.publish.*') ? 'bg-gray-700' : '' }}">
                        公開
                    </a>
                @endif
            </div>
        </div>
    </nav>
    @endif

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
