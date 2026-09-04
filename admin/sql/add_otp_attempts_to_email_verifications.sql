-- ============================================================================
-- OTP support for account activation (email_verifications)
-- ----------------------------------------------------------------------------
-- Account activation now uses a 6-digit OTP code instead of a clickable link.
-- This adds the column that tracks how many wrong attempts have been made for
-- a code so we can lock it after 5 failures (standard OTP practice).
--
-- Run this ONCE against the same MySQL database as the rest of the system:
--     mysql -u<user> -p bodarepensionhouse < admin/sql/add_otp_attempts_to_email_verifications.sql
--
-- If you already ran a previous version of this file, the ALTER below will
-- fail with "Duplicate column name 'attempts'" — that is expected/harmless;
-- the column is already there.
-- ============================================================================

ALTER TABLE `email_verifications`
  ADD COLUMN `attempts` TINYINT(4) NOT NULL DEFAULT 0
  AFTER `used`;
