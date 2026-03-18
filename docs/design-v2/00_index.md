# ACMS 設計 v2

> AI Content Management System — AIがコンテンツを管理し、人間はデザインとGo/NoGoを握る

## v1からの転換点

- CMS → **ACMS**（AIがコンテンツを管理するシステム）
- セクション単位の複雑な管理 → **ページ×世代のシンプルな管理**
- 承認フロー → **公開ボタンだけ + 微細編集（差分記録）**
- Bladeテンプレート差し込み → **デザインシステム × コンテンツHTML の分離結合**
- DNA-OSから同期 → **clinic-page-generatorで生成 → ACMSが受け取り**

## ドキュメント構成

| # | ファイル | 内容 |
|---|---|---|
| 01 | [01_overview.md](01_overview.md) | 全体概要・システム構成・原則 |
| 02 | [02_data_model.md](02_data_model.md) | データモデル（v1との差分含む） |
| 03 | [03_screens_and_flow.md](03_screens_and_flow.md) | 画面一覧・操作フロー |
| 04 | [04_design_system.md](04_design_system.md) | デザインシステム・コンポーネント体系 |
| 05 | [05_mvp_scope.md](05_mvp_scope.md) | MVPスコープ・フェーズ分け |

## 三層 + 生成レイヤー

```
DNA-OS              = 原本管理（医院の人格）
clinic-page-generator = コンテンツ生成（AIが書く）
ACMS                 = 公開統制（受け取り・デザイン結合・公開・世代管理）
XServer              = 表示（静的ファイル配信）
```
