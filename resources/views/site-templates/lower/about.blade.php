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
    <header class="site-header">
        <div class="container">
            <a href="/" class="site-name">{{ $site->name }}</a>
        </div>
    </header>

    <main class="lower-page about-page">
        <nav class="breadcrumb">
            <div class="container">
                <ol>
                    <li><a href="/">TOP</a></li>
                    <li>{{ $page->title }}</li>
                </ol>
            </div>
        </nav>

        <div class="page-header">
            <div class="container">
                <h1>{{ $page->title }}</h1>
            </div>
        </div>

        <div class="page-content">
            <div class="container">
                @if(!empty($sections['philosophy']['html']))
                <section class="about-philosophy">
                    {!! $sections['philosophy']['html'] !!}
                </section>
                @endif

                @if(!empty($sections['history']['html']))
                <section class="about-history">
                    {!! $sections['history']['html'] !!}
                </section>
                @endif

                @if(!empty($sections['facility']['html']))
                <section class="about-facility">
                    {!! $sections['facility']['html'] !!}
                </section>
                @endif

                @foreach($sections as $key => $section)
                    @if(!in_array($key, ['philosophy', 'history', 'facility', 'cta']) && !empty($section['html']))
                    <section class="content-section" id="{{ $key }}">
                        {!! $section['html'] !!}
                    </section>
                    @endif
                @endforeach
            </div>
        </div>

        @if(!empty($sections['cta']['html']))
        <section class="cta">{!! $sections['cta']['html'] !!}</section>
        @endif
    </main>

    <footer class="site-footer">
        <div class="container"><p>&copy; {{ date('Y') }} {{ $site->name }}</p></div>
    </footer>
    <script src="/assets/js/main.js"></script>
</body>
</html>
