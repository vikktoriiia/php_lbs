
<?php
require_once __DIR__ . '/db.php';

/** @var PDO $pdo */

header('Content-Type: application/json; charset=utf-8');

try {
    // Отримуємо значення пошуку
    $q = trim($_GET['q'] ?? '');

    // Якщо введено пріоритет, фільтруємо завдання
    if ($q !== '') {
        $stmt = $pdo->prepare(
            'SELECT id, title, due_date, priority, done
             FROM tasks
             WHERE priority LIKE :q
             ORDER BY id DESC'
        );

        $stmt->execute([
            'q' => '%' . $q . '%'
        ]);
    } else {
        // Якщо пошук порожній, отримуємо всі завдання
        $stmt = $pdo->query(
            'SELECT id, title, due_date, priority, done
             FROM tasks
             ORDER BY id DESC'
        );
    }

    // Отримуємо результати з бази даних
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Повертаємо дані у форматі JSON
    echo json_encode($tasks, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Обробка помилки сервера
    http_response_code(500);

    echo json_encode([
        'error' => 'Помилка отримання завдань'
    ], JSON_UNESCAPED_UNICODE);
}
