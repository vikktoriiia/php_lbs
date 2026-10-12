<?php
// Вмикаємо показ помилок
error_reporting(E_ALL);
ini_set('display_errors', '1');
// Підключаємо класи та допоміжні функції
require_once __DIR__ . '/classes/Task.php';
require_once __DIR__ . '/classes/RecurringTask.php';
require_once __DIR__ . '/classes/ProjectBoard.php';
require_once __DIR__ . '/lib/functions.php';
// Створюємо менеджер завдань
$board = new ProjectBoard();
// Створюємо звичайні завдання
$task1 = new Task("Зробити лабораторну", "2026-10-15", "Високий");
$task2 = new Task("Підготувати звіт", "2026-10-18", "Середній");
$task3 = new Task("Прочитати матеріал", "2026-10-10", "Низький");
// Створюємо повторювані завдання
$task4 = new RecurringTask(
    "Повторювати PHP",
    "2026-10-20",
    "Високий",
    "Щотижня"
);
$task5 = new RecurringTask(
    "Перевіряти завдання",
    "2026-10-22",
    "Середній",
    "Щодня"
);
// Додаємо завдання до менеджера
$board->addTask($task1);
$board->addTask($task2);
$board->addTask($task3);
$board->addTask($task4);
$board->addTask($task5);
// Позначаємо друге завдання як виконане
$board->markDone(1);
// Отримуємо список завдань
$tasks = $board->getTasks();
$pending = $board->pendingTasks();

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Трекер завдань</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f5f5f5;
        }
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #d9eaf7;
        }
        .done {
            color: green;
        }
        .pending {
            color: red;
        }
    </style>
</head>
<body>
<h1>Трекер завдань</h1>
<h2>Список усіх завдань</h2>
<table>
    <tr>
        <th>№</th>
        <th>Назва завдання</th>
        <th>Дата</th>
        <th>Пріоритет</th>
        <th>Повторення</th>
        <th>Термін</th>
        <th>Статус</th>
    </tr>
    <?php foreach ($tasks as $index => $task): ?>

        <?php
        // Отримуємо дані завдання
        $date = $task->getDueDate();
        $priority = $task->getPriority();
        // Визначаємо інтервал повторення
        $repeat = "—";
        if ($task instanceof RecurringTask) {
            $repeat = $task->getRepeatInterval();
        }
        ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td>
                <?= htmlspecialchars($task->getInfo()) ?>
            </td>
            <td>
                <?= htmlspecialchars(formatDueDate($date)) ?>
            </td>
            <td>
                <?= htmlspecialchars(priorityLabel($priority)) ?>
            </td>
            <td>
                <?= htmlspecialchars($repeat) ?>
            </td>
            <td>
                <?= isOverdue($date) && !$task->isDone()
                    ? "Прострочено"
                    : "Не прострочено" ?>
            </td>
            <td class="<?= $task->isDone() ? 'done' : 'pending' ?>">
                <?= $task->isDone() ? "Виконано" : "Не виконано" ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<h2>Підсумки</h2>
<p>Усього завдань: <?= count($tasks) ?></p>
<p>Невиконаних завдань: <?= count($pending) ?></p>
<p>Виконаних завдань:
    <?= count($tasks) - count($pending) ?>
</p>
</body>
</html>
