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
            <a href="/" class="site-name">{{ $site->name }}</a>
            <nav class="global-nav">
                <ul>
                    <li><a href="/">TOP</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="lower-page">
        {{-- パンくず --}}
        <nav class="breadcrumb">
            <div class="container">
                <ol>
                    <li><a href="/">TOP</a></li>
                    <li>{{ $page->title }}</li>
                </ol>
            </div>
        </nav>

        {{-- ページヘッダー --}}
        <div class="page-header">
            <div class="container">
                <h1>{{ $page->title }}</h1>
                @if($page->meta_description)
                <p class="lead">{{ $page->meta_description }}</p>
                @endif
            </div>
        </div>

        {{-- セクション群 --}}
        <div class="page-content">
            <div class="container">
                @foreach($sections as $key => $section)
                    @if(!empty($section['html']))
                    <section class="content-section" id="{{ $key }}">
                        {!! $section['html'] !!}
                    </section>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- CTA --}}
        @if(!empty($sections['cta']['html']))
        <section class="cta">
            {!! $sections['cta']['html'] !!}
        </section>
        @endif
    </main>

    {{-- フッター --}}
    <footer class="site-footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ $site->name }}</p>
        </div>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>
