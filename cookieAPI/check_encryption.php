<?php
session_start();

// Устанавливаем метод шифрования при инициализации сессии
if (!isset($_SESSION['KEY_TYPE'])) {
    $_SESSION['KEY_TYPE'] = 'aes-256-cbc'; // Можно изменить на нужный метод
}

header('Content-Type: application/json');

// Получаем JSON-данные из запроса
$input = json_decode(file_get_contents('php://input'), true);

// Проверяем наличие метода в запросе
if (!$input || !isset($input['method'])) {
    echo json_encode(['allowed' => false, 'error' => 'Invalid request: method not provided']);
    exit;
}

$method = strtolower($input['method']);
$allowed_method = strtolower($_SESSION['KEY_TYPE']);

if ($method === $allowed_method) {
    echo json_encode(['allowed' => true]);
} else {
    echo json_encode([
        'allowed' => false,
        'error' => 'Encryption method not allowed',
        'method' => $method,
        'allowed_method' => $allowed_method
    ]);
}
?>