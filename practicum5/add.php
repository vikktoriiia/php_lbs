<?php
require_once __DIR__ . '/tasks.php';

// Обробляємо лише POST-запити
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Отримуємо дані з форми
$title = trim($_POST['title'] ?? '');
$due_date = $_POST['due_date'] ?? '';
$priority = $_POST['priority'] ?? 'Низький';

// Перевіряємо назву завдання
if ($title === '') {
    die('Назва завдання не може бути порожньою.');
}

// Перевіряємо пріоритет
if (!in_array($priority, ['Низький', 'Середній', 'Високий'], true)) {
    die('Некоректний пріоритет.');
}

// Викликаємо функцію додавання завдання
addTask(
    $title,
    $due_date !== '' ? $due_date : null,
    $priority
);

// Повертаємося до списку завдань
header('Location: index.php');
exit;
