<?php
require_once __DIR__ . '/tasks.php';

// Перевіряємо, що запит надійшов методом POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Отримуємо ID завдання
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die('Некоректний ID завдання.');
}

// Позначаємо завдання як виконане
markDone($id);

// Повертаємося до списку
header('Location: index.php');
exit;

