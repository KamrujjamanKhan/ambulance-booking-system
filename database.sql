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

CREATE TABLE IF NOT EXISTS `ambulances` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_id`     INT UNSIGNED NOT NULL,
  `vehicle_type`  VARCHAR(100) NOT NULL,
  `license_plate` VARCHAR(50) NOT NULL UNIQUE,
  `status`        ENUM('Available', 'Busy', 'Offline') NOT NULL DEFAULT 'Offline',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bookings` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id`        INT UNSIGNED NOT NULL,
  `driver_id`         INT UNSIGNED DEFAULT NULL,
  `pickup_location`   VARCHAR(255) NOT NULL,
  `destination`       VARCHAR(255) NOT NULL,
  `status`            ENUM('Pending', 'Accepted', 'On the way', 'Arrived', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `emergency_details` TEXT,
  `created_at`        DATETIME NOT NULL,
  `updated_at`        DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

