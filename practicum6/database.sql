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
-- Додавання тестових записів
-- Тестові дані додаються лише до порожньої таблиці
INSERT INTO tasks (title, due_date, priority, done)
SELECT title, due_date, priority, done
FROM (
         SELECT 'Підготувати звіт до ПР6' AS title,
                '2026-11-30' AS due_date,
                'Високий' AS priority, 0 AS done

         UNION ALL
         SELECT 'Виконати лабораторну роботу',
                '2026-11-28', 'Середній', 0

         UNION ALL
         SELECT 'Перевірити роботу API',
                '2026-11-24', 'Високий', 1

         UNION ALL
         SELECT 'Підготуватись до практики',
                '2026-11-29', 'Середній', 0

         UNION ALL
         SELECT 'Помити посуд',
                '2026-11-25', 'Низький', 0
     ) AS test_data
WHERE NOT EXISTS (SELECT 1 FROM tasks);
