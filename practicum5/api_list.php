<?php
require_once __DIR__ . '/api_common.php';

try {
    // Отримуємо параметр пошуку
    $q = $_GET['q'] ?? '';

    // Перевіряємо тип параметра
    if (!is_string($q)) {
        jsonResponse([
            'error' => 'Параметр q має бути рядком'
        ], 400);
    }

    $q = trim($q);

    // Підключення БД всередині try
    require __DIR__ . '/db.php';
    /** @var PDO $pdo */
    if ($q === '') {
        // Отримуємо всі завдання
        $stmt = $pdo->query(
            'SELECT id, title, due_date, priority, done
             FROM tasks
             ORDER BY id DESC'
        );
    } else {
        // Пошук завдань за пріоритетом
        $stmt = $pdo->prepare(
            'SELECT id, title, due_date, priority, done
             FROM tasks
             WHERE priority LIKE :q
             ORDER BY id DESC'
        );

        $stmt->execute([
            'q' => '%' . $q . '%'
        ]);
    }

    // Повертаємо JSON-масив
    jsonResponse($stmt->fetchAll());

} catch (Throwable $e) {
    apiError($e);
}
