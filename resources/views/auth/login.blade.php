<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C&C — Command & Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .cc-bg {
            background: url('/img/cc-moon.png') center center / cover no-repeat;
            position: relative;
        }
        .cc-bg::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(10,14,26,0.85) 0%, rgba(26,31,58,0.7) 50%, rgba(13,17,23,0.85) 100%);
        }
        .cc-logo { font-family: 'Courier New', monospace; }
    </style>
</head>
<body class="min-h-screen flex">
    {{-- 左: 月面画像 --}}
    <div class="hidden lg:flex lg:w-3/5 cc-bg items-center justify-center">
        <div class="relative z-10 text-center px-12">
            <h1 class="cc-logo text-8xl font-bold text-white tracking-[0.3em] mb-4" style="text-shadow: 0 0 60px rgba(100,200,255,0.4), 0 0 120px rgba(100,200,255,0.15);">C&C</h1>
            <p class="text-xl text-gray-300 tracking-[0.5em] uppercase font-light">Command & Control</p>
            <p class="text-sm text-gray-500 mt-6 tracking-wider">DNA-OS 統合作戦基盤</p>
        </div>
    </div>

    {{-- 右: ログインフォーム --}}
    <div class="w-full lg:w-2/5 flex items-center justify-center bg-gray-950 px-8">
        <div class="w-full max-w-sm">
            {{-- モバイルロゴ --}}
            <div class="lg:hidden text-center mb-8">
                <h1 class="cc-logo text-5xl font-bold text-white tracking-[0.3em]" style="text-shadow: 0 0 40px rgba(100,200,255,0.3);">C&C</h1>
                <p class="text-xs text-gray-500 tracking-[0.3em] uppercase mt-2">Command & Control</p>
            </div>

            <h2 class="text-lg font-medium text-gray-200 mb-6">ログイン</h2>

            @if($errors->any())
                <div class="bg-red-900/30 border border-red-700/50 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm text-gray-400 mb-1">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="mb-5">
                    <label class="block text-sm text-gray-400 mb-1">パスワード</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="mb-6">
                    <label class="flex items-center"><input type="checkbox" name="remember" class="rounded bg-gray-800 border-gray-600 text-blue-500"><span class="ml-2 text-sm text-gray-500">ログインを記憶</span></label>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-lg font-medium transition">ログイン</button>
            </form>

            <p class="text-center text-xs text-gray-600 mt-8">DNA-OS 統合作戦基盤 v3.1</p>
        </div>
    </div>
</body>
</html>
