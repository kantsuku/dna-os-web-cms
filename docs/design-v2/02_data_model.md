# ACMS 設計 v2 — データモデル

## エンティティ概念図

```
Site 1──* Page 1──* PageGeneration (世代)
  │         │              │
  │         │         最新世代 → 公開中の世代
  │         │              │
  │         │         human_patch (差分JSON、あれば)
  │         │
  │         *── PageType (top / lower / blog / news / exception)
  │
  *── DeployRecord (デプロイ履歴)
  │
  *── SiteDesign (デザイントークン + コンポーネントスタイル)

DesignSystem (グローバル)
  ├── DesignToken (カラー/フォント/スペーシング)
  └── Component (com-h2, com-faq, com-flow 等)
         └── ComponentStyle (コンポーネントごとのCSS変数)
```

## v1との主な違い

| v1 | v2 |
|---|---|
| Section + ContentVariant（セクション単位の複雑な管理） | **PageGeneration（ページ×世代のシンプルな管理）** |
| 上書き制御ルール（4段階） | **不要。AI再生成+人間パッチの2層構造** |
| 承認フロー（reviewer ロール） | **公開ボタンのみ** |
| Bladeテンプレートに差し込み | **デザインシステム×コンテンツHTMLの結合** |

## テーブル定義

### sites（サイト）

医院1つ = サイト1つ。

| カラム | 型 | 説明 |
|---|---|---|
| id | BIGINT PK | |
| clinic_id | VARCHAR(50) | DNA-OS clinic_id |
| name | VARCHAR(255) | 医院名 |
| domain | VARCHAR(255) | 公開ドメイン |
| xserver_host | VARCHAR(255) | FTP接続先 |
| xserver_ftp_user | VARCHAR(255) | |
| xserver_ftp_pass | TEXT | 暗号化保存 |
| xserver_deploy_path | VARCHAR(500) | |
| design_id | FK → site_designs | 適用中のデザイン |
| gas_generator_url | VARCHAR(500) | 記事生成GAS WebApp URL |
| status | ENUM(active, maintenance, archived) | |
| timestamps | | |

### pages（ページ）

サイト内の1ページ。URLと対応。

| カラム | 型 | 説明 |
|---|---|---|
| id | BIGINT PK | |
| site_id | FK → sites | |
| slug | VARCHAR(255) | URLパス |
| title | VARCHAR(500) | ページタイトル |
| page_type | ENUM(top, lower, blog, news, exception) | |
| treatment_key | VARCHAR(100) | 記事生成の internal_key（診療ページの場合） |
| sort_order | INT | ナビ順序 |
| current_generation_id | FK → page_generations | 現在公開中の世代 |
| status | ENUM(draft, ready, published, archived) | |
| timestamps | | |

### page_generations（ページ世代）

ページのコンテンツの1世代。AI生成のたびに新しい世代ができる。

| カラム | 型 | 説明 |
|---|---|---|
| id | BIGINT PK | |
| page_id | FK → pages | |
| generation | INT | 世代番号（1, 2, 3...） |
| source | ENUM(ai_generated, manual, imported) | 生成元 |
| content_html | LONGTEXT | コンポーネントHTML（AI出力そのまま） |
| content_text | LONGTEXT | プレーンテキスト版（検索・比較用） |
| meta_json | JSON | 生成メタ情報（clinic_id, treatment_key, generated_at, model等） |
| human_patch | JSON | 人間修正の差分（なければnull） |
| patch_reason | TEXT | 修正理由（human_patchがある場合） |
| patched_by | FK → users | |
| patched_at | TIMESTAMP | |
| final_html | LONGTEXT | 最終HTML（content_html + human_patch 適用済み。patchなければcontent_htmlと同一） |
| status | ENUM(received, ready, published, superseded, rolled_back) | |
| timestamps | | |

### deploy_records（デプロイ記録）

| カラム | 型 | 説明 |
|---|---|---|
| id | BIGINT PK | |
| site_id | FK → sites | |
| generation_snapshot | JSON | 各page_id → generation_id のマッピング |
| build_path | VARCHAR(500) | ビルド成果物パス |
| deploy_status | ENUM(pending, building, deploying, success, failed, rolled_back) | |
| deployed_by | FK → users | |
| deployed_at | TIMESTAMP | |
| rollback_of | FK → deploy_records | |
| error_log | TEXT | |
| timestamps | | |

### site_designs（サイトデザイン）

サイトごとのデザイン設定。

| カラム | 型 | 説明 |
|---|---|---|
| id | BIGINT PK | |
| site_id | FK → sites | |
| name | VARCHAR(255) | デザイン名（例：v1, v2） |
| tokens | JSON | デザイントークン（カラー/フォント/スペーシング） |
| component_styles | JSON | コンポーネントごとのスタイルオーバーライド |
| layout_config | JSON | TOPページのレイアウト設定 |
| custom_css | LONGTEXT | サイト固有の追加CSS |
| status | ENUM(draft, active, archived) | |
| timestamps | | |

### design_tokens（グローバルデザイントークン）

デザインシステムの基本単位。全サイト共通のデフォルト値。

| カラム | 型 | 説明 |
|---|---|---|
| id | BIGINT PK | |
| category | VARCHAR(50) | color / font / spacing / radius / shadow |
| key | VARCHAR(100) | 例：primary, text, bg, font-base |
| value | VARCHAR(255) | 例：#2563eb, 16px |
| label | VARCHAR(255) | 管理画面での表示名 |
| sort_order | INT | |
| timestamps | | |

### components（コンポーネント定義）

ACMSのコンポーネント体系。

| カラム | 型 | 説明 |
|---|---|---|
| id | BIGINT PK | |
| key | VARCHAR(100) | 例：acms-h2, acms-faq, acms-flow |
| name | VARCHAR(255) | 表示名 |
| category | VARCHAR(50) | heading / layout / content / cta / utility |
| html_template | TEXT | デフォルトHTMLテンプレート |
| default_styles | JSON | デフォルトCSS変数 |
| preview_html | TEXT | プレビュー用サンプルHTML |
| description | TEXT | 説明 |
| sort_order | INT | |
| timestamps | | |

### exception_contents（例外コンテンツ）

症例・医療広告GL配慮。人間主導。

| カラム | 型 | 説明 |
|---|---|---|
| id | BIGINT PK | |
| page_id | FK → pages | 差し込み先ページ |
| content_type | ENUM(case_study, medical_ad_gl, effect_claim, other) | |
| title | VARCHAR(500) | |
| content_html | LONGTEXT | 人間が入力したHTML |
| ai_enhanced_html | LONGTEXT | AIブラッシュアップ版（任意） |
| use_ai_version | BOOLEAN | AI版を使うかどうか |
| compliance_notes | TEXT | |
| status | ENUM(draft, published) | |
| timestamps | | |

### users（ユーザー）

| カラム | 型 | 説明 |
|---|---|---|
| id | BIGINT PK | |
| name | VARCHAR(255) | |
| email | VARCHAR(255) | |
| role | ENUM(admin, editor, client) | |
| password | VARCHAR(255) | |
| timestamps | | |

※ reviewer ロールは廃止。admin = 全操作、editor = 編集+公開、client = ブログ投稿のみ。
