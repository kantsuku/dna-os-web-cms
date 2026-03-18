# DNA-OS Web CMS セットアップガイド

XAMPPを使ったローカル開発環境の構築手順。

---

## 前提条件

- XAMPP がインストール済み（`C:\xampp`）
- PHP 8.2+（XAMPPに同梱）
- MySQL/MariaDB（XAMPPに同梱）
- Git

---

## 手順

### 1. XAMPP の Apache と MySQL を起動

XAMPP Control Panel を開いて、**Apache** と **MySQL** の「Start」ボタンを押す。

両方とも緑色になればOK。

---

### 2. データベースを作成

ブラウザで http://localhost/phpmyadmin を開く。

上部の「**データベース**」タブをクリックして：

- データベース名: `dna_os_cms`
- 照合順序: `utf8mb4_general_ci`

を入力して「作成」ボタンを押す。

（もしくはコマンドでやる場合）
```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE dna_os_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
```

---

### 3. .env ファイルを設定

プロジェクトルート（`C:\tools\dna-os-web-cms`）の `.env` を開いて、以下のDB部分を書き換える：

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dna_os_cms
DB_USERNAME=root
DB_PASSWORD=
```

XAMPPのデフォルトは root / パスワードなし。

---

### 4. マイグレーション実行（テーブル作成）

コマンドプロンプト or ターミナルで：

```bash
cd C:\tools\dna-os-web-cms
C:\xampp\php\php.exe artisan migrate
```

「11個のテーブルが作成された」旨のメッセージが出ればOK。

---

### 5. デモデータを投入

```bash
C:\xampp\php\php.exe artisan db:seed
```

これで以下が作られる：
- 管理者アカウント（admin@dna-os.local / password）
- 編集者アカウント（editor@dna-os.local / password）
- デモ歯科クリニック 1件
- TOPページ + インプラントページ + 医院紹介ページ（各セクション + コンテンツ付き）

---

### 6. 開発サーバーを起動

```bash
C:\xampp\php\php.exe artisan serve
```

ターミナルに以下が表示される：
```
INFO  Server running on [http://127.0.0.1:8000].
```

---

### 7. ブラウザでアクセス

http://127.0.0.1:8000 を開く。

ログイン画面が出るので：

| 項目 | 値 |
|---|---|
| メールアドレス | `admin@dna-os.local` |
| パスワード | `password` |

ログイン後、ダッシュボードが表示される。

---

## 画面の使い方

### ダッシュボード
- 管理サイト数、承認待ち件数、総ページ数が表示される
- サイト一覧からサイトの管理画面に移動できる

### サイト管理（サイト名をクリック）
- ページ一覧が表示される
- 「DNA-OS同期」ボタンでDNA-OSからデータを取り込む（WebApp URL設定後）
- 「公開管理」ボタンでデプロイ画面に移動

### ページ管理（ページ名をクリック）
- セクション一覧が表示される
- 各セクションの「編集」で微調整画面に移動
- 左右に「原本」と「編集エリア」が並ぶ

### プレビュー
- ページ一覧の「プレビュー」リンクで、公開サイトの見た目を確認できる

### 公開管理
- 承認済みページを選んで「デプロイ実行」（XServer接続情報設定後）
- 公開履歴からロールバック可能

---

## XServer接続（本番デプロイ時）

サイト管理 → 設定 で以下を入力：

| 項目 | 例 |
|---|---|
| FTPホスト | `sv12345.xserver.jp` |
| FTPユーザー | `example_ftp` |
| FTPパスワード | （XServerのFTPパスワード） |
| デプロイパス | `/home/example/example.com/public_html` |

「接続テスト」ボタンで接続確認できる。

---

## DNA-OS連携（GAS WebApp）

`.env` に追加：

```
DNA_OS_WEBAPP_URL=https://script.google.com/macros/s/xxxxxxxxx/exec
```

GAS側で `doGet` 関数が以下のパラメータを受け付ける必要がある：
- `action=get_clinic_data`
- `clinic_id=xxx`

→ 構造化済みデータをJSONで返す。

---

## トラブルシューティング

### `php artisan` が動かない
→ フルパスで実行: `C:\xampp\php\php.exe artisan ...`

### マイグレーションで「Access denied」
→ `.env` の `DB_USERNAME` と `DB_PASSWORD` を確認。XAMPPデフォルトは `root` / 空文字。

### 画面が真っ白
→ `storage/logs/laravel.log` を確認。
→ `C:\xampp\php\php.exe artisan config:clear` を実行。

### 「APP_KEY is missing」
→ `C:\xampp\php\php.exe artisan key:generate` を実行。
