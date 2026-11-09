<?php
require_once __DIR__ . '/db.php';

/** @var PDO $pdo */

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');

        echo json_encode([
            'error' => 'Дозволено лише POST-запити'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Отримуємо ID та новий статус
    $id = filter_var(
        $_POST['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $done = $_POST['done'] ?? null;

    // Перевіряємо дані
    if ($id === false ||
        !in_array($done, ['0', '1'], true)) {

        http_response_code(400);

        echo json_encode([
            'error' => 'Некоректні дані завдання'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Перевіряємо, чи існує завдання
    $stmt = $pdo->prepare(
        'SELECT id FROM tasks WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);

    if (!$stmt->fetch()) {
        http_response_code(404);

        echo json_encode([
            'error' => 'Завдання не знайдено'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Оновлюємо статус у базі даних
    $stmt = $pdo->prepare(
        'UPDATE tasks SET done = :done WHERE id = :id'
    );

    $stmt->execute([
        'done' => (int)$done,
        'id' => $id
    ]);

    // Повертаємо результат у JSON
    echo json_encode([
        'success' => true,
        'id' => (int)$id,
        'done' => (int)$done
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'error' => 'Помилка оновлення завдання'
    ], JSON_UNESCAPED_UNICODE);
}
