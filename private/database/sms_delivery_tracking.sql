-- ============================================================
-- Migration: SMS delivery tracking
-- Uaddara Basic School — SBA Management System
-- Run once in phpMyAdmin or MySQL console.
--
-- Until now every SMS was logged as 'sent' with no gateway ID,
-- so nobody could tell which messages actually reached a phone.
-- These columns keep the gateway's batch/message IDs and status
-- so the SMS Centre can check delivery.
--
-- status values from now on:
--   pending     accepted by the gateway, waiting for the network
--   delivered   the phone received it
--   undelivered / expired / rejected / failed   did not arrive
--   sent        old rows from before this migration (not tracked)
-- ============================================================

ALTER TABLE `sms_logs`
  ADD COLUMN `batch_id`          VARCHAR(64)       NULL AFTER `status`,
  ADD COLUMN `message_id`        VARCHAR(64)       NULL AFTER `batch_id`,
  ADD COLUMN `status_code`       SMALLINT UNSIGNED NULL AFTER `message_id`,
  ADD COLUMN `status_checked_at` TIMESTAMP         NULL DEFAULT NULL AFTER `status_code`,
  ADD INDEX `idx_status` (`status`);
