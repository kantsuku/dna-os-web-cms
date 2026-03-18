# 08. データモデル

> 全エンティティの定義、主なカラム、状態遷移、関係性、レイヤー帰属

---

## 1. エンティティ一覧とレイヤー帰属

| エンティティ | レイヤー | 役割 |
|------------|--------|------|
| ClinicDNAProfile | L1 (DNA-OS参照) | 医院DNA情報のキャッシュ |
| StructuredData | L1 (DNA-OS参照) | 構造化データのキャッシュ |
| GeneratedContentSource | L1→L4 橋渡し | 生成コンテンツの取り込み元記録 |
| ImprovementReport | 横断 (AI幕僚長) | 改善レポート |
| StrategicTask | L3 (戦略統制) | 戦略タスク |
| ChannelTask | L3→L4 橋渡し | チャネル実行タスク |
| Site | L4 (Web実行) | サイト定義 |
| Page | L4 (Web実行) | ページ定義 |
| PageGeneration | L4 (Web実行) | ページ世代 |
| Section | L4 (Web実行) | セクション（JSON内だがモデルとして定義） |
| ExceptionContent | L4 (Web実行) | 例外コンテンツ |
| DesignToken | L4 (Web実行) | グローバルデザイントークン |
| Component | L4 (Web実行) | コンポーネント定義 |
| SiteDesign | L4 (Web実行) | サイト別デザイン設定 |
| ApprovalRecord | L3+L4 (横断) | 承認記録 |
| DeployRecord | L5 (公開) | デプロイ記録 |
| OrchestrationLog | 横断 (AI幕僚長) | オーケストレーションログ |
| User | 横断 | ユーザー |
| FreeInputRequest | L3 (戦略統制) | フリー入力修正依頼 |

---

## 2. 各エンティティの詳細定義

### 2-1. StrategicTask（戦略タスク）

```sql
CREATE TABLE strategic_tasks (
    id                  CHAR(20) PRIMARY KEY,   -- ST-YYYYMMDD-NNN
    clinic_id           VARCHAR(50) NOT NULL,
    trigger_type        ENUM('dna_update', 'improvement', 'free_input',
                             'new_page', 'scheduled_check') NOT NULL,
    trigger_source_id   VARCHAR(100),            -- proposal_id / report_id 等
    title               VARCHAR(255) NOT NULL,
    description         TEXT,
    intent              TEXT,
    priority            ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    risk_level          ENUM('high', 'medium', 'low') DEFAULT 'medium',
    target_channels     JSON NOT NULL,           -- ["web", "gbp"]
    status              ENUM('draft', 'pending_approval', 'approved',
                             'in_progress', 'completed', 'cancelled') DEFAULT 'draft',
    created_by          VARCHAR(100) NOT NULL,   -- 'ai_chief' or 'human:{user_id}'
    approved_by         BIGINT UNSIGNED NULL,
    approved_at         TIMESTAMP NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_clinic_status (clinic_id, status),
    INDEX idx_status_priority (status, priority),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
```

**状態遷移:**
```
draft → pending_approval → approved → in_progress → completed
                         ↘ cancelled
```

### 2-2. ChannelTask（チャネルタスク）

```sql
CREATE TABLE channel_tasks (
    id                  CHAR(24) PRIMARY KEY,   -- CT-WEB-YYYYMMDD-NNN
    strategic_task_id   CHAR(20) NOT NULL,
    channel             ENUM('web', 'gbp', 'sns', 'line') DEFAULT 'web',
    task_type           ENUM('update_content', 'update_meta', 'new_page',
                             'delete_page', 'check_quality', 'fix_links',
                             'update_design', 'blog_review', 'case_review',
                             'compliance_check') NOT NULL,
    title               VARCHAR(255) NOT NULL,
    instruction         TEXT,
    target_site_id      BIGINT UNSIGNED NULL,
    target_page_id      BIGINT UNSIGNED NULL,
    target_sections     JSON,                    -- ["sec_01", "sec_03"]
    input_data          JSON,                    -- 変更元データ
    status              ENUM('pending', 'in_progress', 'review_ready',
                             'approved', 'deployed', 'completed',
                             'rejected', 'cancelled') DEFAULT 'pending',
    execution_log       JSON,                    -- [{timestamp, action, detail}]
    result              JSON,                    -- {diff_html, affected_pages, issues}
    assigned_to         VARCHAR(100) DEFAULT 'ai',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_strategic (strategic_task_id),
    INDEX idx_site_status (target_site_id, status),
    INDEX idx_page_status (target_page_id, status),
    FOREIGN KEY (strategic_task_id) REFERENCES strategic_tasks(id),
    FOREIGN KEY (target_site_id) REFERENCES sites(id),
    FOREIGN KEY (target_page_id) REFERENCES pages(id)
);
```

**状態遷移:**
```
pending → in_progress → review_ready → approved → deployed → completed
                      ↘ rejected → (再実行で in_progress に戻る)
```

### 2-3. Site（サイト）

```sql
CREATE TABLE sites (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id           VARCHAR(50) NOT NULL UNIQUE,
    name                VARCHAR(255) NOT NULL,
    domain              VARCHAR(255),
    xserver_ftp_host    VARCHAR(255),
    xserver_ftp_user    VARCHAR(255),
    xserver_ftp_pass    TEXT,                    -- encrypted
    xserver_ftp_path    VARCHAR(255) DEFAULT '/',
    gas_generator_url   VARCHAR(500),            -- clinic-page-generator URL
    design_id           BIGINT UNSIGNED NULL,
    settings            JSON,                    -- サイト固有設定
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (design_id) REFERENCES site_designs(id)
);
```

### 2-4. Page（ページ）

```sql
CREATE TABLE pages (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id                 BIGINT UNSIGNED NOT NULL,
    slug                    VARCHAR(255) NOT NULL,
    title                   VARCHAR(255) NOT NULL,
    page_type               ENUM('top', 'lower', 'blog', 'news', 'case') DEFAULT 'lower',
    template_key            VARCHAR(100) DEFAULT 'generic',
    content_classification  ENUM('standard', 'assisted', 'exception') DEFAULT 'standard',
    meta                    JSON,                -- {title, description, og_image, keywords}
    current_generation_id   BIGINT UNSIGNED NULL,
    dna_source_key          VARCHAR(100),        -- DNA-OS上の対応キー (treatment_key等)
    sort_order              INT DEFAULT 0,
    is_published            BOOLEAN DEFAULT FALSE,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE INDEX idx_site_slug (site_id, slug),
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (current_generation_id) REFERENCES page_generations(id)
);
```

### 2-5. PageGeneration（ページ世代）

```sql
CREATE TABLE page_generations (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id             BIGINT UNSIGNED NOT NULL,
    generation          INT UNSIGNED NOT NULL,
    source              ENUM('ai_generated', 'manual', 'imported', 'partial_regen') NOT NULL,
    source_url          VARCHAR(500),
    source_task_id      VARCHAR(24),             -- channel_task_id
    sections            JSON NOT NULL,           -- セクション配列 (後述)
    human_patches       JSON,                    -- [{section_id, patch, reason, user_id, at}]
    final_html          LONGTEXT,                -- sections結合 + patches適用後
    status              ENUM('draft', 'ready', 'approved', 'published', 'superseded')
                        DEFAULT 'draft',
    approved_by         BIGINT UNSIGNED NULL,
    approved_at         TIMESTAMP NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_page_gen (page_id, generation),
    INDEX idx_status (status),
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
```

**sections JSON構造:**
```json
[
  {
    "section_id": "sec_01",
    "heading": "虫歯治療とは",
    "content_html": "<section class=\"com-section pt0\">...</section>",
    "lock_status": "unlocked",
    "last_modified_by": "ai",
    "last_modified_at": "2026-03-18T10:00:00+09:00",
    "order": 1
  },
  {
    "section_id": "sec_02",
    "heading": "こんなお悩みはありませんか？",
    "content_html": "<section class=\"com-section com-bgc-gray-white\">...</section>",
    "lock_status": "human_locked",
    "last_modified_by": "human:3",
    "last_modified_at": "2026-03-18T14:30:00+09:00",
    "order": 2
  }
]
```

**状態遷移:**
```
draft → ready → approved → published → superseded
                         ↗ (ロールバック時)
```

### 2-6. ExceptionContent（例外コンテンツ）

```sql
CREATE TABLE exception_contents (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id             BIGINT UNSIGNED NOT NULL,
    content_type        ENUM('case', 'compliance_text', 'effect_claim') NOT NULL,
    title               VARCHAR(255) NOT NULL,
    content_html        LONGTEXT,
    structured_data     JSON,                    -- 症例構造化データ等
    compliance_notes    TEXT,
    compliance_check    JSON,                    -- 自動チェック結果
    status              ENUM('draft', 'first_review', 'final_review',
                             'approved', 'published', 'rejected', 'archived')
                        DEFAULT 'draft',
    visibility          ENUM('private', 'limited', 'public') DEFAULT 'private',
    publish_expires_at  TIMESTAMP NULL,
    first_approved_by   BIGINT UNSIGNED NULL,
    first_approved_at   TIMESTAMP NULL,
    final_approved_by   BIGINT UNSIGNED NULL,
    final_approved_at   TIMESTAMP NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (first_approved_by) REFERENCES users(id),
    FOREIGN KEY (final_approved_by) REFERENCES users(id)
);
```

### 2-7. ImprovementReport（改善レポート）

```sql
CREATE TABLE improvement_reports (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id           VARCHAR(50) NOT NULL,
    site_id             BIGINT UNSIGNED NOT NULL,
    report_type         ENUM('seo', 'content_quality', 'performance',
                             'compliance', 'comprehensive') NOT NULL,
    title               VARCHAR(255) NOT NULL,
    summary             TEXT,
    findings            JSON,                    -- [{category, severity, detail, suggestion}]
    generated_by        VARCHAR(100),            -- 'ai_chief' or 'human:{user_id}'
    status              ENUM('draft', 'reviewed', 'actioned', 'archived')
                        DEFAULT 'draft',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (site_id) REFERENCES sites(id)
);
```

### 2-8. ApprovalRecord（承認記録）

```sql
CREATE TABLE approval_records (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    approvable_type     VARCHAR(100) NOT NULL,   -- 'strategic_task' / 'channel_task' / 'exception_content'
    approvable_id       VARCHAR(100) NOT NULL,
    approval_type       ENUM('approve', 'reject', 'send_back') NOT NULL,
    approval_level      ENUM('standard', 'first_review', 'final_review') DEFAULT 'standard',
    approved_by         BIGINT UNSIGNED NOT NULL,
    comment             TEXT,
    diff_snapshot       JSON,                    -- 承認時の差分スナップショット
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_approvable (approvable_type, approvable_id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
```

### 2-9. DeployRecord（デプロイ記録）

```sql
CREATE TABLE deploy_records (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id             BIGINT UNSIGNED NOT NULL,
    generation_snapshot JSON NOT NULL,            -- {page_id: generation_id, ...}
    deploy_status       ENUM('building', 'deploying', 'success', 'failed', 'rolled_back')
                        DEFAULT 'building',
    deployed_by         BIGINT UNSIGNED NOT NULL,
    build_path          VARCHAR(500),
    error_message       TEXT,
    rollback_of         BIGINT UNSIGNED NULL,    -- ロールバック元のデプロイID
    deployed_at         TIMESTAMP NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (deployed_by) REFERENCES users(id),
    FOREIGN KEY (rollback_of) REFERENCES deploy_records(id)
);
```

### 2-10. FreeInputRequest（フリー入力修正依頼）

```sql
CREATE TABLE free_input_requests (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id           VARCHAR(50) NOT NULL,
    site_id             BIGINT UNSIGNED NULL,
    raw_text            TEXT NOT NULL,
    ai_interpretation   JSON,                    -- AI解釈結果
    interpretation_status ENUM('pending', 'interpreted', 'confirmed', 'rejected')
                        DEFAULT 'pending',
    strategic_task_id   CHAR(20) NULL,           -- 変換後のタスクID
    submitted_by        BIGINT UNSIGNED NOT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (submitted_by) REFERENCES users(id),
    FOREIGN KEY (strategic_task_id) REFERENCES strategic_tasks(id)
);
```

### 2-11. OrchestrationLog（オーケストレーションログ）

```sql
CREATE TABLE orchestration_logs (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id           VARCHAR(50) NOT NULL,
    event_type          ENUM('dna_change_detected', 'task_generated', 'task_converted',
                             'report_generated', 'interpretation_completed',
                             'approval_requested', 'deployment_triggered') NOT NULL,
    source_type         VARCHAR(100),            -- トリガー元の種別
    source_id           VARCHAR(100),            -- トリガー元のID
    detail              JSON,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_clinic_event (clinic_id, event_type),
    INDEX idx_created (created_at)
);
```

### 2-12. User（ユーザー）

```sql
CREATE TABLE users (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(255) NOT NULL,
    email               VARCHAR(255) NOT NULL UNIQUE,
    password            VARCHAR(255) NOT NULL,
    role                ENUM('admin', 'editor', 'client') DEFAULT 'editor',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE site_user (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id             BIGINT UNSIGNED NOT NULL,
    user_id             BIGINT UNSIGNED NOT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE INDEX idx_site_user (site_id, user_id),
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 2-13. GeneratedContentSource（生成コンテンツ元記録）

```sql
CREATE TABLE generated_content_sources (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id           VARCHAR(50) NOT NULL,
    page_id             BIGINT UNSIGNED NULL,
    source_type         ENUM('google_docs', 'markup_txt', 'gas_api') NOT NULL,
    source_url          VARCHAR(500),
    source_meta         JSON,                    -- {clinic_id, treatment_key, generated_at}
    fetched_html        LONGTEXT,
    fetched_at          TIMESTAMP NOT NULL,
    page_generation_id  BIGINT UNSIGNED NULL,    -- 取り込み先の世代
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (page_id) REFERENCES pages(id),
    FOREIGN KEY (page_generation_id) REFERENCES page_generations(id)
);
```

### 2-14. DesignToken / Component / SiteDesign

v2から変更なし。定義は [design-v2/02_data_model.md](../design-v2/02_data_model.md) を参照。

---

## 3. ER図（主要リレーション）

```
User ──┬── site_user ──── Site
       │                   │
       │                   ├── Page ──── PageGeneration
       │                   │              │
       │                   │              └── (sections JSON)
       │                   │
       │                   ├── ExceptionContent
       │                   │
       │                   ├── SiteDesign
       │                   │
       │                   ├── DeployRecord
       │                   │
       │                   └── ImprovementReport
       │
       ├── ApprovalRecord
       │
       ├── FreeInputRequest
       │
       └── (StrategicTask.approved_by)

StrategicTask ──── ChannelTask ──── Page (target)

OrchestrationLog (独立、参照のみ)

GeneratedContentSource ──── Page + PageGeneration
```

---

## 4. v2からの主なスキーマ変更点

| 変更 | 理由 |
|------|------|
| `strategic_tasks` テーブル追加 | 戦略タスクの2段構造対応 |
| `channel_tasks` テーブル追加 | チャネルタスクの管理 |
| `page_generations.sections` カラム追加 (JSON) | セクション単位管理対応 |
| `improvement_reports` テーブル追加 | 改善レポート管理 |
| `free_input_requests` テーブル追加 | フリー入力修正依頼管理 |
| `approval_records` テーブル追加 | 統一的な承認記録 |
| `orchestration_logs` テーブル追加 | AI幕僚長の行動ログ |
| `generated_content_sources` テーブル追加 | 生成元のトレーサビリティ |
| `pages.content_classification` カラム追加 | コンテンツ3分類対応 |
| `pages.dna_source_key` カラム追加 | DNA-OSとの紐づけ |
