<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? 'Низький';

    if ($title === '') {
        die('Назва завдання не може бути порожньою.');
    }

    if (!in_array($priority, ['Низький', 'Середній', 'Високий'], true)) {
        die('Некоректний пріоритет.');
    }

    $sql = "INSERT INTO tasks (title, due_date, priority, done)
            VALUES (:title, :due_date, :priority, 0)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':title' => $title,
        ':due_date' => $due_date !== '' ? $due_date : null,
        ':priority' => $priority
    ]);

    header('Location: index.php');
    exit;
}

header('Location: index.php');
exit;
