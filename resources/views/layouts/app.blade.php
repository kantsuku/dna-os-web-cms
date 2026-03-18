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
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-6">
                    <a href="{{ route('clinics.select') }}" class="text-xl font-bold text-indigo-600">ACMS</a>

                    @if(isset($clinic))
                        <span class="text-gray-300">|</span>
                        <a href="{{ route('clinic.dashboard', $clinic) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">{{ $clinic->name }}</a>

                        {{-- 戦略 --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-gray-600 hover:text-gray-900 text-sm">戦略 ▾</button>
                            <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg" style="display:none">
                                <a href="{{ route('clinic.strategy.tasks.index', $clinic) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">タスク一覧</a>
                                <a href="{{ route('clinic.strategy.free-input.index', $clinic) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">修正依頼</a>
                                <a href="{{ route('clinic.strategy.dna-updates.index', $clinic) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">DNA-OS更新</a>
                                <a href="{{ route('clinic.strategy.channel-status.index', $clinic) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">実行状況</a>
                            </div>
                        </div>

                        {{-- サイト --}}
                        @if(isset($clinic) && $clinic->sites->count() > 0)
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="text-gray-600 hover:text-gray-900 text-sm">サイト ▾</button>
                                <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-56 bg-white border border-gray-200 rounded shadow-lg" style="display:none">
                                    @foreach($clinic->sites as $s)
                                        <a href="{{ route('clinic.sites.show', [$clinic, $s]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            {{ $s->name }}
                                            <span class="text-xs text-gray-400 ml-1">{{ $s->getSiteTypeLabel() }}</span>
                                        </a>
                                    @endforeach
                                    <div class="border-t">
                                        <a href="{{ route('clinic.sites.create', $clinic) }}" class="block px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50">+ サイト追加</a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('clinic.approvals.index', $clinic) }}" class="text-gray-600 hover:text-gray-900 text-sm">承認</a>

                        {{-- デザイン --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-gray-600 hover:text-gray-900 text-sm">デザイン ▾</button>
                            <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg" style="display:none">
                                <a href="{{ route('clinic.design.tokens', $clinic) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">トークン</a>
                                <a href="{{ route('clinic.design.components', $clinic) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">コンポーネント</a>
                            </div>
                        </div>
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
