<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'C&C')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .cc-header {
            background: linear-gradient(135deg, #0a0e1a 0%, #1a1f3a 30%, #2a2040 60%, #0d1117 100%);
            position: relative;
            overflow: hidden;
        }
        .cc-header::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(1px 1px at 10% 20%, rgba(255,255,255,0.4) 0%, transparent 100%),
                radial-gradient(1px 1px at 30% 60%, rgba(255,255,255,0.3) 0%, transparent 100%),
                radial-gradient(1px 1px at 50% 10%, rgba(255,255,255,0.5) 0%, transparent 100%),
                radial-gradient(1px 1px at 70% 40%, rgba(255,255,255,0.3) 0%, transparent 100%),
                radial-gradient(1px 1px at 90% 70%, rgba(255,255,255,0.4) 0%, transparent 100%),
                radial-gradient(2px 2px at 20% 80%, rgba(255,255,255,0.2) 0%, transparent 100%),
                radial-gradient(2px 2px at 60% 30%, rgba(255,255,255,0.15) 0%, transparent 100%),
                radial-gradient(1.5px 1.5px at 80% 90%, rgba(255,255,255,0.3) 0%, transparent 100%),
                radial-gradient(1px 1px at 40% 50%, rgba(255,255,255,0.25) 0%, transparent 100%),
                radial-gradient(1px 1px at 85% 15%, rgba(255,255,255,0.35) 0%, transparent 100%);
            pointer-events: none;
        }
        .cc-header::after {
            content: '';
            position: absolute; bottom: 0; left: 0; right: 0;
            height: 40%;
            background: radial-gradient(ellipse 120% 80% at 50% 100%, rgba(180,170,160,0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .cc-logo { font-family: 'Courier New', monospace; letter-spacing: 0.2em; }
        .cc-glow { text-shadow: 0 0 30px rgba(100,200,255,0.5), 0 0 60px rgba(100,200,255,0.2), 0 0 100px rgba(100,200,255,0.1); }
    </style>
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- ===== C&C メインヘッダー ===== --}}
    <header class="cc-header relative z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 relative z-10">
                <div class="flex items-center space-x-5">
                    <a href="{{ route('clinics.select') }}" class="flex items-center space-x-3 group">
                        <span class="cc-logo text-4xl font-black text-white cc-glow tracking-widest group-hover:scale-105 transition-transform">C&C</span>
                        <span class="text-[10px] text-gray-400 hidden sm:block leading-tight uppercase tracking-widest">Command<br>&amp; Control</span>
                    </a>
                    @if(isset($clinic))
                        <span class="text-gray-600">|</span>
                        <a href="{{ route('clinic.dashboard', $clinic) }}" class="text-sm font-medium text-gray-200 hover:text-white">{{ $clinic->name }}</a>
                    @endif
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-xs text-gray-400">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-gray-500 hover:text-red-400">ログアウト</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- ===== サブヘッダー（指揮統制バー） ===== --}}
    @if(isset($clinic))
    <nav class="bg-gray-900/95 backdrop-blur border-b border-gray-700/50 relative z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-10 space-x-1 text-sm">

                <a href="{{ route('clinic.dashboard', $clinic) }}"
                   class="px-3 py-1.5 rounded text-gray-300 hover:bg-white/10 hover:text-white whitespace-nowrap {{ request()->routeIs('clinic.dashboard') ? 'bg-white/10 text-white font-medium' : '' }}">
                    作戦本部
                </a>

                <span class="text-gray-700 mx-0.5">|</span>

                {{-- 戦略実行 --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="px-3 py-1.5 rounded hover:bg-white/10 whitespace-nowrap {{ request()->routeIs('clinic.strategy.*', 'clinic.sites.*') ? 'bg-white/10 text-white font-medium' : 'text-gray-300' }}">
                        戦略実行 ▾
                    </button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute left-0 top-full mt-1.5 w-64 bg-white rounded-lg shadow-xl border border-gray-200 text-gray-800 py-1 z-50"
                         style="display:none">
                        <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">戦略</div>
                        <a href="{{ route('clinic.strategy.tasks.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">タスク一覧</a>
                        <a href="{{ route('clinic.strategy.free-input.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">修正依頼</a>
                        <a href="{{ route('clinic.strategy.dna-updates.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">DNA-OS更新</a>
                        <a href="{{ route('clinic.strategy.channel-status.index', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">実行状況</a>
                        <div class="border-t my-1"></div>
                        <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">チャネル</div>
                        @foreach($clinic->sites as $s)
                            <a href="{{ route('clinic.sites.show', [$clinic, $s]) }}" class="flex items-center px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-700">
                                @php
                                    $bc = ['hp'=>'bg-blue-500','specialty'=>'bg-purple-500','recruitment'=>'bg-green-500','lp'=>'bg-yellow-500','gbp'=>'bg-red-500','instagram'=>'bg-pink-500','blog_media'=>'bg-orange-500'];
                                    $bl = ['hp'=>'HP','specialty'=>'専門','recruitment'=>'採用','lp'=>'LP','gbp'=>'GBP','instagram'=>'Insta','blog_media'=>'メディア'];
                                @endphp
                                <span class="inline-block w-2 h-2 rounded-full {{ $bc[$s->site_type] ?? 'bg-gray-400' }} mr-2"></span>
                                <span>{{ $s->name }}</span>
                                <span class="ml-auto text-[10px] text-gray-400">{{ $bl[$s->site_type] ?? $s->site_type }}</span>
                            </a>
                        @endforeach
                        <div class="border-t"><a href="{{ route('clinic.sites.create', $clinic) }}" class="block px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50">+ チャネル追加</a></div>
                    </div>
                </div>

                <span class="text-gray-700 mx-0.5">|</span>

                <a href="{{ route('clinic.approvals.index', $clinic) }}"
                   class="px-3 py-1.5 rounded hover:bg-white/10 whitespace-nowrap {{ request()->routeIs('clinic.approvals.*') ? 'bg-white/10 text-white font-medium' : 'text-gray-300' }}">
                    承認
                </a>

                <span class="text-gray-700 mx-0.5">|</span>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="px-3 py-1.5 rounded hover:bg-white/10 whitespace-nowrap {{ request()->routeIs('clinic.design.*') ? 'bg-white/10 text-white font-medium' : 'text-gray-300' }}">
                        デザイン ▾
                    </button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute left-0 top-full mt-1.5 w-52 bg-white rounded-lg shadow-xl border border-gray-200 text-gray-800 py-1 z-50"
                         style="display:none">
                        <a href="{{ route('clinic.design.tokens', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50">トンマナ / トークン</a>
                        <a href="{{ route('clinic.design.components', $clinic) }}" class="block px-4 py-2 text-sm hover:bg-indigo-50">コンポーネント</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- コンテキストバー --}}
    @if(isset($site) && request()->routeIs('clinic.sites.pages.*', 'clinic.sites.exceptions.*', 'clinic.sites.publish.*', 'clinic.sites.show', 'clinic.sites.edit'))
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-9 text-xs space-x-3">
                @php
                    $bc = ['hp'=>'bg-blue-500','specialty'=>'bg-purple-500','recruitment'=>'bg-green-500','lp'=>'bg-yellow-500','gbp'=>'bg-red-500','instagram'=>'bg-pink-500','blog_media'=>'bg-orange-500'];
                @endphp
                <span class="w-2 h-2 rounded-full {{ $bc[$site->site_type] ?? 'bg-gray-400' }}"></span>
                <span class="font-semibold text-gray-700">{{ $site->name }}</span>
                <span class="text-gray-300">|</span>
                <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="px-2 py-1 rounded {{ request()->routeIs('clinic.sites.show') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-700' }}">サイトマップ</a>
                <a href="{{ route('clinic.sites.pages.index', [$clinic, $site]) }}" class="px-2 py-1 rounded {{ request()->routeIs('clinic.sites.pages.*') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-700' }}">ページ</a>
                <a href="{{ route('clinic.sites.exceptions.index', [$clinic, $site]) }}" class="px-2 py-1 rounded {{ request()->routeIs('clinic.sites.exceptions.*') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-700' }}">例外</a>
                <a href="{{ route('clinic.sites.publish.index', [$clinic, $site]) }}" class="px-2 py-1 rounded {{ request()->routeIs('clinic.sites.publish.*') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-700' }}">公開</a>
                <a href="{{ route('clinic.design.site', [$clinic, $site]) }}" class="px-2 py-1 rounded text-gray-500 hover:text-gray-700">デザイン</a>
            </div>
        </div>
    </div>
    @endif
    @endif

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4"><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded" x-data="{ show: true }" x-show="show">{{ session('success') }}<button @click="show = false" class="float-right text-green-500">&times;</button></div></div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4"><div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div></div>
    @endif

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">@yield('content')</main>

    @livewireScripts
</body>
</html>
