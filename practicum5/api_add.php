<?php
require_once __DIR__ . '/api_common.php';

try {
    // Дозволяємо лише POST-запити
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');

        jsonResponse([
            'error' => 'Дозволено лише POST'
        ], 405);
    }

    // Отримуємо дані форми
    $title = $_POST['title'] ?? '';
    $dueDate = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? 'Низький';

    // Перевіряємо типи параметрів
    if (
        !is_string($title) ||
        !is_string($dueDate) ||
        !is_string($priority)
    ) {
        jsonResponse([
            'error' => 'Поля завдання мають бути рядками'
        ], 400);
    }

    $title = trim($title);

    // Перевіряємо назву завдання
    if ($title === '' || mb_strlen($title) > 255) {
        jsonResponse([
            'error' => 'Назва має містити від 1 до 255 символів'
        ], 400);
    }

    // Перевіряємо пріоритет
    if (!in_array(
        $priority,
        ['Низький', 'Середній', 'Високий'],
        true
    )) {
        jsonResponse([
            'error' => 'Некоректний пріоритет'
        ], 400);
    }

    // Перевіряємо дату
    if ($dueDate !== '') {
        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $dueDate
        );

        if (!$date || $date->format('Y-m-d') !== $dueDate) {
            jsonResponse([
                'error' => 'Некоректна дата'
            ], 400);
        }
    }

    // Підключаємо базу
    require __DIR__ . '/db.php';
    /** @var PDO $pdo */
    // Додаємо нове завдання
    $stmt = $pdo->prepare(
        'INSERT INTO tasks (title, due_date, priority, done)
         VALUES (:title, :date, :priority, 0)'
    );

    $stmt->execute([
        'title' => $title,
        'date' => $dueDate === '' ? null : $dueDate,
        'priority' => $priority
    ]);

    // Повертаємо створений запис
    jsonResponse([
        'id' => (int)$pdo->lastInsertId(),
        'title' => $title,
        'due_date' => $dueDate === '' ? null : $dueDate,
        'priority' => $priority,
        'done' => 0
    ], 201);

} catch (Throwable $e) {
    apiError($e);
}
