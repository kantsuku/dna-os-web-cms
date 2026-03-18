# DNA-OS Web CMS

DNA-OS（GAS/Spreadsheet）のコンテンツを歯科医院の公開サイトとして統制・配信する薄い独自CMS。

## アーキテクチャ

```
DNA-OS (GAS)  →  Web CMS (Laravel)  →  各医院XServer (PHP/静的HTML)
  原本管理         公開統制              表示のみ
```

## 技術スタック

- **CMS**: Laravel 12 + Livewire + Alpine.js
- **DB**: MySQL 8.0
- **公開サイト**: PHP + 静的HTML（Bladeテンプレートからビルド）
- **デプロイ**: FTP (league/flysystem-ftp)

## セットアップ

```bash
# 依存インストール
composer install

# 環境設定
cp .env.example .env
php artisan key:generate

# .env に DB接続情報を設定してから:
php artisan migrate
php artisan db:seed

# 開発サーバー起動
php artisan serve
```

## デモアカウント

| ロール | Email | Password |
|---|---|---|
| admin | admin@dna-os.local | password |
| editor | editor@dna-os.local | password |

## 設計ドキュメント

[docs/design/00_index.md](docs/design/00_index.md)

## DNA-OS 連携

`.env` に DNA-OS GAS WebApp URL を設定:

```
DNA_OS_WEBAPP_URL=https://script.google.com/macros/s/YOUR_WEBAPP_ID/exec
```
