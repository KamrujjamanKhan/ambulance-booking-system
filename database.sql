-- Basic schema for AmbulanceHub authentication.
-- Import this file into your MySQL database (e.g. using phpMyAdmin)
-- and ensure config.php has the same database name / credentials.

CREATE DATABASE IF NOT EXISTS `ambulancehub`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ambulancehub`;

CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`     VARCHAR(150) NOT NULL,
  `email`         VARCHAR(190) NOT NULL UNIQUE,
  `phone`         VARCHAR(50)  NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('admin', 'patient', 'driver') NOT NULL DEFAULT 'patient',
  `created_at`    DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example admin user. Password: admin123
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`)
VALUES (
  'Site Administrator',
  'admin@example.com',
  '+8801000000000',
  '$2y$10$R62Uu46DDCKcy86B1mMfp.T9TK82U7wOG0frNdM5Dsi4hGkxCRxE.',
  'admin',
  NOW()
)
ON DUPLICATE KEY UPDATE email = email;

-- Example patient user. Password: patient01
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`)
VALUES (
  'Patient X',
  'patient01@gmail.com',
  '01789000000',
  '$2y$10$FL8pAshoKFdSxWgW7usuH.PnFiHezIO67d1WsPDUUKjwG2RSGu4Le',
  'patient',
  NOW()
)
ON DUPLICATE KEY UPDATE email = email;

-- Example driver user. Password: driver01
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`)
VALUES (
  'Driver X',
  'driver01@gmail.com',
  '01890000000',
  '$2y$10$06sg3UHJ2DVgoskOh6gkgeR3FFhIuZwvx8JwRASBpjuiE9eOhkWSq',
  'driver',
  NOW()
)
ON DUPLICATE KEY UPDATE email = email;
