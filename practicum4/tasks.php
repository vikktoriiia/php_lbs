<?php
require_once __DIR__ . '/db.php';

// 1.Отримати всі невиконані завдання
// Сортування за датою виконання
function pendingTasks(): array
{
    global $pdo;

    $sql = "SELECT * FROM tasks
            WHERE done = 0
            ORDER BY due_date ASC";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

// 2.Знайти завдання за пріоритетом
function findByPriority(string $priority): array
{
    global $pdo;

    $sql = "SELECT * FROM tasks
            WHERE priority = :priority";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':priority' => $priority
    ]);

    return $stmt->fetchAll();
}

// 3.Додати нове завдання
function addTask(
    string $title,
    ?string $due_date,
    string $priority
): void {
    global $pdo;

    $title = trim($title);

    if ($title === '') {
        die('Назва завдання не може бути порожньою.');
    }

    if (!in_array($priority, ['Низький', 'Середній', 'Високий'], true)) {
        die('Некоректний пріоритет.');
    }

    $sql = "INSERT INTO tasks
            (title, due_date, priority, done)
            VALUES (:title, :due_date, :priority, 0)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':title' => $title,
        ':due_date' => $due_date !== '' ? $due_date : null,
        ':priority' => $priority
    ]);
}

// 4.Позначити завдання як виконане
function markDone(int $id): void
{
    global $pdo;

    $sql = "UPDATE tasks
            SET done = 1
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id
    ]);
}

// 5.Видалити завдання
function deleteTask(int $id): void
{
    global $pdo;

    $sql = "DELETE FROM tasks
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id
    ]);
}
