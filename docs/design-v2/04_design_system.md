# ACMS 設計 v2 — デザインシステム

## 思想

```
コンテンツ（HTML構造）  ×  デザイン（CSS）  =  公開サイト
      ↑                       ↑
  AIが生成                人間が管理
```

コンテンツとデザインを完全に分離する。同じコンテンツHTMLに異なるデザインを当てられる。

## コンポーネント体系

### WPテーマからの移行マッピング

| WPコンポーネント | → | ACMSコンポーネント | カテゴリ |
|---|---|---|---|
| `com-section` | → | `acms-section` | layout |
| `com-bgc-gray-white` | → | `acms-section--alt` | layout |
| `com-h2-top` + `com-h2-top-desc` | → | `acms-page-header` | heading |
| `com-h2` | → | `acms-h2` | heading |
| `com-h3` | → | `acms-h3` | heading |
| `com-h4` | → | `acms-h4` | heading |
| `com-col-img_right` | → | `acms-media --right` | layout |
| `com-col-img_left` | → | `acms-media --left` | layout |
| `com-col2` / `com-col3` | → | `acms-grid --col-2` / `--col-3` | layout |
| `com-ul-check_03` | → | `acms-checklist` | content |
| `com-ul-dot` | → | `acms-list` | content |
| `com-flow` | → | `acms-flow` | content |
| `com-faq` | → | `acms-faq` | content |
| `com-point` | → | `acms-callout --point` | content |
| `com-comment` | → | `acms-callout --comment` | content |
| `com-note` | → | `acms-note` | content |
| `[com_box03_html]` | → | `acms-cta` | cta |
| `[author_card]` | → | `acms-author` | content |

### コンポーネントのCSS設計

各コンポーネントはCSS Custom Propertiesで制御される。

```css
/* acms-h2 のデフォルトスタイル */
.acms-h2 {
  --acms-h2-font-size: var(--acms-font-size-xl, 1.5rem);
  --acms-h2-font-weight: var(--acms-font-weight-bold, 700);
  --acms-h2-color: var(--acms-color-text, #1f2937);
  --acms-h2-border-color: var(--acms-color-primary, #2563eb);
  --acms-h2-border-width: 2px;
  --acms-h2-padding-bottom: 0.5rem;
  --acms-h2-margin-bottom: 1rem;

  font-size: var(--acms-h2-font-size);
  font-weight: var(--acms-h2-font-weight);
  color: var(--acms-h2-color);
  border-bottom: var(--acms-h2-border-width) solid var(--acms-h2-border-color);
  padding-bottom: var(--acms-h2-padding-bottom);
  margin-bottom: var(--acms-h2-margin-bottom);
}
```

### サイトごとのオーバーライド

site_designs.tokens でデザイントークンを上書き → 全コンポーネントに波及。
site_designs.component_styles で特定コンポーネントのCSS変数を個別上書き。

```json
// site_designs.tokens の例
{
  "color-primary": "#2563eb",
  "color-primary-dark": "#1d4ed8",
  "color-text": "#1f2937",
  "color-bg": "#ffffff",
  "font-base": "'Noto Sans JP', sans-serif",
  "font-heading": "'Noto Serif JP', serif",
  "radius-base": "8px",
  "spacing-section": "60px"
}

// site_designs.component_styles の例（acms-h2だけスタイルを変えたい場合）
{
  "acms-h2": {
    "--acms-h2-border-width": "3px",
    "--acms-h2-border-color": "var(--acms-color-accent)"
  }
}
```

## デザイントークンの階層

```
グローバルデフォルト（design_tokens テーブル）
    ↓ 上書き
サイトデザイン（site_designs.tokens）
    ↓ 上書き
コンポーネント個別（site_designs.component_styles）
    ↓ 上書き
カスタムCSS（site_designs.custom_css）
```

## ビルド時のCSS生成

```
1. グローバルデフォルトの :root 変数を生成
2. サイトデザインのトークンで上書き
3. コンポーネント個別スタイルを生成
4. カスタムCSSを末尾に追加
5. 1つのCSSファイルに結合 → 公開サイトに配置
```

## 管理画面UI（段階的実装）

### Phase 1（MVP）: トークン管理
- カラーパレット: カラーピッカーでprimary/text/bg等を変更
- フォント: Google Fonts選択UI
- スペーシング: スライダーで調整
- リアルタイムプレビュー（コンポーネント一覧で即反映確認）

### Phase 2: コンポーネントスタイル調整
- コンポーネントごとのCSS変数をGUIで微調整
- Before/Afterプレビュー
- プリセット保存機能

### Phase 3: レイアウトエディタ
- TOPページのセクション並べ替え（ドラッグ&ドロップ）
- セクションの表示/非表示切替
- レスポンシブプレビュー（PC/タブレット/SP）
