## 概要
ACMS v3.2の主要UI機能4件を一括実装。コンポーネントCSS確実適用、ヘッダー/フッター統合、ナビ自動生成+ハンバーガーメニュー、メディアピッカー連携。

## 背景・課題
前セッション（同日）でv3設計〜v3.2基盤実装まで完了したが、以下の課題が残っていた：
- ClinicComponent/Component の default_styles/custom_css がプレビューCSSに反映されていなかった
- SiteBuildService::wrapInLayout にヘッダー/フッターが未統合（`<main>`のみ出力）
- ナビゲーションは共通パーツ設定で手動入力のみ。ページからの自動生成なし
- メディアライブラリUIは存在するが、セクション編集から画像を挿入する導線がなかった

## 決定内容

### 1. コンポーネントCSS適用の確実化
- DesignCssServiceに `generateGlobalComponentCss()` / `generateClinicComponentCss()` / `stylesToCss()` 追加
- CSS生成パイプラインを8層構造に拡張（ベース → トークン → 医院トンマナ → グローバルコンポーネント → 医院コンポーネント → サイト固有 → コンポーネント上書き → カスタム）

### 2. ヘッダー/フッター統合
- SiteBuildServiceに `renderHeader()` / `renderFooter()` メソッド追加
- wrapInLayout() にヘッダー/フッターHTML統合
- PageController::contentFrame に `?layout=1` パラメータ追加（ヘッダー/フッター付き完全プレビュー）
- SitePartsController::previewHeader をcom-CSSクラス方式に書き換え、previewFooter 追加
- 全体プレビューボタンが layout=1 付きリンクに変更

### 3. ナビ自動生成 + ハンバーガーメニュー
- Site モデルに getNavItems()（手動優先、なければ generateNavFromPages() でページから自動生成）
- theme-base.css に com-header / com-footer / com-hamburger / com-mobile-nav（ドロワー）のCSS追加
- 834px以下でハンバーガー表示、ドロワーでナビ展開
- ハンバーガーJSは SiteBuildService と PageController に組み込み

### 4. メディアピッカー → セクション編集連携
- media/picker.blade.php 新規作成（画像グリッド選択UI、フォルダ移動、アップロード対応）
- postMessage 方式で親ウィンドウに選択画像を通知
- edit-section.blade.php に「画像を挿入」ボタンとモーダル追加
- ビジュアル/HTML両モードでカーソル位置に画像挿入

## 採用しなかった選択肢と理由
- **SiteLayoutService を別サービスとして分離**: SiteBuildService がすでにレイアウト責務を持っていたため、メソッド追加で対応。過度な分離は避けた
- **ナビをDB管理**: 現状はheader_config JSON + ページからの自動生成で十分。独立テーブルは将来必要になったとき

## 注意事項・今後の課題
- 次の優先: TOPセクションのコンポーネントタイプ管理、まるっとぽん統合
- 実データ取り込みテスト（清澄白河駅前矯正歯科の成人矯正マークアップ等）で動作検証が必要
- ハンバーガーJSが PageController と SiteBuildService で重複定義。共通化の余地あり
- メディアピッカーのpostMessage方式はsame-origin前提。CDN配信時は要対応
