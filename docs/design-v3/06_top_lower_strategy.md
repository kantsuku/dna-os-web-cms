# 06. TOP自由 / 下層構造化の設計

> TOPページの自由度と下層ページの量産性を両立する実装方針

---

## 1. 設計方針

### TOPページ
- **戦略・ブランド・雰囲気** の表現拠点
- デザイナーが自由にレイアウト可能
- CMS管理は **一部セクションのみ**（差し込み可能な領域を指定）
- 医院ごとに完全にカスタムでよい

### 下層ページ
- **構造化・量産・再利用** が目的
- テンプレート + セクション差し込みで管理
- AIが生成したコンテンツを流し込む
- 同じテンプレートで90サイト分を量産

---

## 2. TOPページの設計

### 2-1. TOPページの構造モデル

```
TOPページ
  ├── [固定] ヒーロー領域
  │     → 医院ごとにカスタムHTML（テンプレート選択 or フルカスタム）
  │
  ├── [CMS差し込み] セクション群
  │     → slot方式で差し込み可能な領域を定義
  │     → 例: slot="top_greeting" → 院長あいさつ
  │     → 例: slot="top_features" → 医院の特徴
  │     → 例: slot="top_treatments" → 診療メニュー一覧
  │
  ├── [固定] ブランド表現領域
  │     → デザイナーが作成、CMS管理外
  │
  └── [自動] 動的コンテンツ
        → 最新ブログ一覧
        → お知らせ一覧
        → これらはCMSから自動生成
```

### 2-2. TOPテンプレートの種類

| テンプレート | 説明 | CMS管理範囲 |
|------------|------|-----------|
| **top_standard** | 標準レイアウト（ヒーロー + セクション群） | slot差し込み領域のみ |
| **top_custom** | フルカスタムHTML | 指定slotのみ差し込み |
| **top_minimal** | ミニマルレイアウト（仮サイト向け） | ほぼ全面CMS管理 |

### 2-3. slot（スロット）方式の設計

```html
<!-- top_standard.blade.php -->
<main>
  <!-- ヒーロー: 医院カスタム -->
  @include("sites.{$site->id}.hero")

  <!-- CMS差し込みスロット: 院長あいさつ -->
  @if($page->hasSlot('top_greeting'))
    <section class="com-section">
      <div class="com-contentWidth">
        {!! $page->getSlotHtml('top_greeting') !!}
      </div>
    </section>
  @endif

  <!-- CMS差し込みスロット: 医院の特徴 -->
  @if($page->hasSlot('top_features'))
    <section class="com-section com-bgc-gray-white">
      <div class="com-contentWidth">
        {!! $page->getSlotHtml('top_features') !!}
      </div>
    </section>
  @endif

  <!-- 固定: ブランド表現 -->
  @include("sites.{$site->id}.brand_section")

  <!-- 自動: 最新ブログ -->
  @include("partials.latest_blogs", ['blogs' => $latestBlogs])

  <!-- 自動: お知らせ -->
  @include("partials.latest_news", ['news' => $latestNews])
</main>
```

### 2-4. TOPページの上書き制御
- **固定領域**: CMS管理外。HTMLファイルとして保存。AI上書き不可
- **slot領域**: CMS管理。AI生成可、人間微調整可、ロック可
- **自動領域**: CMS自動生成。人間編集不可（ソースデータを編集する）

---

## 3. 下層ページの設計

### 3-1. テンプレート一覧

| テンプレートキー | 用途 | セクション構成 |
|---------------|------|-------------|
| `treatment_detail` | 診療詳細 | ヒーロー + 導入 + こだわり + 治療の流れ + FAQ + CTA |
| `staff` | スタッフ紹介 | ヒーロー + 医師一覧 + スタッフ一覧 |
| `facility` | 設備紹介 | ヒーロー + 設備一覧 + こだわり |
| `about` | 医院紹介 | ヒーロー + 理念 + 沿革 + アクセス |
| `recruitment` | 採用 | ヒーロー + 募集要項 + 職場の魅力 + 応募フロー |
| `faq` | FAQ | ヒーロー + カテゴリ別FAQ |
| `blog_post` | ブログ | ヒーロー + 本文 + 著者カード + 関連記事 |
| `news_post` | お知らせ | ヒーロー + 本文 |
| `case_detail` | 症例 | ヒーロー + 症例情報 + 画像 + リスク表記 |
| `blog_archive` | ブログ一覧 | ヒーロー + 記事カード一覧 + ページネーション |
| `case_archive` | 症例一覧 | ヒーロー + 症例カード一覧 + ページネーション |
| `contact` | お問い合わせ | ヒーロー + フォーム |
| `generic` | 汎用 | ヒーロー + 自由セクション |

### 3-2. テンプレートの構造

```html
<!-- treatment_detail.blade.php -->
@extends('layouts.site')

@section('content')
  <!-- ヒーロー（共通コンポーネント） -->
  @include('partials.page_hero', [
    'title' => $page->title,
    'subtitle' => $page->meta['subtitle'] ?? '',
    'breadcrumb' => $breadcrumb
  ])

  <!-- セクション群（CMSから流し込み） -->
  @foreach($page->currentGeneration->sections as $section)
    {!! $section['content_html'] !!}
  @endforeach

  <!-- CTA（共通コンポーネント） -->
  @include('partials.cta_box')
@endsection
```

### 3-3. セクション差し込みの仕組み

```
DNA-OS → clinic-page-generator → マークアップHTML
  │
  │ ACMS取り込み時にセクション分割
  ▼
PageGeneration.sections = [
  {
    section_id: "sec_01",
    heading: "虫歯治療とは",
    content_html: "<section class='com-section pt0'>...<\/section>",
    lock_status: "unlocked",
    order: 1
  },
  {
    section_id: "sec_02",
    heading: "こんなお悩みはありませんか？",
    content_html: "<section class='com-section com-bgc-gray-white'>...<\/section>",
    lock_status: "unlocked",
    order: 2
  },
  ...
]
```

### 3-4. コンポーネント戦略

WP既存の `com-` プレフィックスをそのまま採用する（v2で決定済み）。

#### 理由
1. clinic-page-generatorのマークアップ出力が `com-` クラスで生成済み
2. WP既存サイトとの互換性を維持
3. 変換コストを回避

#### コンポーネント一覧（WP準拠）

| カテゴリ | コンポーネント | 用途 |
|---------|-------------|------|
| 構造 | `com-section` | セクション wrapper |
| 構造 | `com-contentWidth` | コンテンツ幅制御 |
| 見出し | `com-h2-top` / `com-h2` / `com-h3` / `com-h4` | 見出し |
| レイアウト | `com-col2` / `com-col3` | カラム |
| レイアウト | `com-col-img_right` / `com-col-img_left` | 画像+テキスト |
| リスト | `com-ul-dot` / `com-ul-check_03` / `com-ul-num01` | 各種リスト |
| コンテンツ | `com-faq` | FAQ |
| コンテンツ | `com-flow` | フロー図 |
| コンテンツ | `com-comment` | 医師コメント |
| コンテンツ | `com-point` | ポイント |
| コンテンツ | `com-note` | 注釈 |
| UI | `com-btn` | ボタン |
| 間隔 | `com-spacer-*` | スペーサー |
| 背景 | `com-bgc-*` | 背景色 |

### 3-5. CSS の構成

```
assets/css/
  ├── base.css                ← リセット + 基本スタイル
  ├── components.css          ← com-* コンポーネント定義
  │                             (WPテーマの _common.scss を移植)
  ├── tokens.css              ← デザイントークン（CSS Custom Properties）
  │                             (サイト別に上書き可能)
  └── custom.css              ← サイト固有のカスタムCSS
```

**デザイントークンの例:**
```css
:root {
  /* カラー */
  --color-main: #2c5f8a;
  --color-main-light: #e8f0f7;
  --color-sub: #8ab63f;
  --color-text: #333333;
  --color-bg-gray: #f5f5f5;

  /* タイポグラフィ */
  --font-family-base: "Noto Sans JP", sans-serif;
  --font-size-base: 16px;
  --line-height-base: 1.8;

  /* スペーシング */
  --spacer-section: 80px;
  --spacer-large: 60px;
  --spacer-medium: 40px;
  --spacer-small: 20px;

  /* レイアウト */
  --content-width: 1100px;
  --content-width-wide: 1400px;
}
```

---

## 4. CMSとフロントの接続方法

### 4-1. ビルド時結合

```
[CMS DB]
  │
  │ ビルドエンジン
  ▼
[Blade テンプレート] + [セクションHTML] + [デザイントークン]
  │
  │ Laravel Blade レンダリング
  ▼
[静的HTML]
  │
  │ FTP デプロイ
  ▼
[XServer]
```

CMS管理画面とフロント（公開サイト）は **完全に分離** されている。CMSはビルド時にHTMLを生成し、FTPで配信する。リアルタイム接続はない。

### 4-2. プレビューの仕組み
- CMSのプレビュー画面でビルド結果をインラインフレームで表示
- 公開前に最終確認可能
- プレビューはCMS内部で完結（XServerにはアップしない）

---

## 5. どこまでをCMS管理にするか

| 領域 | CMS管理 | 備考 |
|------|---------|------|
| 共通レイアウト（header/footer/nav） | ○ テンプレートとして管理 | サイト設定で切り替え |
| TOPページ固定領域 | △ ファイルとして保存 | 直接編集はデザイナー |
| TOPページslot領域 | ○ セクションとして管理 | AI生成+人間微調整 |
| 下層ページ本文 | ○ セクションとして管理 | AI生成+人間微調整 |
| メタ情報 | ○ ページ属性として管理 | AI生成+人間編集 |
| 画像 | △ パス参照のみ | 画像自体はXServer上 |
| CSS（デザイントークン） | ○ DB管理 | 管理画面で編集 |
| CSS（コンポーネント） | △ ファイルとして管理 | 更新頻度低い |
| JavaScript | × CMS管理外 | ファイルとして配置 |
| フォーム | × CMS管理外 | 外部サービス or 静的 |
