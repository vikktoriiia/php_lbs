<?php
require_once __DIR__ . '/db.php';

/** @var PDO $pdo */

header('Content-Type: application/json; charset=utf-8');

try {
    // Дозволяємо тільки POST-запити
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'error' => 'Метод не дозволений'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Отримуємо дані з форми
    $title = trim($_POST['title'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? 'Низький';

    // Перевіряємо назву завдання
    if ($title === '') {
        http_response_code(400);
        echo json_encode([
            'error' => 'Введіть назву завдання'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Перевіряємо пріоритет
    if (!in_array($priority, ['Низький', 'Середній', 'Високий'], true)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Невірний пріоритет'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Перевіряємо дату, якщо вона вказана
    if ($due_date !== '') {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $due_date);

        if (!$date || $date->format('Y-m-d') !== $due_date) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Невірний формат дати'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Додаємо завдання до бази даних
    $stmt = $pdo->prepare(
        'INSERT INTO tasks (title, due_date, priority, done)
         VALUES (:title, :due_date, :priority, 0)'
    );

    $stmt->execute([
        'title' => $title,
        'due_date' => $due_date !== '' ? $due_date : null,
        'priority' => $priority
    ]);

    // Отримуємо ID нового завдання
    $id = (int)$pdo->lastInsertId();

    // Повертаємо додане завдання
    http_response_code(201);

    echo json_encode([
        'id' => $id,
        'title' => $title,
        'due_date' => $due_date !== '' ? $due_date : null,
        'priority' => $priority,
        'done' => 0
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'error' => 'Помилка додавання завдання'
    ], JSON_UNESCAPED_UNICODE);
}
