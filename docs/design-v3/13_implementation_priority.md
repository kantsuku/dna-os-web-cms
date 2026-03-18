# 13. 実装優先順位

> MVPフェーズ内の具体的な実装順序と、各タスクの依存関係を定義する

---

## 1. 実装の大原則

1. **データモデルを先に確定する** — 他の全てがこれに依存する
2. **既存v2コードを最大限活用する** — 動いているものは壊さない
3. **エンドツーエンドの縦串を先に通す** — 横に広げるのは後
4. **UIは最小限で始める** — Tailwind + Livewireで素早く作る
5. **AI連携は最後に入れる** — まず人間が手動でできる状態を作る

---

## 2. Phase 1: データ基盤 + サイト/ページ管理（Week 1-2）

### 実装順序

```
1-1. v3マイグレーション作成
  │   ├── strategic_tasks
  │   ├── channel_tasks
  │   ├── improvement_reports
  │   ├── free_input_requests
  │   ├── approval_records
  │   ├── orchestration_logs
  │   ├── generated_content_sources
  │   ├── pages (カラム追加: content_classification, dna_source_key)
  │   └── page_generations (カラム変更: sections JSON追加)
  │
  ▼
1-2. Eloquentモデル定義/更新
  │   ├── StrategicTask (新規)
  │   ├── ChannelTask (新規)
  │   ├── ImprovementReport (新規)
  │   ├── FreeInputRequest (新規)
  │   ├── ApprovalRecord (新規)
  │   ├── OrchestrationLog (新規)
  │   ├── GeneratedContentSource (新規)
  │   ├── Page (更新)
  │   └── PageGeneration (更新)
  │
  ▼
1-3. サイト管理 (v2のSiteController拡張)
  │   ├── SITE-01: 一覧
  │   ├── SITE-02: 作成
  │   ├── SITE-03: 詳細
  │   └── SITE-04: 編集
  │
  ▼
1-4. ページ管理 (v2のPageController拡張)
  │   ├── PAGE-01: 一覧 (content_classification, dna_source_key対応)
  │   ├── PAGE-02: 作成 (テンプレート選択、分類自動設定)
  │   └── PAGE-03: 詳細 (世代一覧、セクション一覧の枠だけ)
  │
  ▼
1-5. シーダー更新
      └── DemoSeeder: v3テーブルのサンプルデータ
```

### 依存関係
- 1-1 → 1-2 → 1-3, 1-4（並列可）→ 1-5

### 完了基準
- `php artisan migrate` が成功
- サイト/ページのCRUDが動作
- デモデータで画面が表示される

---

## 3. Phase 2: コンテンツ取り込み + セクション管理（Week 3-4）

### 実装順序

```
2-1. SectionParseService (新規)
  │   ├── HTMLをcom-section単位に分割
  │   ├── 見出しテキストの抽出
  │   ├── section_id自動付与
  │   └── パース失敗時のフォールバック
  │
  ▼
2-2. ContentImportService 拡張
  │   ├── Google Docs URL → HTML取得 (v2流用)
  │   ├── マークアップTXTアップロード対応
  │   ├── SectionParseServiceと統合
  │   └── GeneratedContentSource記録
  │
  ▼
2-3. 取り込みUI
  │   ├── IMP-01: 取り込みフォーム (セクション分割プレビュー追加)
  │   └── IMP-02: 取り込み結果確認
  │
  ▼
2-4. セクション管理UI
  │   ├── PAGE-04: セクション一覧 (ロック状態表示)
  │   ├── PAGE-05: セクション編集 (CodeMirror + プレビュー)
  │   └── ロック/アンロック操作
  │
  ▼
2-5. 世代比較
  │   └── PAGE-06: セクション単位の差分表示
  │
  ▼
2-6. ビルドエンジン拡張
      └── SiteBuildService: sections結合 + human_patches適用
```

### 依存関係
- 2-1 → 2-2 → 2-3
- 2-1 → 2-4（2-2と並列可）
- 2-4 → 2-5
- 2-1 → 2-6

### 完了基準
- Google DocsからHTMLを取り込み、セクション分割される
- セクション単位で編集・ロックできる
- 世代間の差分がセクション単位で表示される
- ビルドエンジンでHTMLが生成される

---

## 4. Phase 3: 戦略タスク + 承認 + 公開（Week 5-6）

### 実装順序

```
3-1. DNA-OS連携サービス (新規)
  │   ├── DnaOsSyncService: ポーリング + 変更検出
  │   ├── GAS API呼び出し (getRecentReflections)
  │   └── 変更イベントの保存
  │
  ▼
3-2. 影響分析サービス (新規)
  │   ├── ImpactAnalysisService: ルールベースマッピング
  │   └── シート → ページ の対応テーブル
  │
  ▼
3-3. タスク生成サービス (新規)
  │   ├── TaskGenerationService
  │   │   ├── 戦略タスク生成
  │   │   └── チャネルタスク分解
  │   └── ルールベース変換ロジック
  │
  ▼
3-4. フリー入力AI解釈 (新規)
  │   ├── AiInterpretationService
  │   └── Claude API呼び出し (1回)
  │
  ▼
3-5. 戦略ダッシュボード画面
  │   ├── STR-04: タスク一覧
  │   ├── STR-05: タスク詳細
  │   ├── STR-06: フリー入力
  │   ├── STR-07: DNA-OS更新差分
  │   └── STR-08: 実行状況
  │
  ▼
3-6. 承認フロー
  │   ├── ApprovalService (新規)
  │   ├── APR-01: 承認待ち一覧
  │   └── APR-02: 承認詳細
  │
  ▼
3-7. 公開フロー (v2拡張)
  │   ├── PUB-01: 公開ダッシュボード
  │   ├── PUB-02: デプロイ確認
  │   ├── PUB-03: デプロイ履歴
  │   └── PUB-04: ロールバック
  │
  ▼
3-8. ダッシュボード拡張
      └── COM-02: タスク概要、承認待ちバッジ追加
```

### 依存関係
- 3-1 → 3-2 → 3-3 → 3-5
- 3-4 → 3-5（3-3と並列可）
- 3-5 → 3-6 → 3-7
- 3-7 → 3-8

### 完了基準
- DNA-OSの変更を検出してタスクが自動生成される
- フリー入力からタスクが生成される
- タスクの承認→公開フローが動作する
- ロールバックが動作する

---

## 5. Phase 4: 例外コンテンツ + 統合テスト（Week 7-8）

### 実装順序

```
4-1. 症例CRUD
  │   ├── EXC-01: 一覧
  │   ├── EXC-02: 作成/編集 (構造化フォーム)
  │   └── ExceptionContent モデル (v2拡張)
  │
  ▼
4-2. コンプライアンスチェック (新規)
  │   ├── ComplianceCheckService
  │   │   ├── 必須項目チェック (治療内容/期間/費用/リスク)
  │   │   ├── 禁止表現チェック
  │   │   └── チェック結果の構造化
  │   └── EXC-03: チェック結果画面
  │
  ▼
4-3. 二段階承認
  │   ├── EXC-04: 承認画面 (一次 + 最終)
  │   └── ApprovalServiceの拡張
  │
  ▼
4-4. 結合テスト
  │   ├── P1: DNA-OS更新→Web公開の全フロー
  │   ├── P3: フリー入力→タスク化→公開の全フロー
  │   ├── P4: 新規ページ生成→取り込み→公開
  │   ├── P6: ブログ→承認→公開
  │   ├── P7: 症例→二段階承認→公開
  │   ├── セクションロック + 再生成スキップの確認
  │   └── ロールバックの確認
  │
  ▼
4-5. 1サイト実証
  │   ├── 亀有矯正歯科のデータで実証
  │   ├── 既存WPサイトからのコンテンツ移行テスト
  │   └── FTPデプロイの実環境テスト
  │
  ▼
4-6. バグ修正 + UX改善
      ├── 実証で発見された問題の修正
      └── 画面表示の微調整
```

### 依存関係
- 4-1 → 4-2 → 4-3
- Phase 1-3全完了 → 4-4
- 4-4 → 4-5 → 4-6

---

## 6. ファイル構成（実装後の想定）

```
app/
  Http/
    Controllers/
      Strategy/
        StrategicTaskController.php
        ImprovementReportController.php
        FreeInputController.php
        DnaOsUpdateController.php
      Web/
        SiteController.php          (v2拡張)
        PageController.php          (v2拡張)
        SectionController.php       (新規)
        ImportController.php        (v2拡張)
        PublishController.php       (v2拡張)
        ExceptionContentController.php (v2拡張)
      Shared/
        ApprovalController.php      (新規)
        DashboardController.php     (v2拡張)
        AuthController.php          (v2流用)
        DesignController.php        (v2流用)
        UserController.php          (新規)
  Models/
    StrategicTask.php               (新規)
    ChannelTask.php                 (新規)
    ImprovementReport.php           (新規)
    FreeInputRequest.php            (新規)
    ApprovalRecord.php              (新規)
    OrchestrationLog.php            (新規)
    GeneratedContentSource.php      (新規)
    Site.php                        (v2拡張)
    Page.php                        (v2拡張)
    PageGeneration.php              (v2拡張)
    ExceptionContent.php            (v2拡張)
    SiteDesign.php                  (v2流用)
    DesignToken.php                 (v2流用)
    Component.php                   (v2流用)
    DeployRecord.php                (v2流用)
    User.php                        (v2流用)
  Services/
    Strategy/
      DnaOsSyncService.php          (新規)
      ImpactAnalysisService.php     (新規)
      TaskGenerationService.php     (新規)
      AiInterpretationService.php   (新規)
      AiChiefService.php            (新規: 将来のAI幕僚長統合サービス)
    Web/
      ContentImportService.php      (v2拡張)
      SectionParseService.php       (新規)
      SiteBuildService.php          (v2拡張)
      FtpDeployService.php          (v2流用)
      DesignCssService.php          (v2流用)
      ComplianceCheckService.php    (新規)
    Shared/
      ApprovalService.php           (新規)
```

---

## 7. 実装タスクのサマリー

| Phase | 新規ファイル数 | 既存変更数 | 推定工数 |
|-------|-------------|-----------|---------|
| Phase 1 | 8 (モデル) + 1 (マイグレ) | 4 (コントローラ) + 2 (ビュー) | 2週 |
| Phase 2 | 2 (サービス) + 4 (ビュー) | 2 (サービス) + 2 (コントローラ) | 2週 |
| Phase 3 | 5 (サービス) + 6 (コントローラ) + 8 (ビュー) | 3 (ビュー) | 2週 |
| Phase 4 | 1 (サービス) + 4 (ビュー) | 2 (サービス) | 2週 |
| **合計** | **約35ファイル新規** | **約15ファイル変更** | **8週** |

---

## 8. 各フェーズのリスクと回避策

| Phase | 主なリスク | 回避策 |
|-------|-----------|--------|
| 1 | スキーマ変更でv2コードが壊れる | マイグレーションは追加のみ、既存カラム削除しない |
| 2 | セクションパースが不安定 | フォールバック実装、generatorと出力仕様を先に合意 |
| 3 | AI API呼び出しの安定性 | フリー入力のAI解釈は非同期実行、タイムアウト設定 |
| 4 | 実環境でのFTPデプロイ問題 | 事前にステージング環境で検証 |
