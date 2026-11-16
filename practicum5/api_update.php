<?php
require_once __DIR__ . '/api_common.php';

try {
    // Дозволяємо лише POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');

        jsonResponse([
            'error' => 'Дозволено лише POST'
        ], 405);
    }

    // Отримуємо параметри
    $rawId = $_POST['id'] ?? null;
    $done = $_POST['done'] ?? null;

    // Перевіряємо типи і значення
    if (
        !is_string($rawId) ||
        !ctype_digit($rawId) ||
        !is_string($done) ||
        !in_array($done, ['0', '1'], true)
    ) {
        jsonResponse([
            'error' => 'Некоректні дані завдання'
        ], 400);
    }

    // Перевіряємо ID
    $id = filter_var(
        $rawId,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        jsonResponse([
            'error' => 'Некоректний ID'
        ], 400);
    }

    // Підключаємо базу
    require __DIR__ . '/db.php';
    /** @var PDO $pdo */
    // Перевіряємо, чи існує завдання
    $stmt = $pdo->prepare(
        'SELECT id FROM tasks WHERE id = :id'
    );

    $stmt->execute(['id' => $id]);

    if (!$stmt->fetch()) {
        jsonResponse([
            'error' => 'Завдання не знайдено'
        ], 404);
    }

    // Оновлюємо статус
    $stmt = $pdo->prepare(
        'UPDATE tasks SET done = :done WHERE id = :id'
    );

    $stmt->execute([
        'done' => (int)$done,
        'id' => $id
    ]);

    // Повертаємо JSON
    jsonResponse([
        'success' => true,
        'id' => $id,
        'done' => (int)$done
    ]);

} catch (Throwable $e) {
    apiError($e);
}
