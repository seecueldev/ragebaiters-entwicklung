-- =======================================================
-- Ragebaiters – Datenbank-Schema
-- Importiere diese Datei in phpMyAdmin (bplaced)
-- =======================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Tabelle: users  (Teammitglieder-Accounts)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(64)  NOT NULL,
  `email`         VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('admin','member') NOT NULL DEFAULT 'member',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabelle: invites  (Einladungscodes zur Registrierung)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `invites` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`       VARCHAR(64)  NOT NULL,
  `used_by`    INT UNSIGNED NULL,
  `used_at`    DATETIME     NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_code` (`code`),
  KEY `fk_invite_user` (`used_by`),
  CONSTRAINT `fk_invite_user` FOREIGN KEY (`used_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabelle: photos  (hochgeladene Bilder)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `photos` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `filename`   VARCHAR(255) NOT NULL,
  `thumbname`  VARCHAR(255) NOT NULL,
  `title`      VARCHAR(160) NULL,
  `mime`       VARCHAR(64)  NOT NULL,
  `size_bytes` INT UNSIGNED NOT NULL,
  `width`      INT UNSIGNED NULL,
  `height`     INT UNSIGNED NULL,
  `uploaded_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_photos_uploaded_at` (`uploaded_at`),
  KEY `fk_photos_user` (`user_id`),
  CONSTRAINT `fk_photos_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Startwerte
-- -----------------------------------------------------
-- Beispiel-Einladungscode: TEAM-RAGEBAIT-2026
INSERT INTO `invites` (`code`) VALUES ('TEAM-RAGEBAIT-2026');

SET FOREIGN_KEY_CHECKS = 1;
