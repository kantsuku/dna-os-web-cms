<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DNA-OS Web CMS')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    {{-- ナビゲーション --}}
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-indigo-600">DNA-OS CMS</a>
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 text-sm">ダッシュボード</a>
                    <a href="{{ route('sites.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">サイト管理</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600">ログアウト</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- フラッシュメッセージ --}}
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
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- メインコンテンツ --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
