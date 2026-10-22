<?php
require_once __DIR__ . '/db.php';

// Видаляємо завдання лише після POST-запиту
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Отримуємо ID завдання
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die('Некоректний ID завдання.');
}

// Видаляємо запис через підготовлений запит
$stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
$stmt->execute([':id' => $id]);

// Повертаємося до списку завдань
header('Location: index.php');
exit;
