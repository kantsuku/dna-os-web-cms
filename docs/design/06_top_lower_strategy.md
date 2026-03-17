# 6. TOP自由 / 下層構造化の実装方針

## 基本思想

```
TOPページ  = 医院の「顔」→ 自由度最優先。テンプレートを個別カスタム可能
下層ページ  = 情報の「棚」→ 構造化優先。共通テンプレートで量産・品質統一
```

## TOPページの設計

### テンプレート戦略

```
resources/views/sites/
├── templates/
│   ├── top/
│   │   ├── default.blade.php          ← デフォルトTOP（新規サイト用）
│   │   ├── premium.blade.php          ← プレミアムTOP
│   │   └── custom/
│   │       ├── clinic_abc.blade.php   ← 医院ABC専用TOP
│   │       └── clinic_xyz.blade.php   ← 医院XYZ専用TOP
│   └── lower/
│       ├── treatment.blade.php        ← 診療科目系（インプラント等）
│       ├── about.blade.php            ← 医院紹介系
│       ├── staff.blade.php            ← スタッフ紹介
│       ├── access.blade.php           ← アクセス
│       ├── blog_index.blade.php       ← ブログ一覧
│       ├── blog_single.blade.php      ← ブログ記事
│       ├── news.blade.php             ← お知らせ
│       └── generic.blade.php          ← 汎用下層
```

### TOPのセクション構成例

```php
// sites テーブルの template_set = 'default' の場合
// pages テーブルの template_name = 'top/default'

// default.blade.php のセクションスロット:
@section('hero')         {{-- ヒーロービジュアル --}}
@section('message')      {{-- 院長メッセージ --}}
@section('features')     {{-- 医院の特徴 3〜5つ --}}
@section('treatments')   {{-- 診療科目カード --}}
@section('staff')        {{-- スタッフ紹介 --}}
@section('access')       {{-- アクセス・診療時間 --}}
@section('news')         {{-- お知らせ --}}
@section('cta')          {{-- 予約CTA --}}
```

### TOPカスタムの仕組み

1. **ベーステンプレートから派生** — `top/default.blade.php` を `@extends` してセクション単位でオーバーライド
2. **セクションの追加・削除・並べ替え** — CMS画面(C2)から操作
3. **完全カスタム** — `top/custom/clinic_xxx.blade.php` を直接作成。セクションスロットさえ定義すればCMSからコンテンツ差し込み可能

```php
// custom/clinic_abc.blade.php の例
@extends('sites.templates.top._base')

@section('layout')
<div class="clinic-abc-unique-layout">
    {!! $sections['hero']->render() !!}

    {{-- この医院固有のセクション --}}
    <div class="split-layout">
        {!! $sections['features']->render() !!}
        {!! $sections['video_tour']->render() !!}  {{-- この医院だけの動画ツアー --}}
    </div>

    {!! $sections['treatments']->render() !!}
    {!! $sections['staff']->render() !!}
    {!! $sections['access']->render() !!}
</div>
@endsection
```

## 下層ページの設計

### 共通構造

すべての下層ページは同じ骨格を共有する:

```php
// lower/_base.blade.php
<main>
    {{-- パンくず --}}
    @include('components.breadcrumb', ['page' => $page])

    {{-- ページヘッダー --}}
    <header class="page-header">
        <h1>{{ $page->title }}</h1>
        @if($page->meta_description)
            <p class="lead">{{ $page->meta_description }}</p>
        @endif
    </header>

    {{-- セクション群（CMS定義順に描画） --}}
    @foreach($sections as $section)
        @include('components.section_renderer', ['section' => $section])
    @endforeach

    {{-- サイドバー（任意） --}}
    @hasSection('sidebar')
        <aside>@yield('sidebar')</aside>
    @endif

    {{-- 共通CTA --}}
    @include('components.cta', ['site' => $site])
</main>
```

### セクションレンダラー

```php
// components/section_renderer.blade.php
@switch($section->section_key)
    @case('faq')
        @include('components.sections.faq', ['content' => $section->activeContent()])
        @break
    @case('price_table')
        @include('components.sections.price_table', ['content' => $section->activeContent()])
        @break
    @case('before_after')
        @include('components.sections.before_after', ['content' => $section->activeContent()])
        @break
    @default
        @include('components.sections.richtext', ['content' => $section->activeContent()])
@endswitch
```

### 下層テンプレートの使い分け

| テンプレート | 用途 | 固有セクション |
|---|---|---|
| `treatment.blade.php` | 診療科目ページ | 料金表、症例、Q&A |
| `about.blade.php` | 医院紹介 | 理念、沿革、設備 |
| `staff.blade.php` | スタッフ紹介 | スタッフカード、メッセージ |
| `access.blade.php` | アクセス | 地図埋め込み、診療時間表 |
| `blog_index.blade.php` | ブログ一覧 | ページネーション、カテゴリフィルター |
| `blog_single.blade.php` | ブログ記事 | 投稿本文、関連記事 |
| `generic.blade.php` | その他 | リッチテキストのみ |

## ビルドとデプロイ

### CMS → 公開サイト のビルドフロー

```
1. CMS がページ + セクション + アクティブバリアント を収集
2. Blade テンプレートを PHP でレンダリング → HTML生成
3. HTML + CSS + JS + 画像 をビルド成果物として保存
4. FTP/SSH で XServer にアップロード
```

### 公開サイト側のファイル構成

```
public_html/
├── index.php              ← TOPページ（静的HTML or 軽量PHPラッパー）
├── implant/
│   └── index.php          ← 下層ページ
├── orthodontics/
│   └── index.php
├── blog/
│   ├── index.php          ← 一覧
│   └── {slug}/
│       └── index.php      ← 個別記事
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── .htaccess              ← URLリライト
```

### 軽量PHPラッパー

公開サイトのindex.phpは極力シンプル。ロジックは持たない:

```php
<?php
// index.php — CMS がビルド時に生成
// ロジックなし。表示のみ。
?>
<!DOCTYPE html>
<html lang="ja">
<!-- CMS がレンダリングしたHTML がそのまま入る -->
<?php include __DIR__ . '/_rendered/implant.html'; ?>
</html>
```
