-- Migration 001: Criacao das tabelas do sistema SisFrota Municipal
-- Baseado estritamente nas especificacoes do FSD.md (Secao 11)

-- 1. Tabela de controle de migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `migration` VARCHAR(255) NOT NULL,
  `executed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de usuarios do sistema
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('ADMIN', 'OPERATOR', 'MANAGER') NOT NULL DEFAULT 'OPERATOR',
  `status` ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NULL,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` BIGINT UNSIGNED NULL,
  INDEX `idx_users_username` (`username`),
  INDEX `idx_users_role_status` (`role`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de secretarias municipais
CREATE TABLE IF NOT EXISTS `secretariats` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `acronym` VARCHAR(20) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NULL,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` BIGINT UNSIGNED NULL,
  INDEX `idx_secretariats_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela de veiculos da frota
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `plate` VARCHAR(10) NOT NULL UNIQUE,
  `model` VARCHAR(80) NOT NULL,
  `brand` VARCHAR(50) NULL,
  `year` INT UNSIGNED NULL,
  `secretariat_id` BIGINT UNSIGNED NULL,
  `fuel_type` ENUM('GASOLINE', 'ETHANOL', 'DIESEL', 'FLEX') NOT NULL DEFAULT 'FLEX',
  `current_km` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('ACTIVE', 'MAINTENANCE', 'UNAVAILABLE') NOT NULL DEFAULT 'ACTIVE',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NULL,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` BIGINT UNSIGNED NULL,
  CONSTRAINT `fk_vehicles_secretariat` FOREIGN KEY (`secretariat_id`) REFERENCES `secretariats` (`id`) ON DELETE SET NULL,
  INDEX `idx_vehicles_plate` (`plate`),
  INDEX `idx_vehicles_status_deleted` (`status`, `is_deleted`),
  INDEX `idx_vehicles_secretariat` (`secretariat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela de categorias de manutencao mecânica
CREATE TABLE IF NOT EXISTS `maintenance_categories` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(80) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL,
  `status` ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NULL,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` BIGINT UNSIGNED NULL,
  INDEX `idx_maint_cat_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela de abastecimentos de combustivel
CREATE TABLE IF NOT EXISTS `fuelings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vehicle_id` BIGINT UNSIGNED NOT NULL,
  `fueling_date` DATETIME NOT NULL,
  `km` INT UNSIGNED NOT NULL,
  `liters` DECIMAL(10, 2) NOT NULL,
  `total_price` DECIMAL(10, 2) NOT NULL,
  `unit_price` DECIMAL(10, 2) GENERATED ALWAYS AS (`total_price` / `liters`) STORED,
  `fuel_type` ENUM('GASOLINE', 'ETHANOL', 'DIESEL', 'FLEX') NOT NULL,
  `station_name` VARCHAR(100) NOT NULL,
  `avg_kml` DECIMAL(8, 2) NULL COMMENT 'Calculado automaticamente a partir do 2º abastecimento',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NOT NULL,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` BIGINT UNSIGNED NULL,
  CONSTRAINT `fk_fuelings_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_fuelings_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  INDEX `idx_fuelings_vehicle_date` (`vehicle_id`, `fueling_date`),
  INDEX `idx_fuelings_date` (`fueling_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabela de manutençoes operacionais
CREATE TABLE IF NOT EXISTS `maintenances` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vehicle_id` BIGINT UNSIGNED NOT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `service_date` DATE NOT NULL,
  `workshop_name` VARCHAR(100) NOT NULL,
  `os_number` VARCHAR(50) NOT NULL,
  `cost` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('SCHEDULED', 'IN_PROGRESS', 'PENDING', 'COMPLETED') NOT NULL DEFAULT 'PENDING',
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NOT NULL,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` BIGINT UNSIGNED NULL,
  CONSTRAINT `fk_maintenances_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_maintenances_category` FOREIGN KEY (`category_id`) REFERENCES `maintenance_categories` (`id`),
  CONSTRAINT `fk_maintenances_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  INDEX `idx_maint_vehicle_date` (`vehicle_id`, `service_date`),
  INDEX `idx_maint_status` (`status`),
  INDEX `idx_maint_service_date` (`service_date`),
  INDEX `idx_maint_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabela de logs do sistema e auditoria
CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_type` ENUM('ERROR', 'SECURITY_AUDIT') NOT NULL,
  `severity` ENUM('INFO', 'WARNING', 'CRITICAL') NOT NULL DEFAULT 'INFO',
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `request_uri` VARCHAR(255) NULL,
  `message` TEXT NOT NULL,
  `details` LONGTEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_logs_type_created` (`event_type`, `created_at`),
  INDEX `idx_logs_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
