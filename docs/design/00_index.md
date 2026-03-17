# DNA-OS Web CMS 設計ドキュメント

> DNA-OS（GAS/Spreadsheet）のコンテンツを公開サイトとして統制・配信する薄い独自CMS

## 前提

- **現状**: XServer + WordPress で歯科サイト（90+）を構築・運用中
- **XServer継続**: クライアントごとに契約。セキュリティ・シェア率の観点で維持
- **順次移行**: 新規サイトから本CMSを使用。既存WPサイトは共存
- **技術スタック**: Laravel (PHP+MySQL) — XServer互換
- **クライアント**: ブログ・お知らせの投稿のみ。入口は分離

## ドキュメント構成

| # | ファイル | 内容 |
|---|---|---|
| 01 | [01_architecture.md](01_architecture.md) | 全体アーキテクチャ概要 |
| 02 | [02_information_design.md](02_information_design.md) | 情報設計（エンティティ境界・ロール定義） |
| 03 | [03_screens.md](03_screens.md) | 画面一覧 |
| 04 | [04_screen_flow.md](04_screen_flow.md) | 画面遷移・ステータス遷移 |
| 05 | [05_data_model.md](05_data_model.md) | データモデル（DDL） |
| 06 | [06_top_lower_strategy.md](06_top_lower_strategy.md) | TOP自由 / 下層構造化の実装方針 |
| 07 | [07_override_control.md](07_override_control.md) | 上書き制御設計 |
| 08 | [08_mvp_scope.md](08_mvp_scope.md) | MVPスコープ |
| 09 | [09_technical_concerns.md](09_technical_concerns.md) | 技術的懸念点・実装優先順位 |

## 三層責務

```
DNA-OS    = 原本管理 + AI構造化（真実の源泉）
Web CMS   = 公開統制（組み立て・微調整・承認・公開・バージョン管理）
公開サイト = 表示のみ（ロジックを持たない）
```
