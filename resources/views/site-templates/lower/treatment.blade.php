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
            <nav class="global-nav">
                <ul>
                    <li><a href="/">TOP</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="lower-page treatment-page">
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
                @if($page->meta_description)
                <p class="lead">{{ $page->meta_description }}</p>
                @endif
            </div>
        </div>

        <div class="page-content">
            <div class="container">
                {{-- 診療概要 --}}
                @if(!empty($sections['overview']['html']))
                <section class="treatment-overview">
                    {!! $sections['overview']['html'] !!}
                </section>
                @endif

                {{-- 当院の特徴 --}}
                @if(!empty($sections['features']['html']))
                <section class="treatment-features">
                    {!! $sections['features']['html'] !!}
                </section>
                @endif

                {{-- 治療の流れ --}}
                @if(!empty($sections['flow']['html']))
                <section class="treatment-flow">
                    {!! $sections['flow']['html'] !!}
                </section>
                @endif

                {{-- 料金表 --}}
                @if(!empty($sections['price_table']['html']))
                <section class="treatment-price">
                    {!! $sections['price_table']['html'] !!}
                </section>
                @endif

                {{-- 症例 --}}
                @if(!empty($sections['cases']['html']))
                <section class="treatment-cases">
                    {!! $sections['cases']['html'] !!}
                </section>
                @endif

                {{-- Q&A --}}
                @if(!empty($sections['faq']['html']))
                <section class="treatment-faq">
                    {!! $sections['faq']['html'] !!}
                </section>
                @endif

                {{-- その他セクション --}}
                @foreach($sections as $key => $section)
                    @if(!in_array($key, ['overview', 'features', 'flow', 'price_table', 'cases', 'faq', 'cta']) && !empty($section['html']))
                    <section class="content-section" id="{{ $key }}">
                        {!! $section['html'] !!}
                    </section>
                    @endif
                @endforeach
            </div>
        </div>

        @if(!empty($sections['cta']['html']))
        <section class="cta">
            {!! $sections['cta']['html'] !!}
        </section>
        @endif
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ $site->name }}</p>
        </div>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>
