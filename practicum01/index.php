<?php
//перевірка
error_reporting(E_ALL);  //всі типи помилок
ini_set('display_errors', 1);  //помилки на сторінці в браузері
//оголошення масиву даних мого варіанту
$tasks = [
    ["title" => "Завдання 1: Лабораторна робота 1 з предмету 'Архітектура комп'ютерів'",
    "dueDate" => "2026-09-12",
        "priority" => "Високий",
        "done" => false],

    ["title" => "Завдання 2: Прочитати книгу 80 сторінок",
        "dueDate" => "2026-09-13",
        "priority" => "Низький",
        "done" => true],

    ["title" => "Завдання 3: Лабораторна робота 1 з предмету 'Php'",
        "dueDate" => "2026-09-14",
        "priority" => "Високий",
        "done" => true],

    ["title" => "Завдання 4: Лабораторна робота 1 з предмету 'Front-end'",
        "dueDate" => "2026-09-15",
        "priority" => "Середній",
        "done" => true],

    ["title" => "Завдання 5: Лабораторна робота 1 з предмету 'Серверні елементи'",
        "dueDate" => "2026-09-16",
        "priority" => "Середній",
        "done" => false],

    ["title" => "Завдання 6: Зробити конспект лекції",
        "dueDate" => "2026-09-17",
        "priority" => "Низький",
        "done" => false]
    ];
function formatTask(array $task): string  //функція форматування
{
    return $task["title"] . " (Пріоритет: " . $task["priority"] . ")";
}

//Статуси виконано/в процесі/прострочено
function getTaskStatus(array $task): string
{
    if ($task["done"]) {
        return "Завдання виконано";}
    elseif ($task["dueDate"]< date("Y-m-d")) {
        return "Завдання прострочено";}
    else{
        return "Завдання в процесі";
    }
}
$doneCount =0;      //лічильник скільки виконано
$notDoneCount =0;  //лічильник скільки не виконано
foreach ($tasks as $task) {
    if ($task["done"]){
        $doneCount++;
    }
    else {
        $notDoneCount++;
    }
}
?>

<!--Робимо HTML-->

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Трекер завдань</title>
    <link rel="stylesheet" href="style.css">   <!--пов'язуємо з файлом style.css-->
</head>
<body>
<h1>Трекер завдань</h1>
<div class="tasks-container">
<?php
foreach ($tasks as $task) {
    echo "<div class='task'>";
    echo "<p>" . formatTask($task). "</p>";
    echo "<p>Дата: " . $task["dueDate"] . "</p>";
    echo "<p>" . getTaskStatus($task). "</p>";
    echo "</div>";
}
?>
</div>
<div class="summary">
<h2>Підсумок виконання завдань</h2>
<p>Виконаних завдань:   <?= $doneCount ?></p>
<p>Невиконаних завдань: <?= $notDoneCount ?></p>
</div>
</body>
</html>
