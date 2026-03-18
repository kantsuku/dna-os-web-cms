## 概要
DNA-OS統合作戦基盤 v3 → v3.1 → v3.2 の設計・実装を1セッションで一気に実施。

## 背景・課題
v3設計ドキュメント13本を作成後、Phase 1〜4の実装、さらにUI構造変更（医院中心ダッシュボード、サイト種別、C&Cブランディング）を経て、コンポーネント管理・共通パーツ・メディアライブラリまで実装。

## 実装済み（このセッション）

### 設計
- v3設計ドキュメント13本（docs/design-v3/）
- v3.1 UI構造変更ドキュメント（14_v3.1_ui_restructure.md）

### 基盤 (Phase 1-4)
- v3マイグレーション（strategic_tasks, channel_tasks, approval_records等）
- 新規モデル10+本
- SectionParseService（com-section単位HTML分割）
- ContentImportService（セクション分割統合、ロックマージ）
- DnaOsSyncService / ImpactAnalysisService / TaskGenerationService
- AiInterpretationService（Claude API連携）
- ComplianceCheckService（医療広告GL準拠チェック）

### UI (v3.1-3.2)
- Clinic（医院）エンティティ導入、医院中心のダッシュボード
- C&Cブランディング（月面背景ヘッダー、ログイン画面）
- サイト種別（hp/specialty/recruitment/gbp/instagram/blog_media）
- チャネルとWebサイトの分離表示
- iframe src方式によるcom-CSS適用プレビュー
- セクション管理（追加/削除/上下移動/ロック）
- WYSIWYG（iframe contenteditable）
- コンポーネントHTML+CSS定義・ライブプレビュー
- 共通パーツ（ヘッダー/フッター）管理
- メディアライブラリ（フォルダ管理/アップロード/グリッド表示）
- ページ階層構造（parent_id）
- スラッグ自動生成（日本語→ローマ字マッピング）

## 未実装・次セッションで対応

### 優先度高
1. コンポーネントCSS適用の確実化（save→reload方式に切り替え）
2. プレビューエンジンにヘッダー/フッター統合
3. ナビ項目のページ構造からの自動生成
4. ハンバーガーメニュー（レスポンシブ対応）
5. メディアピッカー→セクション編集連携

### 優先度中
6. TOPセクションのコンポーネントタイプ管理
7. まるっとぽん（clinic-page-generator）統合
8. カラー設定変更のライブプレビュー

### 優先度低
9. AI幕僚長の高度化
10. 他チャネル（GBP/Instagram）の本格実装
11. A/Bテスト機能

## 注意事項
- iframeのCSS適用はpublicルート経由でないと動作しない（auth必須ルートだとセッション問題）
- マイグレーションの一部が手動SQL適用されている（2026_03_18_400000）
- Gemini生成画像（cc-moon.png）がリポジトリに含まれている
