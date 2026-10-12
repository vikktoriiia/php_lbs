<?php
// Базовий клас для завдань
class Task
{
    protected string $title;
    protected string $dueDate;
    protected string $priority;
    protected bool $done;

    // Конструктор створює нове завдання
    public function __construct(
        string $title,
        string $dueDate,
        string $priority,
        bool $done = false
    ) {
        $this->title = $title;
        $this->dueDate = $dueDate;
        $this->priority = $priority;
        $this->done = $done;
    }

    // Повертає інформацію про завдання
    public function getInfo(): string
    {
        return $this->title;
    }

    // Повертає дату виконання
    public function getDueDate(): string
    {
        return $this->dueDate;
    }

    // Повертає пріоритет
    public function getPriority(): string
    {
        return $this->priority;
    }

    // Позначає завдання як виконане
    public function markDone(): void
    {
        $this->done = true;
    }

    // Перевіряє статус завдання
    public function isDone(): bool
    {
        return $this->done;
    }
}
