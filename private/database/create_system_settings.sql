-- ============================================================
-- Migration: Create system_settings table
-- Uaddara Basic School — SBA Management System
-- Run this once in phpMyAdmin or via the migration script
-- ============================================================

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id`            int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key`   varchar(100)     NOT NULL,
  `setting_value` text             DEFAULT NULL,
  `category`      varchar(50)      NOT NULL DEFAULT 'general',
  `updated_at`    timestamp        NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Seed default values (safe to re-run — INSERT IGNORE) ──────
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `category`) VALUES
  ('school_name',        'Uaddara Basic School',    'branding'),
  ('school_body',        'Armed Forces Education Unit', 'branding'),
  ('school_tagline',     'SBA Management System',   'branding'),
  ('brand_accent_color', '#9633cc',                 'branding'),
  ('school_logo',        '/assets/img/school-logo.png', 'branding'),
  ('sms_api_key',        '',                        'connectivity'),
  ('sms_host',           'api.smsonlinegh.com',     'connectivity'),
  ('sms_sender',         'Fabric Flow',             'connectivity');
