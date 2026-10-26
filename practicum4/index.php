<?php
require_once __DIR__ . '/tasks.php';

// Визначаємо, які завдання відображати
$filter = $_GET['filter'] ?? 'all';
$priority = $_GET['priority'] ?? '';

if ($filter === 'pending') {
    // Лише невиконані завдання
    $tasks = pendingTasks();
} elseif (
        $filter === 'priority' &&
        in_array($priority, ['Низький', 'Середній', 'Високий'], true)
) {
    // Пошук за пріоритетом
    $tasks = findByPriority($priority);
} else {
    // Усі завдання
    $stmt = $pdo->query("SELECT * FROM tasks");
    $tasks = $stmt->fetchAll();
}
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

<hr>

<h2>Фільтрація завдань</h2>

<a href="index.php">Усі завдання</a>

<br><br>

<a href="index.php?filter=pending">
    Лише невиконані (за датою)
</a>

<br><br>

<form action="index.php" method="GET">
    <input type="hidden" name="filter" value="priority">

    <label>Знайти за пріоритетом:</label>

    <select name="priority">
        <?php foreach (['Низький', 'Середній', 'Високий'] as $p): ?>
            <option value="<?= htmlspecialchars($p) ?>"
                    <?= $priority === $p ? 'selected' : '' ?>>
                <?= htmlspecialchars($p) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Знайти</button>
</form>

<hr>

<h2>Завдання</h2>

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

            <td>
                <?= htmlspecialchars($task['title']) ?>
            </td>

            <td>
                <?= htmlspecialchars($task['due_date'] ?? '') ?>
            </td>

            <td>
                <?= htmlspecialchars($task['priority']) ?>
            </td>

            <td>
                <?= $task['done'] ? 'Виконано' : 'Не виконано' ?>
            </td>

            <td>
                <a href="edit.php?id=<?= (int)$task['id'] ?>">
                    Редагувати
                </a>

                <?php if (!$task['done']): ?>
                    <form action="mark_done.php"
                          method="POST"
                          style="display:inline;">

                        <input type="hidden"
                               name="id"
                               value="<?= (int)$task['id'] ?>">

                        <button type="submit">
                            Позначити виконаним
                        </button>
                    </form>
                <?php endif; ?>

                <form action="delete.php"
                      method="POST"
                      style="display:inline;"
                      onsubmit="return confirm('Ви впевнені, що хочете видалити завдання?');">

                    <input type="hidden"
                           name="id"
                           value="<?= (int)$task['id'] ?>">

                    <button type="submit">Видалити</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php if (empty($tasks)): ?>
    <p>Завдань за вибраними умовами не знайдено.</p>
<?php endif; ?>

</body>
</html>
