<?php
require_once __DIR__ . '/Task.php';
// Клас для керування завданнями
class ProjectBoard
{
    // Масив для зберігання завдань
    private array $tasks = [];
    // Додаємо завдання до списку
    public function addTask(Task $task): void
    {
        $this->tasks[] = $task;
    }
    // Повертаємо всі невиконані завдання
    public function pendingTasks(): array
    {
        $result = [];

        foreach ($this->tasks as $task) {
            if (!$task->isDone()) {
                $result[] = $task;
            }
        }
        return $result;
    }
    // Позначаємо завдання як виконане за номером
    public function markDone(int $index): void
    {
        if (isset($this->tasks[$index])) {
            $this->tasks[$index]->markDone();
        }
    }
    // Повертаємо всі завдання
    public function getTasks(): array
    {
        return $this->tasks;
    }
}
