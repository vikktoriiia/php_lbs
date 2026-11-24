<?php
// Встановлення формату відповіді API
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Перетворення PHP-попереджень на винятки
set_error_handler(static function (
    int $severity,
    string $message
): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity);
});

// Формування JSON-відповіді
function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

// Отримання списку завдань
function listTasks(PDO $pdo, ?string $q): void
{
    if ($q === null || trim($q) === '') {
        $stmt = $pdo->query(
            'SELECT id, title, due_date, priority, done
             FROM tasks
             ORDER BY id DESC'
        );
    } else {
        // Пошук завдань за пріоритетом
        $stmt = $pdo->prepare(
            'SELECT id, title, due_date, priority, done
             FROM tasks
             WHERE priority LIKE :q
             ORDER BY id DESC'
        );

        $stmt->execute([
            'q' => '%' . trim($q) . '%'
        ]);
    }

    jsonResponse([
        'success' => true,
        'data' => $stmt->fetchAll()
    ], 200);
}

// Отримання одного завдання за ID
function getTask(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare(
        'SELECT id, title, due_date, priority, done
         FROM tasks
         WHERE id = :id'
    );

    $stmt->execute(['id' => $id]);

    $task = $stmt->fetch();

    // Перевірка наявності запису
    if ($task === false) {
        jsonResponse([
            'success' => false,
            'error' => 'Завдання не знайдено'
        ], 404);
    }

    jsonResponse([
        'success' => true,
        'data' => $task
    ], 200);
}

// Отримання даних POST-запиту
function getRequestData(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    $contentType = strtolower(
        trim(explode(';', $contentType, 2)[0])
    );

    // Отримання даних із JSON-тіла
    if ($contentType === 'application/json') {
        $rawData = file_get_contents('php://input');

        $data = json_decode($rawData);

        // Перевірка коректності JSON
        if (json_last_error() !== JSON_ERROR_NONE ||
            !is_object($data)) {

            jsonResponse([
                'success' => false,
                'error' => 'Некоректне JSON-тіло запиту'
            ], 400);
        }

        return (array)$data;
    }

    // Отримання даних звичайної форми
    return $_POST;
}

// Створення нового завдання
function createTask(): void
{
    $data = getRequestData();

    // Отримання параметрів
    $title = $data['title'] ?? null;
    $dueDate = $data['due_date'] ?? null;
    $priority = $data['priority'] ?? null;

    // Перевірка типу назви завдання
    if (!is_string($title)) {
        jsonResponse([
            'success' => false,
            'error' => 'Поле title є обов’язковим і має бути рядком'
        ], 400);
    }

    $title = trim($title);

    // Перевірка довжини назви
    if ($title === '' || mb_strlen($title) > 255) {
        jsonResponse([
            'success' => false,
            'error' => 'Назва має містити від 1 до 255 символів'
        ], 400);
    }

    // Перевірка пріоритету
    if (!is_string($priority) ||
        !in_array($priority, [
            'Низький',
            'Середній',
            'Високий'
        ], true)) {

        jsonResponse([
            'success' => false,
            'error' => 'Некоректне або відсутнє поле priority'
        ], 400);
    }

    // Перевірка дати виконання
    if ($dueDate !== null) {
        if (!is_string($dueDate)) {
            jsonResponse([
                'success' => false,
                'error' => 'Некоректний тип поля due_date'
            ], 400);
        }

        if ($dueDate === '') {
            $dueDate = null;
        } else {
            // Перевірка формату дати
            if (!preg_match(
                '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D',
                $dueDate
            )) {
                jsonResponse([
                    'success' => false,
                    'error' => 'Дата має бути у форматі YYYY-MM-DD'
                ], 400);
            }

            [$year, $month, $day] = explode('-', $dueDate);

            // Перевірка існування дати
            if ((int)$year < 1000 ||
                !checkdate(
                    (int)$month,
                    (int)$day,
                    (int)$year
                )) {
                jsonResponse([
                    'success' => false,
                    'error' => 'Некоректна дата виконання'
                ], 400);
            }
        }
    }

    // Підключення до бази даних
    require __DIR__ . '/db.php';
    /** @var PDO $pdo */

    // Створення запису через підготовлений запит
    $stmt = $pdo->prepare(
        'INSERT INTO tasks (title, due_date, priority, done)
         VALUES (:title, :due_date, :priority, 0)'
    );

    $stmt->execute([
        'title' => $title,
        'due_date' => $dueDate,
        'priority' => $priority
    ]);

    // Отримання ID нового завдання
    $id = (int)$pdo->lastInsertId();

    // Повернення створеного запису
    jsonResponse([
        'success' => true,
        'data' => [
            'id' => $id,
            'title' => $title,
            'due_date' => $dueDate,
            'priority' => $priority,
            'done' => 0
        ]
    ], 201);
}

// Доменна дія: позначення завдання виконаним
function completeTask(): void
{
    // Отримання даних POST-запиту
    $data = getRequestData();
    $rawId = $data['id'] ?? null;

    // Перевірка типу ідентифікатора
    if (!is_string($rawId) && !is_int($rawId)) {
        jsonResponse([
            'success' => false,
            'error' => 'Некоректний або відсутній параметр id'
        ], 400);
    }

    // Перевірка значення ідентифікатора
    $id = filter_var(
        $rawId,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1,
                'max_range' => 2147483647
            ]
        ]
    );

    if ($id === false) {
        jsonResponse([
            'success' => false,
            'error' => 'Некоректний параметр id'
        ], 400);
    }

    // Підключення до бази даних
    require __DIR__ . '/db.php';
    /** @var PDO $pdo */

    // Перевірка наявності завдання
    $stmt = $pdo->prepare(
        'SELECT id FROM tasks WHERE id = :id'
    );

    $stmt->execute(['id' => $id]);

    if ($stmt->fetch() === false) {
        jsonResponse([
            'success' => false,
            'error' => 'Завдання не знайдено'
        ], 404);
    }

    // Позначення завдання виконаним
    $stmt = $pdo->prepare(
        'UPDATE tasks
         SET done = 1
         WHERE id = :id'
    );

    $stmt->execute(['id' => $id]);

    // Повернення оновленого запису
    getTask($pdo, $id);
}

try {
    // Отримання параметрів запиту
    $method = $_SERVER['REQUEST_METHOD'];
    $resource = $_GET['resource'] ?? null;
    $id = $_GET['id'] ?? null;
    $action = $_GET['action'] ?? null;
    $q = $_GET['q'] ?? null;

    // Перевірка типів параметрів
    foreach ([
                 'resource' => $resource,
                 'id' => $id,
                 'action' => $action,
                 'q' => $q
             ] as $name => $value) {
        if ($value !== null && !is_string($value)) {
            jsonResponse([
                'success' => false,
                'error' => 'Некоректний параметр ' . $name
            ], 400);
        }
    }

    // Перевірка ресурсу
    if ($resource !== 'tasks') {
        jsonResponse([
            'success' => false,
            'error' => 'Невідомий ресурс'
        ], 404);
    }

    // Перевірка HTTP-методу
    if (!in_array($method, ['GET', 'POST'], true)) {
        header('Allow: GET, POST');

        jsonResponse([
            'success' => false,
            'error' => 'Метод не підтримується'
        ], 405);
    }

    // Перевірка значення ID
    if ($id !== null) {
        $id = filter_var(
            $id,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                    'max_range' => 2147483647
                ]
            ]
        );

        if ($id === false) {
            jsonResponse([
                'success' => false,
                'error' => 'Некоректний параметр id'
            ], 400);
        }
    }

    // Маршрутизація запитів
    if ($method === 'GET' &&
        $id === null &&
        $action === null) {

        require __DIR__ . '/db.php';
        /** @var PDO $pdo */

        listTasks($pdo, $q);

    } elseif ($method === 'GET' &&
        $id !== null &&
        $action === null) {

        require __DIR__ . '/db.php';
        /** @var PDO $pdo */

        getTask($pdo, $id);

    } elseif ($method === 'POST' &&
        $action === null &&
        $id === null) {

        createTask();

    } elseif ($method === 'POST' &&
        $action === 'complete' &&
        $id === null) {

        completeTask();

    } else {
        jsonResponse([
            'success' => false,
            'error' => 'Невідомий маршрут або дія'
        ], 404);
    }

} catch (Throwable $e) {
    // Запис системної помилки в журнал
    error_log((string)$e);

    // Повернення помилки у форматі JSON
    jsonResponse([
        'success' => false,
        'error' => 'Внутрішня помилка сервера'
    ], 500);
}
