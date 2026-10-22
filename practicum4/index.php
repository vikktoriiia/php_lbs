<?php
require_once __DIR__ . '/db.php';

// Отримання всіх завдань із бази даних
$stmt = $pdo->query("SELECT * FROM tasks");
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Трекер завдань</title>
</head>
<body>

<h1>Список завдань</h1>

<h2>Додати нове завдання</h2>

<form action="add.php" method="POST">
    <label>Назва завдання:</label>
    <input type="text" name="title" required>

    <br><br>

    <label>Термін виконання:</label>
    <input type="date" name="due_date">

    <br><br>

    <label>Пріоритет:</label>
    <select name="priority">
        <option value="Низький">Низький</option>
        <option value="Середній">Середній</option>
        <option value="Високий">Високий</option>
    </select>

    <br><br>

    <button type="submit">Додати завдання</button>
</form>

<br>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Назва завдання</th>
        <th>Термін виконання</th>
        <th>Пріоритет</th>
        <th>Статус</th>
        <th>Дії</th>
    </tr>

    <?php foreach ($tasks as $task): ?>
        <tr>
            <td><?= (int)$task['id'] ?></td>
            <td><?= htmlspecialchars($task['title']) ?></td>
            <td><?= htmlspecialchars($task['due_date'] ?? '') ?></td>
            <td><?= htmlspecialchars($task['priority'] ?? '') ?></td>
            <td>
                <?= $task['done'] ? 'Виконано' : 'Не виконано' ?>
            </td>

            <td>
                <a href="edit.php?id=<?= (int)$task['id'] ?>">
                    Редагувати
                </a>

                <form action="delete.php" method="POST"
                      style="display:inline;"
                      onsubmit="return confirm('Ви впевнені, що хочете видалити завдання?');">
                    <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
                    <button type="submit">Видалити</button>
                </form>
            </td>

        </tr>
    <?php endforeach; ?>

</table>

</body>
</html>
