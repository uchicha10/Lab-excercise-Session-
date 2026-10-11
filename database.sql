-- Run this in phpMyAdmin (Import tab) or: mysql -u root < database.sql
CREATE DATABASE IF NOT EXISTS login_system
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE login_system;

CREATE TABLE IF NOT EXISTS users (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name  VARCHAR(100) NOT NULL,
  username   VARCHAR(50)  NOT NULL,
  password   VARCHAR(255) NOT NULL,
  role       ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demo accounts (passwords are bcrypt-hashed)
--   admin / Admin@123  (role: admin)
--   juan  / User@1234  (role: user)
INSERT INTO users (full_name, username, password, role) VALUES
('Administrator', 'admin', '$2y$10$rTYXcjfPPY.eF1LtZ9L3W.FEGC/DvYb8j4u.SDFljm7jgzf8BX6fO', 'admin'),
('Juan Dela Cruz', 'juan', '$2y$10$2t8642T65xa10JLaQdtfzuO4zp2aFyMGRiTMzFqyp4uUUPFXxWxf.', 'user');
