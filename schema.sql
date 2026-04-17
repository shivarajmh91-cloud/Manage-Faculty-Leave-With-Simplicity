CREATE DATABASE IF NOT EXISTS faculty_leave_db;
USE faculty_leave_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(255) DEFAULT 'Faculty',
  branch VARCHAR(100) DEFAULT 'Others',
  role VARCHAR(50) DEFAULT 'faculty',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS leaves (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  leave_type VARCHAR(100) NOT NULL,
  from_date DATE NOT NULL,
  to_date DATE NOT NULL,
  branch VARCHAR(100),
  reason TEXT,
  status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  document_file VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Test admin user (password: admin123 hashed)
INSERT IGNORE INTO users (email, password, name, role) VALUES 
('test@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin'),
('test-faculty@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Faculty', 'faculty'),
('hod@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HOD', 'hod');

