-- =====================================================================
-- Plus Agency — live tender migrations (idempotent)
-- Run once in phpMyAdmin (SQL tab). Safe to re-run; each step is skipped
-- if the column / index / table already exists.
-- Covers: consolidated tender migration (recent columns + indexes) and the
-- new tender_audit_logs table. Assumes the core tender tables already exist.
-- =====================================================================

-- ---- helper procedures (check-then-add) -----------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS _addcol $$
CREATE PROCEDURE _addcol(IN tbl VARCHAR(64), IN col VARCHAR(64), IN ddl TEXT)
BEGIN
  IF EXISTS (SELECT 1 FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = tbl)
     AND NOT EXISTS (SELECT 1 FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = tbl AND column_name = col) THEN
    SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', ddl);
    PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
  END IF;
END $$

DROP PROCEDURE IF EXISTS _addidx $$
CREATE PROCEDURE _addidx(IN tbl VARCHAR(64), IN idx VARCHAR(64), IN cols TEXT)
BEGIN
  IF EXISTS (SELECT 1 FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = tbl)
     AND NOT EXISTS (SELECT 1 FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = tbl AND index_name = idx) THEN
    SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD INDEX `', idx, '` (', cols, ')');
    PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
  END IF;
END $$

DELIMITER ;

-- ---- tender_purchases: columns added after the original create -------
CALL _addcol('tender_purchases', 'company_name',      "`company_name` VARCHAR(200) NULL");
CALL _addcol('tender_purchases', 'company_address',   "`company_address` TEXT NULL");
CALL _addcol('tender_purchases', 'purchased_modules', "`purchased_modules` TEXT NULL");
CALL _addcol('tender_purchases', 'paid_at',           "`paid_at` TIMESTAMP NULL");
CALL _addcol('tender_purchases', 'access_status',     "`access_status` VARCHAR(20) NOT NULL DEFAULT 'active'");
CALL _addcol('tender_purchases', 'suspend_reason',    "`suspend_reason` VARCHAR(255) NULL");
CALL _addcol('tender_purchases', 'suspended_at',      "`suspended_at` TIMESTAMP NULL");
CALL _addcol('tender_purchases', 'payment_reference', "`payment_reference` VARCHAR(100) NULL");
CALL _addcol('tender_purchases', 'invoice',           "`invoice` VARCHAR(255) NULL");

-- ---- basic_settings_extra: tender toggles + watermark + encryption ---
CALL _addcol('basic_settings_extra', 'is_tender',                         "`is_tender` TINYINT NOT NULL DEFAULT 1");
CALL _addcol('basic_settings_extra', 'tender_breadcrumb_bg',              "`tender_breadcrumb_bg` VARCHAR(255) NULL");
CALL _addcol('basic_settings_extra', 'tender_breadcrumb_overlay_color',   "`tender_breadcrumb_overlay_color` VARCHAR(20) NULL");
CALL _addcol('basic_settings_extra', 'tender_breadcrumb_overlay_opacity', "`tender_breadcrumb_overlay_opacity` DECIMAL(3,2) NULL");
CALL _addcol('basic_settings_extra', 'tender_watermark_enabled',          "`tender_watermark_enabled` TINYINT NOT NULL DEFAULT 1");
CALL _addcol('basic_settings_extra', 'tender_watermark_template',         "`tender_watermark_template` TEXT NULL");
CALL _addcol('basic_settings_extra', 'tender_watermark_opacity',          "`tender_watermark_opacity` DECIMAL(3,2) NOT NULL DEFAULT 0.30");
CALL _addcol('basic_settings_extra', 'tender_watermark_color',            "`tender_watermark_color` VARCHAR(20) NOT NULL DEFAULT 'FF0000'");
CALL _addcol('basic_settings_extra', 'tender_watermark_font_size',        "`tender_watermark_font_size` SMALLINT NOT NULL DEFAULT 24");
CALL _addcol('basic_settings_extra', 'tender_watermark_rotation',         "`tender_watermark_rotation` SMALLINT NOT NULL DEFAULT 45");
CALL _addcol('basic_settings_extra', 'tender_pdf_encrypt_enabled',        "`tender_pdf_encrypt_enabled` TINYINT NOT NULL DEFAULT 0");
CALL _addcol('basic_settings_extra', 'tender_pdf_password',               "`tender_pdf_password` VARCHAR(255) NULL");

-- ---- pages: special page type ---------------------------------------
CALL _addcol('pages', 'page_type', "`page_type` VARCHAR(50) NULL");

-- ---- seed the default watermark template on rows that lack one -------
UPDATE `basic_settings_extra`
SET `tender_watermark_template` = '{company}\nDownloaded by: {name}\nTender ID: {tender_code}\n{datetime}'
WHERE `tender_watermark_template` IS NULL OR `tender_watermark_template` = '';

-- ---- indexes (read patterns the module runs) ------------------------
CALL _addidx('tenders',          'idx_tenders_lang_featured',   '`language_id`, `is_featured`');
CALL _addidx('tenders',          'idx_tenders_lang_slug',       '`language_id`, `slug`');
CALL _addidx('tenders',          'idx_tenders_category',        '`tender_category_id`');
CALL _addidx('tender_modules',   'idx_tmodules_tender_status',  '`tender_id`, `status`');
CALL _addidx('tender_sections',  'idx_tsections_module',        '`tender_module_id`');
CALL _addidx('tender_purchases', 'idx_tpurchases_tender',       '`tender_id`');
CALL _addidx('tender_purchases', 'idx_tpurchases_order',        '`order_number`');
CALL _addidx('tender_purchases', 'idx_tpurchases_email',        '`email`');
CALL _addidx('tender_purchases', 'idx_tpurchases_pstatus',      '`payment_status`');
CALL _addidx('tender_categories','idx_tcategories_lang_status', '`language_id`, `status`');
CALL _addidx('pages',            'pages_page_type_index',       '`page_type`');

-- ---- tender_blacklists (recent; create if missing) ------------------
CREATE TABLE IF NOT EXISTS `tender_blacklists` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NULL,
  `phone_number` VARCHAR(255) NULL,
  `ip_address` VARCHAR(45) NULL,
  `company_name` VARCHAR(255) NULL,
  `reason` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `tender_blacklists_email_index` (`email`),
  KEY `tender_blacklists_phone_number_index` (`phone_number`),
  KEY `tender_blacklists_ip_address_index` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- tender_audit_logs (new) ----------------------------------------
CREATE TABLE IF NOT EXISTS `tender_audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` BIGINT UNSIGNED NULL,
  `admin_name` VARCHAR(255) NULL,
  `action` VARCHAR(50) NOT NULL,
  `tender_purchase_id` BIGINT UNSIGNED NULL,
  `order_number` VARCHAR(255) NULL,
  `description` VARCHAR(500) NULL,
  `meta` JSON NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `tender_audit_logs_admin_id_index` (`admin_id`),
  KEY `tender_audit_logs_action_index` (`action`),
  KEY `tender_audit_logs_tender_purchase_id_index` (`tender_purchase_id`),
  KEY `tender_audit_logs_order_number_index` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- cleanup --------------------------------------------------------
DROP PROCEDURE IF EXISTS _addcol;
DROP PROCEDURE IF EXISTS _addidx;

-- ---- OPTIONAL: mark migrations as run so `php artisan migrate` stays --
-- quiet later. Skip this block if you don't use artisan on live.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2024_01_01_000001_create_tender_module', (SELECT b FROM (SELECT COALESCE(MAX(batch),0)+1 AS b FROM `migrations`) t)
WHERE NOT EXISTS (SELECT 1 FROM (SELECT `migration` FROM `migrations`) m WHERE m.`migration` = '2024_01_01_000001_create_tender_module');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_04_000001_create_tender_audit_logs_table', (SELECT b FROM (SELECT COALESCE(MAX(batch),0)+1 AS b FROM `migrations`) t)
WHERE NOT EXISTS (SELECT 1 FROM (SELECT `migration` FROM `migrations`) m WHERE m.`migration` = '2026_07_04_000001_create_tender_audit_logs_table');
