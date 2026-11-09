<?php
require_once __DIR__ . '/db.php';

// Отримуємо ID завдання
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die('Некоректний ID завдання.');
}

// Знаходимо завдання за ID
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = :id");
$stmt->execute([':id' => $id]);
$task = $stmt->fetch();

if (!$task) {
    die('Завдання не знайдено.');
}

// Обробляємо форму редагування
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $done = isset($_POST['done']) ? 1 : 0;

    if ($title === '') {
        die('Назва завдання не може бути порожньою.');
    }

    if (!in_array($priority, ['Низький', 'Середній', 'Високий'], true)) {
        die('Некоректний пріоритет.');
    }

    // Оновлюємо запис через підготовлений запит
    $sql = "UPDATE tasks
            SET title = :title,
                due_date = :due_date,
                priority = :priority,
                done = :done
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':due_date' => $due_date !== '' ? $due_date : null,
        ':priority' => $priority,
        ':done' => $done,
        ':id' => $id
    ]);

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагування завдання</title>
</head>
<body>

<h1>Редагування завдання</h1>

<form method="POST">
    <label>Назва завдання:</label>
    <input type="text" name="title"
           value="<?= htmlspecialchars($task['title']) ?>"
           required>

    <br><br>

    <label>Термін виконання:</label>
    <input type="date" name="due_date"
           value="<?= htmlspecialchars($task['due_date'] ?? '') ?>">

    <br><br>

    <label>Пріоритет:</label>
    <select name="priority">
        <?php foreach (['Низький', 'Середній', 'Високий'] as $priority): ?>
            <option value="<?= $priority ?>"
                <?= $task['priority'] === $priority ? 'selected' : '' ?>>
                <?= $priority ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>

    <label>
        <input type="checkbox" name="done" value="1"
            <?= $task['done'] ? 'checked' : '' ?>>
        Виконано
    </label>

    <br><br>

    <button type="submit">Зберегти зміни</button>
</form>

<br>

<a href="index.php">Повернутися до списку</a>

</body>
</html>
