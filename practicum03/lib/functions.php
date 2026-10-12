<?php
// Форматуємо дату виконання завдання
function formatDueDate(string $date): string
{
    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return "Невідома дата";
    }

    return date("d.m.Y", $timestamp);
}
// Визначаємо назву пріоритету
function priorityLabel(string $priority): string
{
    if ($priority === "Високий") {
        return "Високий пріоритет";
    } elseif ($priority === "Середній") {
        return "Середній пріоритет";
    } else {
        return "Низький пріоритет";
    }
}
// Перевіряємо, чи минув термін виконання
function isOverdue(string $date): bool
{
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return false;
    }
    return $timestamp < strtotime(date("Y-m-d"));
}
