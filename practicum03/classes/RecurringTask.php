<?php
require_once __DIR__ . '/Task.php';
// Клас повторюваного завдання
class RecurringTask extends Task
{
    protected string $repeatInterval;

    // Конструктор повторюваного завдання
    public function __construct(
        string $title,
        string $dueDate,
        string $priority,
        string $repeatInterval,
        bool $done = false
    ) {
        // Викликаємо конструктор базового класу
        parent::__construct($title, $dueDate, $priority, $done);

        $this->repeatInterval = $repeatInterval;
    }

    // Повертає розширену інформацію про завдання
    public function getInfo(): string
    {
        return parent::getInfo()
            . " (Повторюване завдання)";
    }

    // Повертає інтервал повторення
    public function getRepeatInterval(): string
    {
        return $this->repeatInterval;
    }
}
