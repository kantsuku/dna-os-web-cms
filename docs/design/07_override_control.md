# 7. 上書き制御設計

## 問題の本質

```
DNA-OSで原本が更新される
    ↓
CMSが同期を実行する
    ↓
しかし、editor が既に微調整済みのセクションがある
    ↓
上書きしたら人間の修正が消える
上書きしなかったら原本の改善が反映されない
    ↓
→ セクション単位で「どう扱うか」を制御する仕組みが必要
```

## 4段階のポリシー

| ポリシー | 同期時の挙動 | 想定ユースケース |
|---|---|---|
| `auto_sync` | 新バリアントを自動作成。旧バリアントは `superseded` に。承認フローに自動投入 | まだ微調整していないセクション。DNA-OSの更新をそのまま反映したい |
| `confirm_before_sync` | 差分比較画面(D4)に候補を表示。editorが採用/棄却/マージを判断 | 微調整済みだが、原本の改善も取り込みたいセクション |
| `manual_only` | 通知のみ（「原本が更新されました」）。同期は実行しない | 人間が慎重に判断すべきセクション |
| `locked` | 何もしない。通知もしない | 例外コンテンツ等、絶対に自動更新してはいけないセクション |

## デフォルトポリシーの決定ロジック

```php
// 新規セクション作成時のデフォルト
function getDefaultPolicy(Section $section): string
{
    // 例外コンテンツに紐づいている → locked
    if ($section->content_source_type === 'exception') {
        return 'locked';
    }

    // クライアント投稿 → locked（クライアントの文章を勝手に変えない）
    if ($section->content_source_type === 'client_post') {
        return 'locked';
    }

    // 手動入力セクション → manual_only
    if ($section->content_source_type === 'manual') {
        return 'manual_only';
    }

    // DNA-OSソース → auto_sync（未修正の間）
    return 'auto_sync';
}

// 人間修正が入った時点でポリシーを自動昇格
function onHumanEdit(Section $section): void
{
    $rule = $section->overrideRule;

    if ($rule->policy === 'auto_sync') {
        // 修正が入ったら confirm_before_sync に昇格
        $rule->update([
            'policy' => 'confirm_before_sync',
            'reason' => '人間修正が入ったため自動昇格',
        ]);
    }
}
```

## 同期実行フロー

```
DNA-OS同期開始
    │
    ▼
各セクションをループ
    │
    ├─ content_source_ref が null → スキップ
    │
    ├─ DNA-OS APIから最新コンテンツ取得
    │
    ├─ 現在のアクティブバリアントの original_content と比較
    │    │
    │    └─ 変更なし → スキップ（ログに記録）
    │
    ├─ 変更あり → override_policy を確認
    │    │
    │    ├─ auto_sync
    │    │    ├─ 新バリアント作成（source_type: dna_os_sync）
    │    │    ├─ 旧バリアントを superseded に
    │    │    └─ 新バリアントを draft で承認フローへ
    │    │
    │    ├─ confirm_before_sync
    │    │    ├─ 同期候補バリアント作成（status: draft）
    │    │    ├─ 既存バリアントはそのまま維持
    │    │    ├─ 差分比較画面(D4)に通知
    │    │    └─ editor に判断を委ねる
    │    │
    │    ├─ manual_only
    │    │    └─ 通知のみ。バリアントは作らない
    │    │
    │    └─ locked
    │         └─ 何もしない
    │
    ▼
sync_logs に記録
```

## 差分比較画面(D4)の3面比較

`confirm_before_sync` の場合、editorは以下の3つを比較できる:

```
┌──────────────────┬──────────────────┬──────────────────┐
│   DNA-OS 原本    │   現在の公開版    │   新しい同期候補  │
│  (original)      │  (human_edited)  │  (dna_os_sync)   │
│                  │                  │                  │
│  最初に取り込んだ │  editor が       │  DNA-OS で       │
│  時点の原本      │  微調整した版    │  更新された最新版 │
└──────────────────┴──────────────────┴──────────────────┘

editorの選択肢:
├─ 「新しい同期候補を採用」 → 新バリアントが有効に
├─ 「現在の公開版を維持」   → 候補を棄却
├─ 「手動マージ」           → 候補を基に微調整画面(D3)で編集
└─ 「ポリシーを変更」       → manual_only or locked に変更
```

## 上書き制御のUI

### セクション管理画面(D1)での表示

```
┌─────────────────────────────────────────────┐
│ セクション: hero                              │
│ ソース: DNA-OS (03_DNA_Master > clinic_abc)  │
│                                              │
│ 上書き制御: [confirm_before_sync ▼]          │
│ 理由: 院長メッセージを微調整済み               │
│ 設定者: 田中（2026-03-15）                    │
│                                              │
│ [ポリシー変更] [同期履歴を見る]               │
└─────────────────────────────────────────────┘
```

## 競合解決の原則

1. **人間の判断を最優先** — 自動で人間の修正を消すことは絶対にしない
2. **情報は捨てない** — superseded バリアントも保持。いつでも差分確認・復元可能
3. **ポリシーは昇格のみ自動** — auto_sync → confirm は自動。逆方向は人間が明示的に設定
4. **locked は聖域** — 例外コンテンツ・クライアント投稿は自動操作の対象外
