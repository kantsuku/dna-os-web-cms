# DNA-OS 統合作戦基盤 設計ドキュメント v3

> Webチャネル導線強化版 — 2026-03-18

---

## 概要

DNA-OS統合作戦基盤の全体再設計。DNA-OSを原本基盤として守りつつ、
**戦略統制→チャネル実行→公開** の全導線を一気通貫で設計する。

v1（CMS）→ v2（ACMS）→ **v3（統合作戦基盤）** の3段階目。

### v3の主要変更点
- 5層アーキテクチャ + AI幕僚長を横断層として定義
- 戦略タスク→チャネルタスクの2段タスク構造を導入
- Webチャネルへの接続導線を7パターンで完全定義
- セクション単位のコンテンツ管理・上書き制御を導入
- コンテンツ3分類（標準管理/アシスト執筆/例外管理）による制御分離

---

## ドキュメント一覧

| # | ドキュメント | 対応する依頼 |
|---|------------|------------|
| [01](01_architecture.md) | **全体アーキテクチャ再設計案** | 依頼1 |
| [02](02_layer_roles.md) | **各レイヤーの役割定義** | 依頼1 |
| [03](03_task_design.md) | **戦略タスク / チャネル実行タスク設計** | 依頼2 |
| [04](04_web_channel_pipelines.md) | **Webチャネル接続導線設計（全7パターン）** | 依頼3 |
| [05](05_web_cms_design.md) | **Web公開基盤（薄い独自CMS）設計** | 依頼4 |
| [06](06_top_lower_strategy.md) | **TOP自由 / 下層構造化の設計** | 依頼5 |
| [07](07_content_classification.md) | **コンテンツ分類と上書き制御設計** | 依頼6 |
| [08](08_data_model.md) | **データモデル** | 依頼7 |
| [09](09_screens.md) | **画面一覧** | 依頼8 |
| [10](10_screen_flow.md) | **画面遷移** | 依頼8 |
| [11](11_mvp_scope.md) | **MVPスコープ** | 依頼9 |
| [12](12_technical_concerns.md) | **技術的懸念点** | 依頼10 |
| [13](13_implementation_priority.md) | **実装優先順位** | — |

---

## 読む順番

### 全体像を掴みたい場合
01 → 02 → 03 の順に読む

### Webチャネルの導線を理解したい場合
04 → 05 → 06 → 07 の順に読む

### 実装に入りたい場合
08 → 13 → 11 の順に読む

### リスクを把握したい場合
12 を読む

---

## 前提ドキュメント

| ドキュメント | 場所 |
|------------|------|
| v1設計 | [docs/design/](../design/) |
| v2設計 | [docs/design-v2/](../design-v2/) |
| 依頼書 | [claude_code_full_redesign_request_dnaos_web.md](../../../dna-os/claude_code_full_redesign_request_dnaos_web.md) |

---

## 技術スタック

| レイヤー | 技術 |
|---------|------|
| DNA-OS | Google Apps Script + Spreadsheet |
| ACMS | Laravel 12 + Livewire 4 + Alpine.js + Tailwind CSS |
| DB | MySQL 8.0 |
| 公開サイト | 静的HTML (FTP → XServer) |
| AI | Gemini / Claude API |
