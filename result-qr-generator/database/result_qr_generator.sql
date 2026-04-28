-- Result QR Generator Database
-- Database Name: result_qr_generator
-- Default Admin:
-- Email: admin@example.com
-- Password: admin123
-- Password Hash generated using PHP password_hash()

CREATE DATABASE IF NOT EXISTS `result_qr_generator`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `result_qr_generator`;

DROP TABLE IF EXISTS `students_results`;
DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `students_results` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_name` VARCHAR(150) NOT NULL,
    `father_name` VARCHAR(150) NOT NULL,
    `roll_no` VARCHAR(100) NOT NULL UNIQUE,
    `registration_no` VARCHAR(100) DEFAULT NULL,
    `program` VARCHAR(150) DEFAULT NULL,
    `department` VARCHAR(150) DEFAULT NULL,
    `session` VARCHAR(100) DEFAULT NULL,
    `semester` VARCHAR(50) DEFAULT NULL,
    `exam_title` VARCHAR(200) DEFAULT NULL,
    `total_marks` INT DEFAULT NULL,
    `obtained_marks` INT DEFAULT NULL,
    `percentage` DECIMAL(5,2) DEFAULT NULL,
    `grade` VARCHAR(20) DEFAULT NULL,
    `result_status` VARCHAR(50) DEFAULT NULL,
    `qr_token` VARCHAR(255) NOT NULL UNIQUE,
    `qr_image` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` (`name`, `email`, `password`) VALUES
('Administrator', 'admin@example.com', '$2y$10$K.lJwSfO563oTA4Raqydce1r7.cVsDKr9vSvdepQVjBQ8Rvm3kZLa');
