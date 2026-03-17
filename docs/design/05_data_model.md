# 5. データモデル設計

## ER図（概念）

```
Site 1──* Page 1──* Section 1──* ContentVariant
  │                    │              │
  │                    │              *
  │                    │         ApprovalRecord
  │                    │
  │                    *──* OverrideRule (Section単位)
  │
  *── PublishRecord
  │
  *── ExceptionContent ──? Section (linked)
  │
  *── SiteUser (中間テーブル)
        │
        *── User
```

## マイグレーション定義

### sites

```sql
CREATE TABLE sites (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id       VARCHAR(50) NOT NULL COMMENT 'DNA-OS clinic_id',
    name            VARCHAR(255) NOT NULL COMMENT '医院名',
    domain          VARCHAR(255) NULL COMMENT '公開ドメイン',
    xserver_host    VARCHAR(255) NULL,
    xserver_ftp_user VARCHAR(255) NULL,
    xserver_ftp_pass TEXT NULL COMMENT '暗号化保存',
    xserver_deploy_path VARCHAR(500) DEFAULT '/public_html',
    template_set    VARCHAR(100) DEFAULT 'default',
    status          ENUM('active','maintenance','archived') DEFAULT 'active',
    wp_site_url     VARCHAR(500) NULL COMMENT '移行期: 既存WPサイトURL',
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    INDEX idx_clinic_id (clinic_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### pages

```sql
CREATE TABLE pages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id         BIGINT UNSIGNED NOT NULL,
    slug            VARCHAR(255) NOT NULL COMMENT 'URLパス',
    title           VARCHAR(500) NOT NULL,
    page_type       ENUM('top','lower','blog','news','exception') DEFAULT 'lower',
    template_name   VARCHAR(255) DEFAULT 'default_lower',
    meta_description TEXT NULL,
    og_image_path   VARCHAR(500) NULL,
    status          ENUM('draft','pending_review','approved','published','archived') DEFAULT 'draft',
    publish_version INT UNSIGNED DEFAULT 0,
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_site_slug (site_id, slug),
    INDEX idx_status (status),
    INDEX idx_page_type (page_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### sections

```sql
CREATE TABLE sections (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id             BIGINT UNSIGNED NOT NULL,
    section_key         VARCHAR(100) NOT NULL COMMENT 'テンプレート内スロット名',
    sort_order          INT DEFAULT 0,
    content_source_type ENUM('dna_os','manual','exception','client_post') DEFAULT 'dna_os',
    content_source_ref  JSON NULL COMMENT '{"sheet":"03_DNA_Master","record_id":"xxx","field":"content"}',
    is_human_edited     BOOLEAN DEFAULT FALSE,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_page_section (page_id, section_key),
    INDEX idx_source_type (content_source_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### content_variants

```sql
CREATE TABLE content_variants (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id          BIGINT UNSIGNED NOT NULL,
    version             INT UNSIGNED NOT NULL,
    source_type         ENUM('dna_os_sync','human_edit','ai_regenerated') NOT NULL,
    content_html        LONGTEXT NOT NULL COMMENT '表示用HTML',
    content_raw         LONGTEXT NULL COMMENT '原文テキスト',
    original_content    LONGTEXT NULL COMMENT 'DNA-OS原本（差分基準）',
    diff_from_original  JSON NULL COMMENT '原本からの差分',
    edited_by           BIGINT UNSIGNED NULL,
    edit_reason         TEXT NULL COMMENT '修正理由',
    status              ENUM('draft','pending_review','approved','published','superseded') DEFAULT 'draft',
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    FOREIGN KEY (edited_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uniq_section_version (section_id, version),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### approval_records

```sql
CREATE TABLE approval_records (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    variant_id  BIGINT UNSIGNED NOT NULL,
    action      ENUM('approve','reject','send_back') NOT NULL,
    reviewer_id BIGINT UNSIGNED NOT NULL,
    notes       TEXT NULL,
    created_at  TIMESTAMP NULL,
    FOREIGN KEY (variant_id) REFERENCES content_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_variant (variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### publish_records

```sql
CREATE TABLE publish_records (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id         BIGINT UNSIGNED NOT NULL,
    pages_json      JSON NOT NULL COMMENT '公開対象ページIDと各バージョン',
    snapshot_path   VARCHAR(500) NULL COMMENT 'ビルド成果物パス',
    deploy_status   ENUM('pending','building','deploying','success','failed','rolled_back') DEFAULT 'pending',
    deployed_by     BIGINT UNSIGNED NULL,
    deployed_at     TIMESTAMP NULL,
    rollback_of     BIGINT UNSIGNED NULL COMMENT 'ロールバック元',
    error_log       TEXT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (deployed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (rollback_of) REFERENCES publish_records(id) ON DELETE SET NULL,
    INDEX idx_site_status (site_id, deploy_status),
    INDEX idx_deployed_at (deployed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### override_rules

```sql
CREATE TABLE override_rules (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id  BIGINT UNSIGNED NOT NULL,
    policy      ENUM('auto_sync','confirm_before_sync','manual_only','locked') DEFAULT 'auto_sync',
    reason      TEXT NULL,
    set_by      BIGINT UNSIGNED NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    FOREIGN KEY (set_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uniq_section (section_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### exception_contents

```sql
CREATE TABLE exception_contents (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id                     BIGINT UNSIGNED NOT NULL,
    content_type                ENUM('case_study','medical_ad_gl','effect_claim','other') NOT NULL,
    title                       VARCHAR(500) NOT NULL,
    content_html                LONGTEXT NOT NULL,
    risk_level                  ENUM('high','critical') DEFAULT 'high',
    compliance_notes            TEXT NULL COMMENT 'GL対応メモ',
    requires_specialist_review  BOOLEAN DEFAULT TRUE,
    status                      ENUM('draft','under_review','approved','published','suspended') DEFAULT 'draft',
    linked_section_id           BIGINT UNSIGNED NULL,
    reviewed_by                 BIGINT UNSIGNED NULL,
    reviewed_at                 TIMESTAMP NULL,
    created_at                  TIMESTAMP NULL,
    updated_at                  TIMESTAMP NULL,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (linked_section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_site_type (site_id, content_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### users

```sql
CREATE TABLE users (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','editor','reviewer','client') DEFAULT 'editor',
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### site_user（中間テーブル: ユーザーのサイトアクセス制御）

```sql
CREATE TABLE site_user (
    id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_site_user (site_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### sync_logs（DNA-OS同期ログ）

```sql
CREATE TABLE sync_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id         BIGINT UNSIGNED NOT NULL,
    sync_type       ENUM('manual','scheduled') NOT NULL,
    sections_updated INT DEFAULT 0,
    sections_skipped INT DEFAULT 0 COMMENT '上書き制御でスキップ',
    sections_conflicted INT DEFAULT 0 COMMENT '要確認',
    details         JSON NULL,
    status          ENUM('success','partial','failed') NOT NULL,
    started_at      TIMESTAMP NULL,
    completed_at    TIMESTAMP NULL,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_site_date (site_id, started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
