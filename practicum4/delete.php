<?php
require_once __DIR__ . '/tasks.php';

// Дозволяємо видалення лише через POST-запит
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Отримуємо ID завдання
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// Перевіряємо правильність ID
if (!$id || $id < 1) {
    die('Некоректний ID завдання.');
}

// Викликаємо окрему функцію видалення
deleteTask($id);

// Повертаємося до списку завдань
header('Location: index.php');
exit;
