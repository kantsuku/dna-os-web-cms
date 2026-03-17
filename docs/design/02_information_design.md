# 2. 情報設計

## データの所在マップ

```
┌─────────────────────────────────────────────────────────────┐
│ DNA-OS（原本）                                               │
│                                                             │
│  医院人格 / 診療方針 / スタッフ / 採用方針 / クレド /          │
│  トーン&マナー / 戦略 / コンテンツ生成ガイド                    │
│                                                             │
│  → 構造化済みマスターデータ                                    │
│  → AI生成済みマークアップテキスト                               │
└──────────────────────┬──────────────────────────────────────┘
                       │ 同期（Pull）
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ Web CMS（公開統制レイヤー）                                    │
│                                                             │
│  【CMS が保持するデータ】                                     │
│  ├─ サイト定義 ─── どの医院のどのドメイン                      │
│  ├─ ページ定義 ─── URL構造、テンプレート指定、公開状態          │
│  ├─ セクション ─── ページ内の構成単位、並び順                   │
│  ├─ コンテンツソース参照 ─── DNA-OS原本へのポインタ             │
│  ├─ コンテンツバリアント ─── 微調整差分、人間修正版             │
│  ├─ 承認記録 ─── 誰が・いつ・何を承認/却下したか               │
│  ├─ 公開記録 ─── デプロイ履歴、ロールバック用スナップショット    │
│  ├─ 上書き制御ルール ─── セクション単位の保護設定               │
│  ├─ 例外コンテンツ ─── 症例・医療広告GL配慮コンテンツ           │
│  └─ ユーザー・権限 ─── CMS操作者のロール管理                   │
│                                                             │
│  【CMS が保持しないデータ】                                    │
│  ├─ 医院人格の原本                                           │
│  ├─ 構造化前の生データ                                        │
│  ├─ AI構造化ロジック                                          │
│  └─ 戦略判断のコンテキスト                                     │
└─────────────────────────────────────────────────────────────┘
```

## エンティティ一覧と境界定義

### Site（サイト）
医院1つ = サイト1つ。マルチサイト管理の最上位単位。

| 属性 | 説明 |
|---|---|
| site_id | 一意識別子 |
| clinic_id | DNA-OS側の clinic_id と対応 |
| name | 医院名 |
| domain | 公開ドメイン |
| xserver_host | FTP/SSH接続先 |
| xserver_credentials | 接続情報（暗号化保存） |
| template_set | 使用するテンプレートセット名 |
| status | active / maintenance / archived |

### Page（ページ）
サイト内の1ページ。URL1つに対応。

| 属性 | 説明 |
|---|---|
| page_id | 一意識別子 |
| site_id | 所属サイト |
| slug | URLパス（例: `/implant`） |
| title | ページタイトル |
| page_type | top / lower / blog / news / exception |
| template_name | 使用テンプレート（TOPは `custom:{site_id}` も可） |
| meta_description | SEO用 |
| status | draft / pending_review / approved / published / archived |
| publish_version | 現在公開中のバージョン番号 |
| sort_order | ナビゲーション表示順 |

### Section（セクション）
ページ内の構成ブロック。コンテンツ差し込みの最小単位。

| 属性 | 説明 |
|---|---|
| section_id | 一意識別子 |
| page_id | 所属ページ |
| section_key | テンプレート内のスロット名（例: `hero`, `treatment_detail`, `faq`） |
| sort_order | セクション表示順 |
| content_source_type | dna_os / manual / exception / client_post |
| content_source_ref | DNA-OS側の参照キー（sheet + record_id + field） |
| override_policy | auto_sync / manual_only / locked |
| is_human_edited | 人間修正済みフラグ |

### ContentVariant（コンテンツバリアント）
セクションの具体的なコンテンツ。バージョン管理される。

| 属性 | 説明 |
|---|---|
| variant_id | 一意識別子 |
| section_id | 所属セクション |
| version | バージョン番号（1, 2, 3...） |
| source_type | dna_os_sync / human_edit / ai_regenerated |
| content_html | 表示用HTML |
| content_raw | 原文テキスト（差分比較用） |
| original_content | DNA-OS から取得した原本（差分表示の基準） |
| diff_from_original | 原本からの差分（JSON patch形式） |
| edited_by | 編集者 |
| edit_reason | 修正理由 |
| status | draft / pending_review / approved / published / superseded |
| created_at | 作成日時 |

### ApprovalRecord（承認記録）
コンテンツバリアントの承認/却下/差し戻し記録。

| 属性 | 説明 |
|---|---|
| approval_id | 一意識別子 |
| variant_id | 対象バリアント |
| action | approve / reject / send_back |
| reviewer_id | レビュアー |
| notes | コメント |
| created_at | 承認日時 |

### PublishRecord（公開記録）
デプロイの実行記録。ロールバックの単位。

| 属性 | 説明 |
|---|---|
| publish_id | 一意識別子 |
| site_id | 対象サイト |
| pages | 公開対象ページID配列（JSON） |
| snapshot_path | ビルド成果物のスナップショットパス |
| deploy_status | pending / deploying / success / failed / rolled_back |
| deployed_by | 実行者 |
| deployed_at | デプロイ日時 |
| rollback_of | ロールバック元のpublish_id（あれば） |

### OverrideRule（上書き制御ルール）
セクション単位の上書き制御ポリシー。

| 属性 | 説明 |
|---|---|
| rule_id | 一意識別子 |
| section_id | 対象セクション |
| policy | auto_sync / confirm_before_sync / manual_only / locked |
| reason | ルール設定理由 |
| set_by | 設定者 |
| set_at | 設定日時 |

### ExceptionContent（例外コンテンツ）
症例・医療広告GL配慮が必要な高リスクコンテンツ。

| 属性 | 説明 |
|---|---|
| exception_id | 一意識別子 |
| site_id | 対象サイト |
| content_type | case_study / medical_ad_gl / effect_claim / other |
| title | タイトル |
| content_html | コンテンツ本文 |
| risk_level | high / critical |
| compliance_notes | GL対応メモ |
| requires_specialist_review | 専門家レビュー要否 |
| status | draft / under_review / approved / published / suspended |
| linked_section_id | 差し込み先セクション（あれば） |

### User（ユーザー）
CMS操作者。

| 属性 | 説明 |
|---|---|
| user_id | 一意識別子 |
| name | 氏名 |
| email | メールアドレス |
| role | admin / editor / reviewer / client |
| accessible_sites | アクセス可能サイトID配列（clientロールはここで制限） |
| password | ハッシュ化パスワード |

### ロール定義

| ロール | できること |
|---|---|
| **admin** | 全操作。サイト作成・ユーザー管理・デプロイ・上書き制御設定 |
| **editor** | コンテンツ編集・微調整・公開申請。承認はできない |
| **reviewer** | 承認/却下/差し戻し。編集はしない |
| **client** | ブログ・お知らせの投稿のみ。自サイトのみアクセス可 |
