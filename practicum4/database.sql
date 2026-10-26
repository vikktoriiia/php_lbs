-- Практична робота №4
-- Варіант №7: Трекер завдань

-- Створення бази даних
CREATE DATABASE IF NOT EXISTS practicum4
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Вибір бази даних
USE practicum4;

-- Створення таблиці завдань
CREATE TABLE IF NOT EXISTS tasks (
                                     id INT AUTO_INCREMENT PRIMARY KEY,
                                     title VARCHAR(255) NOT NULL,
    due_date DATE DEFAULT NULL,
    priority VARCHAR(20) NOT NULL DEFAULT 'Низький',
    done TINYINT(1) NOT NULL DEFAULT 0
    );
