-- ============================================================
-- SECURITY: Add security columns to absensi table
-- ============================================================
-- Run this SQL to add security tracking columns

ALTER TABLE `absensi` 
ADD COLUMN `ip_address` VARCHAR(45) NULL AFTER `approval_status`,
ADD COLUMN `user_agent` TEXT NULL AFTER `ip_address`,
ADD COLUMN `device_type` VARCHAR(50) NULL AFTER `user_agent`,
ADD COLUMN `network_type` VARCHAR(50) NULL AFTER `device_type`,
ADD COLUMN `latitude` DECIMAL(10, 8) NULL AFTER `network_type`,
ADD COLUMN `longitude` DECIMAL(11, 8) NULL AFTER `latitude`,
ADD COLUMN `location_accuracy` FLOAT NULL AFTER `longitude`,
ADD COLUMN `mock_detected` BOOLEAN DEFAULT FALSE AFTER `location_accuracy`,
ADD COLUMN `is_suspicious` BOOLEAN DEFAULT FALSE AFTER `mock_detected`,
ADD COLUMN `validation_notes` TEXT NULL AFTER `is_suspicious`;

-- Add index for performance
ALTER TABLE `absensi` 
ADD INDEX `idx_ip_address` (`ip_address`),
ADD INDEX `idx_is_suspicious` (`is_suspicious`),
ADD INDEX `idx_mock_detected` (`mock_detected`);

-- ============================================================
-- SECURITY: Create school location configuration table
-- ============================================================
CREATE TABLE IF NOT EXISTS `school_config` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `config_key` VARCHAR(100) NOT NULL,
  `config_value` TEXT NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default school location (SMKN 1 Sanden, Bantul, Yogyakarta)
INSERT INTO `school_config` (`config_key`, `config_value`, `description`) VALUES
('school_latitude', '-7.9297', 'Latitude koordinat sekolah'),
('school_longitude', '110.2538', 'Longitude koordinat sekolah'),
('max_distance_meters', '100', 'Jarak maksimal absensi dari sekolah (meter)'),
('teleport_time_minutes', '10', 'Waktu minimum antar absensi untuk deteksi teleport (menit)'),
('teleport_distance_km', '20', 'Jarak minimum untuk deteksi teleport (kilometer)');
