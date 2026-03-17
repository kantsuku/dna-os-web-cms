# 8. MVPスコープ

## MVP の定義

**「1サイトを DNA-OS → CMS → XServer の流れで公開できる」** 状態をMVPとする。

## MVP に含めるもの

### フェーズ1: 基盤（1〜2週目）

- [ ] Laravel プロジェクトセットアップ（認証、DB、基本レイアウト）
- [ ] sites テーブル + CRUD
- [ ] pages テーブル + CRUD
- [ ] sections テーブル + CRUD
- [ ] users テーブル + 認証（admin / editor のみ）
- [ ] 基本レイアウト（Livewire + Alpine.js）

### フェーズ2: コンテンツ管理（3〜4週目）

- [ ] DNA-OS同期機能（GAS WebApp → CMS、手動実行のみ）
- [ ] content_variants テーブル + バージョン管理
- [ ] 微調整画面（D3）— リッチテキストエディタ
- [ ] 差分表示（原本 vs 編集版）
- [ ] 上書き制御（override_rules）— auto_sync / locked の2段階のみ

### フェーズ3: テンプレート & ビルド（5〜6週目）

- [ ] TOPテンプレート（default 1種）
- [ ] 下層テンプレート（treatment, about, generic の3種）
- [ ] セクションレンダラー（richtext, faq, price_table）
- [ ] ビルドエンジン（Blade → HTML生成）
- [ ] プレビュー画面（F2）

### フェーズ4: 公開 & デプロイ（7〜8週目）

- [ ] FTPデプロイ機能（phpseclib）
- [ ] publish_records + デプロイ履歴
- [ ] 公開確認画面（F1）
- [ ] ロールバック機能
- [ ] 1サイト実証（テスト医院でエンドツーエンド動作確認）

## MVP に含めないもの（v2以降）

| 機能 | 理由 |
|---|---|
| 承認フロー（reviewer ロール） | MVPではadmin承認で十分 |
| クライアント投稿画面 | MVPはadmin/editorのみ運用 |
| confirm_before_sync / manual_only | MVPはauto_sync / locked の2択で十分 |
| 3面比較画面（D4） | v2で上書き制御を拡充する際に |
| 例外コンテンツ管理 | MVPでは手動セクションで代用 |
| WP既存サイトの一覧管理 | MVPは新規サイトのみ |
| 定期自動同期（cron） | MVPは手動同期のみ |
| SSHデプロイ | MVPはFTPのみ |
| 複数テンプレートセット | MVPは default 1セットのみ |

## MVP成功基準

1. DNA-OS Spreadsheet のデータが CMS に同期される
2. CMS上でコンテンツの確認・微調整ができる
3. テンプレートに差し込んでプレビューできる
4. FTPでXServerにデプロイし、公開サイトが表示される
5. ロールバックで前の状態に戻せる
