# 03. 戦略タスク / チャネル実行タスク設計

> タスクの2段構造と、戦略→Webチャネルへの変換フローを詳細定義する

---

## 1. タスクの2段構造

```
戦略タスク (StrategicTask)
  │
  │  AI幕僚長が分解
  │  ＋ 人間が承認
  ▼
チャネルタスク (ChannelTask)
  │
  │  チャネルAI / ACMS が実行
  ▼
実行結果 (TaskResult)
```

### なぜ2段にするのか
- 戦略タスクは「何を達成したいか」（意図）
- チャネルタスクは「どう実行するか」（手段）
- 同じ戦略タスクが複数チャネルに分解される可能性がある
- チャネル側の実装事情で戦略意図が歪むのを防ぐ

---

## 2. 戦略タスク (StrategicTask)

### 定義
**AI幕僚長または人間が生成する、チャネル横断の改善意図**。

### 生成トリガー
| トリガー | 生成主体 | 例 |
|---------|---------|-----|
| DNA-OS更新 | AI幕僚長（自動） | 診療方針変更→該当ページ更新 |
| 改善レポート | AI幕僚長（自動） | CTR低下→メタ情報改善 |
| フリー入力 | AI幕僚長（AI解釈） | 「トップの写真を変えたい」→TOPページ修正 |
| 新規ページ依頼 | 人間（手動） | 新しい診療メニューのページ追加 |
| 定期チェック | AI幕僚長（スケジュール） | リンク切れ検出→修正タスク |

### データ構造

```
StrategicTask {
  id:                 ST-YYYYMMDD-NNN
  trigger_type:       dna_update | improvement | free_input | new_page | scheduled_check
  trigger_source_id:  (DNA-OS proposal_id / report_id / free_input_id)
  title:              "診療方針変更に伴う虫歯治療ページの更新"
  description:        "DNA-OS上で虫歯治療の方針が更新された。..."
  intent:             "該当ページの本文・メタ情報を最新方針に合わせる"
  priority:           critical | high | medium | low
  risk_level:         high | medium | low
  target_channels:    ["web"]  (将来: ["web", "gbp", "sns"])
  status:             draft → pending_approval → approved → in_progress
                      → completed | cancelled
  created_by:         ai_chief | human:{user_id}
  approved_by:        null | {user_id}
  approved_at:        null | timestamp
  created_at:         timestamp
  updated_at:         timestamp
}
```

### 承認ルール

| priority | risk_level | 承認要否 |
|----------|-----------|---------|
| any | high | **必須**（例外コンテンツ関連、広告GL関連） |
| critical | any | **必須** |
| high | medium | **必須** |
| medium | low | 自動承認可（設定で切替） |
| low | low | 自動承認 |

---

## 3. チャネルタスク (ChannelTask)

### 定義
**戦略タスクをチャネル固有の実行単位に分解したもの**。

### 生成フロー
1. 戦略タスクが承認される
2. AI幕僚長がチャネルごとにタスクを分解
3. チャネルタスクがL4に配信される

### データ構造

```
ChannelTask {
  id:                 CT-WEB-YYYYMMDD-NNN
  strategic_task_id:  ST-YYYYMMDD-NNN
  channel:            web | gbp | sns | line
  task_type:          update_content | update_meta | new_page | delete_page
                      | check_quality | fix_links | update_design
                      | blog_review | case_review | compliance_check
  title:              "虫歯治療ページ本文の更新"
  instruction:        "以下の変更をページに反映してください：..."
  target_site_id:     {site_id}
  target_page_id:     {page_id} (nullable)
  target_sections:    ["section_3", "section_5"] (nullable)
  input_data: {
    dna_changes:      [{field, old_value, new_value}]
    source_content:   "マークアップ済みHTML（差し替え元）"
    meta_suggestion:  {title, description, keywords}
  }
  status:             pending → in_progress → review_ready
                      → approved → deployed → completed
                      | rejected | cancelled
  execution_log:      [{timestamp, action, detail}]
  result: {
    diff_html:        "変更差分"
    affected_pages:   [page_ids]
    issues_found:     [{type, detail}]
  }
  assigned_to:        ai | human:{user_id}
  created_at:         timestamp
  updated_at:         timestamp
}
```

### チャネルタスクのステータスフロー

```
pending
  │
  ▼
in_progress  ──→  rejected (差し戻し)
  │                  │
  ▼                  ▼
review_ready ←──── (再実行)
  │
  ▼
approved
  │
  ▼
deployed
  │
  ▼
completed
```

---

## 4. 戦略→Webチャネルタスク変換の詳細設計

### 4-1. DNA-OS更新トリガー

```
DNA-OS Proposal反映
  │
  ▼
変更差分を検出
  │ destination_sheet + destination_field + proposed_value
  ▼
影響範囲を特定
  │ Treatment_Policy → 該当診療ページ
  │ Staff_Master → スタッフ紹介ページ
  │ DNA_Master → 理念ページ + 全ページのトーン確認
  │ Tone_And_Manner → 全ページのトーン再チェック
  ▼
戦略タスク生成
  │ "○○の更新に伴う△△ページの反映"
  ▼
チャネルタスク分解
  ├── CT: ページ本文更新 (update_content)
  ├── CT: メタ情報更新 (update_meta)
  └── CT: 関連ページの整合性チェック (check_quality)
```

**変換ルール（影響マッピング）**:

| DNA-OS シート | 影響するWebページ | タスクタイプ |
|-------------|----------------|------------|
| 04_Treatment_Policy | 該当診療ページ | update_content, update_meta |
| 03_DNA_Master | 理念ページ、全ページトーン | update_content, check_quality |
| 10_Staff_Master | スタッフ紹介ページ | update_content |
| 11_Recruitment_Policy | 採用ページ | update_content |
| 16_Credo_Master | 理念・クレドページ | update_content |
| 31_Tone_And_Manner | 全ページ | check_quality |
| 00_Clinic | 共通情報（フッター等） | update_content |
| 30_Content_Gen_Guide | 新規生成時のみ影響 | — |

### 4-2. 改善レポートトリガー

```
AI幕僚長がレポート生成
  │
  ▼
改善提案を戦略タスクに変換
  │ 例: "虫歯治療ページのCTRが低い"
  │     → "メタディスクリプションの改善"
  ▼
チャネルタスク分解
  ├── CT: メタ情報改善案作成 (update_meta)
  └── CT: H1/リード文の改善案作成 (update_content)
```

### 4-3. フリー入力トリガー

```
人間: "トップページの院長あいさつを変えたい"
  │
  ▼
AI幕僚長が解釈
  │ 対象: TOPページ
  │ セクション: 院長あいさつ
  │ 意図: テキスト変更
  ▼
戦略タスク生成
  │ "TOPページ院長あいさつセクションのテキスト変更"
  ▼
チャネルタスク
  └── CT: 対象セクション特定 → 修正案作成 (update_content)
```

### 4-4. 新規ページ生成トリガー

```
人間 or AI幕僚長: "静脈内鎮静法のページを新規追加"
  │
  ▼
戦略タスク生成
  │ "静脈内鎮静法の診療ページ新規作成"
  ▼
チャネルタスク分解
  ├── CT: DNA-OSデータ確認 → 記事生成パイプライン起動
  ├── CT: 新規ページ作成 (new_page)
  ├── CT: メタ情報設定 (update_meta)
  └── CT: 内部リンク更新 (check_quality)
```

---

## 5. タスク粒度のガイドライン

### 適切な粒度
- **1チャネルタスク = 1ページまたは1機能に対する1アクション**
- 「虫歯治療ページの本文を更新する」→ ○
- 「全ページを一括更新する」→ ✕（ページごとに分解）

### 粒度の例外
- `check_quality` タスクはサイト全体を対象にしてよい（結果として個別タスクが派生する）
- `compliance_check` は複数ページを横断してよい

### 粒度が粗すぎる場合のサイン
- タスク完了まで1時間以上かかる
- 1タスクで5ページ以上を変更する
- 承認者が変更内容を把握できない

### 粒度が細かすぎる場合のサイン
- 同じページに対して同時に5個以上のタスクが走る
- タスク間の依存関係が複雑になりすぎる
- 承認待ちが渋滞する

---

## 6. タスクのライフサイクル管理

### 同時実行制御
- 同一ページに対するタスクは **直列実行** （排他ロック）
- 異なるページに対するタスクは **並列実行可**
- ロック待ちが発生した場合はキューに入る

### タスクのタイムアウト
- `in_progress` が24時間を超えた場合 → 管理者に通知
- `review_ready` が72時間を超えた場合 → リマインダー通知

### タスクのキャンセル
- 元の戦略タスクがキャンセルされた場合 → 配下のチャネルタスクも全キャンセル
- チャネルタスク単体のキャンセルは承認者のみ可能
