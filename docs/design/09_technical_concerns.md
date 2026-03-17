# 9. 技術的懸念点と実装優先順位

## 技術的懸念点

### 高リスク

| # | 懸念 | 詳細 | 対策 |
|---|---|---|---|
| 1 | **XServer FTPデプロイの信頼性** | FTP接続の安定性、タイムアウト、部分アップロード失敗 | リトライ機構 + アトミックデプロイ（一時ディレクトリにアップ → rename） |
| 2 | **DNA-OS GAS WebApp のレート制限** | GASの実行時間制限（6分/実行）、同時接続数制限 | 差分同期（変更があったレコードのみ取得）。バッチ分割 |
| 3 | **90サイト規模でのビルド性能** | 全サイト一括同期時のメモリ・時間 | サイト単位のキュー処理。一括操作は非同期ジョブ |

### 中リスク

| # | 懸念 | 詳細 | 対策 |
|---|---|---|---|
| 4 | **XServer の PHP/MySQL バージョン差異** | 各医院のXServer契約時期によりPHP/MySQLバージョンが異なる可能性 | 公開サイト側はPHP 7.4互換で書く。CMS本体は専用サーバーなので最新OK |
| 5 | **FTP認証情報の管理** | 90サイト分のFTP認証情報をCMSに保存する必要がある | Laravel の encrypt/decrypt でDB保存。環境変数でマスターキー管理 |
| 6 | **同期の衝突検知** | 同じセクションを複数人が同時編集 | 楽観的ロック（updated_at チェック） + 編集開始時の通知 |

### 低リスク

| # | 懸念 | 詳細 | 対策 |
|---|---|---|---|
| 7 | **Bladeテンプレートのセキュリティ** | カスタムテンプレートでの XSS | テンプレートは開発者のみ作成。CMS投入コンテンツは `{!! !!}` を限定使用 |
| 8 | **画像管理** | 画像の保存先・リサイズ・CDN | MVPではCMSサーバーにローカル保存 → XServerにFTPアップ。v2でCDN検討 |

## DNA-OS連携の具体的な仕組み

### GAS側（既存を拡張）

```javascript
// DNA-OS側の WebApp エンドポイント
function doGet(e) {
  const action = e.parameter.action;

  switch(action) {
    case 'get_clinic_data':
      // clinic_id で指定した医院の全構造化データを返す
      return getClinicData(e.parameter.clinic_id);

    case 'get_updated_since':
      // 指定日時以降に更新されたレコードのみ返す（差分同期用）
      return getUpdatedSince(e.parameter.clinic_id, e.parameter.since);

    case 'get_content':
      // 特定のシート・レコード・フィールドの値を返す
      return getContent(e.parameter.sheet, e.parameter.record_id, e.parameter.field);
  }
}
```

### CMS側（同期サービス）

```php
class DnaOsSyncService
{
    // 1サイト分の同期を実行
    public function syncSite(Site $site): SyncLog
    {
        $log = SyncLog::create([...]);

        // 1. DNA-OSから最新データ取得
        $remoteData = $this->fetchFromDnaOs($site->clinic_id);

        // 2. サイトの全セクション（DNA-OSソース）をループ
        $sections = $site->pages()
            ->with('sections.overrideRule', 'sections.activeVariant')
            ->get()
            ->pluck('sections')
            ->flatten()
            ->where('content_source_type', 'dna_os');

        foreach ($sections as $section) {
            $this->syncSection($section, $remoteData, $log);
        }

        return $log;
    }
}
```

## 実装優先順位

```
優先度A（MVP必須・ブロッカー）
├── Laravel基盤セットアップ
├── sites / pages / sections CRUD
├── DNA-OS同期（手動・1サイト単位）
├── コンテンツバリアント管理
├── Bladeテンプレート基盤（TOP default + lower 3種）
├── ビルドエンジン（Blade → HTML）
├── FTPデプロイ
└── ロールバック

優先度B（MVP直後・運用改善）
├── 承認フロー（reviewer ロール）
├── 上書き制御の全4段階
├── 差分比較画面（3面比較）
├── クライアント投稿画面
├── 定期自動同期（cron）
└── ダッシュボード統計

優先度C（v2・スケール対応）
├── 例外コンテンツ管理
├── WP既存サイト管理
├── 複数テンプレートセット
├── SSHデプロイ
├── 画像CDN
└── 監査ログ
```
