<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} | {{ $site->name }}</title>
    @if($page->meta_description)
    <meta name="description" content="{{ $page->meta_description }}">
    @endif
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    {{-- ヘッダー --}}
    <header class="site-header">
        <div class="container">
            <h1 class="site-name">{{ $site->name }}</h1>
            <nav class="global-nav">
                <ul>
                    <li><a href="/">TOP</a></li>
                </ul>
            </nav>
        </div>
    </header>

    {{-- ヒーロー --}}
    @if(!empty($sections['hero']['html']))
    <section class="hero">
        {!! $sections['hero']['html'] !!}
    </section>
    @endif

    {{-- 院長メッセージ --}}
    @if(!empty($sections['message']['html']))
    <section class="message">
        <div class="container">
            {!! $sections['message']['html'] !!}
        </div>
    </section>
    @endif

    {{-- 医院の特徴 --}}
    @if(!empty($sections['features']['html']))
    <section class="features">
        <div class="container">
            {!! $sections['features']['html'] !!}
        </div>
    </section>
    @endif

    {{-- 診療科目 --}}
    @if(!empty($sections['treatments']['html']))
    <section class="treatments">
        <div class="container">
            {!! $sections['treatments']['html'] !!}
        </div>
    </section>
    @endif

    {{-- スタッフ --}}
    @if(!empty($sections['staff']['html']))
    <section class="staff">
        <div class="container">
            {!! $sections['staff']['html'] !!}
        </div>
    </section>
    @endif

    {{-- アクセス --}}
    @if(!empty($sections['access']['html']))
    <section class="access">
        <div class="container">
            {!! $sections['access']['html'] !!}
        </div>
    </section>
    @endif

    {{-- お知らせ --}}
    @if(!empty($sections['news']['html']))
    <section class="news">
        <div class="container">
            {!! $sections['news']['html'] !!}
        </div>
    </section>
    @endif

    {{-- CTA --}}
    @if(!empty($sections['cta']['html']))
    <section class="cta">
        {!! $sections['cta']['html'] !!}
    </section>
    @endif

    {{-- フッター --}}
    <footer class="site-footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ $site->name }}</p>
        </div>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>
