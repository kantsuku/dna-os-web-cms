# ACMS 設計 v2 — MVPスコープ

## MVP定義

**「1サイトをACMSから記事生成→デザイン適用→XServerに公開できる」** 状態。

## Phase 1: 基盤（1〜2週目）

- [ ] データモデル刷新（v1 → v2 マイグレーション）
- [ ] sites / pages / page_generations CRUD
- [ ] users + 認証（admin / editor の2ロール）
- [ ] 基本レイアウト（Tailwind CDN + Alpine.js）

## Phase 2: 記事生成連携（3〜4週目）

- [ ] GAS WebApp 呼び出しサービス（ACMS → clinic-page-generator）
- [ ] 記事生成パネルUI（D1）
- [ ] page_generations への保存（世代管理）
- [ ] 世代比較画面（C3）— diff表示

## Phase 3: デザインシステム基盤（5〜6週目）

- [ ] ACMSコンポーネント定義（WPマッピング15種）
- [ ] design_tokens テーブル + デフォルト値投入
- [ ] site_designs テーブル + サイトごとトークン上書き
- [ ] ビルドエンジン（コンテンツHTML + デザインCSS → 完成HTML）
- [ ] デザイントークン管理UI（F1）— カラーピッカー + プレビュー

## Phase 4: 公開 & デプロイ（7〜8週目）

- [ ] 公開確認画面（E1）
- [ ] FTPデプロイ
- [ ] deploy_records + デプロイ履歴
- [ ] ロールバック
- [ ] 1サイト実証（テスト医院でエンドツーエンド）

## MVPに含めないもの（Phase 5以降）

| 機能 | 理由 |
|---|---|
| 微細編集WYSIWYG（C4） | MVPはHTMLソース直接編集で代用 |
| コンポーネントスタイル調整GUI（F3） | MVPはJSON直接編集で代用 |
| TOPレイアウトエディタ（F5） | MVPはテンプレートファイルで固定 |
| 例外コンテンツ AIブラッシュアップ | MVPは手動入力のみ |
| クライアント投稿画面 | v2以降 |
| WP既存サイト管理 | 優先度低 |

## MVP成功基準

1. ACMSのUIから「記事生成」ボタンでGASを呼び出し、HTMLが返ってくる
2. 返ってきたHTMLがpage_generationsに保存される
3. デザイントークンを変更するとプレビューに反映される
4. 「公開」ボタンでXServerにFTPデプロイされ、公開サイトが表示される
5. 「ロールバック」で前の状態に戻せる

## 技術スタック（v2）

| レイヤー | 技術 |
|---|---|
| CMS バックエンド | Laravel 12 (PHP 8.2+) |
| CMS フロント | Livewire + Alpine.js + Tailwind CDN |
| DB | MySQL (MariaDB 10.4 / XAMPP) |
| 公開サイト | 静的HTML + CSS（ACMSがビルド） |
| デプロイ | FTP (league/flysystem-ftp) |
| 記事生成連携 | HTTP (GAS WebApp URL) |
| デザインシステム | CSS Custom Properties |
