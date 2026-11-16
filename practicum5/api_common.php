<?php
// Спільні налаштування JSON API
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');

// Перетворюємо PHP-попередження на винятки
set_error_handler(static function (
    int $severity,
    string $message
): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity);
});

// Повернення відповіді у форматі JSON
function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

// Обробка внутрішніх помилок сервера
function apiError(Throwable $exception): never
{
    error_log((string)$exception);

    jsonResponse([
        'error' => 'Внутрішня помилка сервера'
    ], 500);
}
