-- Crop Management Portal Database Schema
-- MySQL Database Setup

-- Create Database
CREATE DATABASE IF NOT EXISTS crop_management;
USE crop_management;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(15),
  address VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email)
);

-- Crops Table
CREATE TABLE IF NOT EXISTS crops (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  crop_name VARCHAR(100) NOT NULL,
  crop_type VARCHAR(100) NOT NULL,
  planting_date DATE NOT NULL,
  expected_harvest_date DATE,
  status ENUM('Planning', 'Planting', 'Growing', 'Ready to Harvest', 'Harvested') DEFAULT 'Planning',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
);

-- Expenses Table
CREATE TABLE IF NOT EXISTS expenses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  crop_id INT NOT NULL,
  user_id INT NOT NULL,
  expense_category VARCHAR(100) NOT NULL,
  description VARCHAR(255),
  amount DECIMAL(10, 2) NOT NULL,
  expense_date DATE NOT NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_crop_id (crop_id),
  INDEX idx_user_id (user_id),
  INDEX idx_expense_date (expense_date)
);
-- Sales Table (NEW)
CREATE TABLE IF NOT EXISTS sales (
  id INT PRIMARY KEY AUTO_INCREMENT,
  crop_id INT NOT NULL,
  user_id INT NOT NULL,
  sale_date DATE NOT NULL,
  quantity_sold DECIMAL(10, 2) NOT NULL,
  quantity_unit VARCHAR(20) DEFAULT 'kg',
  price_per_unit DECIMAL(10, 2) NOT NULL,
  total_amount DECIMAL(10, 2) NOT NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_crop_id (crop_id),
  INDEX idx_user_id (user_id),
  INDEX idx_sale_date (sale_date),
  INDEX idx_payment_status (payment_status)
);

-- Sample Data
INSERT INTO users (name, email, password, phone, address) VALUES
('Raj Kumar Patel', 'farmer@example.com', '$2y$10$2QN2r6Yz8qLYHYN2m3N2eOfRJVYQaJQX2h3J7R7L7K7J7K7J7K7J', '9876543210', '123 Farm Road'),
('Priya Singh', 'priya@example.com', '$2y$10$2QN2r6Yz8qLYHYN2m3N2eOfRJVYQaJQX2h3J7R7L7K7J7K7J7K7J', '8765432109', '456 Green Fields');

INSERT INTO crops (user_id, crop_name, crop_type, planting_date, expected_harvest_date, status) VALUES
(1, 'Wheat', 'Cereal', '2024-01-15', '2024-05-15', 'Growing'),
(1, 'Cotton', 'Cash Crop', '2024-03-01', '2024-10-15', 'Growing'),
(2, 'Rice', 'Cereal', '2024-05-01', '2024-09-15', 'Planting');

INSERT INTO expenses (crop_id, user_id, expense_category, description, amount, expense_date, notes) VALUES
(1, 1, 'Seeds', 'Wheat seeds - Premium variety', 5000, '2024-01-10', 'Purchase from local vendor'),
(1, 1, 'Fertilizer', 'NPK Fertilizer - 2 bags', 3500, '2024-02-05', 'First application'),
(1, 1, 'Labor', 'Field preparation labor', 2000, '2024-01-15', 'Hired 3 workers for 2 days'),
(2, 1, 'Seeds', 'Cotton seeds - Hybrid BT', 8000, '2024-02-28', 'High quality seeds'),
(2, 1, 'Pesticide', 'Insecticide spray', 1500, '2024-04-10', 'Pest management'),
(3, 2, 'Seeds', 'Rice seeds - IR36 variety', 4000, '2024-04-20', 'Quality seeds'),
(3, 2, 'Labor', 'Seedbed preparation', 1200, '2024-04-25', 'Hired workers');

-- Create indexes for better query performance
CREATE INDEX idx_crops_user_status ON crops(user_id, status);
CREATE INDEX idx_expenses_crop_date ON expenses(crop_id, expense_date);
