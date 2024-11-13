<?php
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $pdo = new PDO('mysql:host=localhost;dbname=pharmacy2', 'root', '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profilePhoto'])) {
        $file = $_FILES['profilePhoto'];
        $userId = $_SESSION['user_id'];
        
        // Логирование информации о файле
        file_put_contents('debug.txt', print_r($file, true));

        // Проверка ошибок загрузки файла
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка загрузки файла.']);
            exit;
        }

        $fileTmpName = $file['tmp_name'];
        $fileName = $file['name'];
        $fileSize = $file['size'];

        // Проверка размера файла
        if ($fileSize > 10 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'Размер файла не должен превышать 10 МБ.']);
            exit;
        }

        // Проверка типа файла
        $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['status' => 'error', 'message' => 'Допустимые типы файлов: .jpeg, .jpg, .png, .gif.']);
            exit;
        }

        // Проверка корректности изображения
        if (!getimagesize($fileTmpName)) {
            echo json_encode(['status' => 'error', 'message' => 'Изображение повреждено. Замените его на не поврежденный вариант.']);
            exit;
        }

        // Чтение содержимого файла
        $fileData = file_get_contents($fileTmpName);

        // Обновление базы данных с новой ссылкой на изображение
        $stmt = $pdo->prepare("UPDATE users SET profilePhoto = ? WHERE id = ?");
        $stmt->execute([$fileData, $userId]);

        // Проверка успешности обновления
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Не удалось обновить фото профиля.']);
        }
        exit;
    }
} catch (Exception $ex) {
    file_put_contents('debug.txt', $ex->getMessage());
    $a = 'Произошла ошибка на сервере. ' . $ex->getMessage()
    echo json_encode(['status' => 'error', 'message' => $a]);
}