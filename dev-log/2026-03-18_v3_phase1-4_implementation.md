## 概要
DNA-OS統合作戦基盤 v3 の Phase 1〜4 を一括実装。設計ドキュメントに基づき、MVP全体をコードに落とした。

## 背景・課題
v3設計ドキュメント13本が完成し、承認を得たため、8週計画のPhase 1〜4を実装した。

## 決定内容

### Phase 1: データ基盤 + サイト/ページ管理
- v3マイグレーション: 7テーブル新規 + 3テーブル拡張
- 新規モデル7本、既存モデル3本拡張
- コントローラを Strategy/Web/Shared にディレクトリ分離
- 戦略タスク一覧/詳細/承認、承認待ち一覧を実装

### Phase 2: コンテンツ取り込み + セクション管理
- SectionParseService: com-section単位のHTML自動分割
- ContentImportService拡張: セクション分割統合、ロックマージ
- セクション一覧・編集画面、マークアップTXT直接取り込み
- ビルドエンジンのsections結合対応

### Phase 3: 戦略タスク自動生成 + AI解釈
- DnaOsSyncService: GAS API経由のポーリング
- ImpactAnalysisService: シート→ページのルールベースマッピング
- TaskGenerationService: 戦略タスク生成 + チャネルタスク分解
- AiInterpretationService: Claude API呼び出しでフリー入力解釈

### Phase 4: 例外コンテンツ + コンプライアンスチェック
- ComplianceCheckService: 医療広告GL必須項目チェック、禁止表現検出
- ExceptionContentController: 症例CRUD + 二段階承認フロー
- 例外コンテンツの一覧/作成/詳細/承認画面

## 採用しなかった選択肢と理由
- セクション分割にDOM Parserを使う案 → PHP標準のDOMDocumentはHTMLの揺れに弱いため、正規表現ベースのシンプルなパースを採用
- AI解釈を同期実行する案 → タイムアウトリスクがあるが、MVP段階では非同期キューより実装がシンプルなため同期を採用

## 注意事項・今後の課題
- DNA-OS (GAS) 側に `getRecentReflections` APIがまだ存在しない → Phase 3のDNA-OS同期は手動同期のみ動作
- AI解釈は ANTHROPIC_API_KEY が .env に設定されていない場合フォールバックで動作
- Phase 4のテスト/実証はまだ未実施 → 次ステップで亀有矯正歯科のデータで検証予定
