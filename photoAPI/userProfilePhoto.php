<?php
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $pdo = new PDO('mysql:host=localhost;dbname=pharmacy', 'root', '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profilePhoto'])) {
        $file = $_FILES['profilePhoto'];
        $userId = $_SESSION['user_id'];
        
        file_put_contents('debug.txt', print_r($file, true));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка загрузки файла.']);
            exit;
        }

        $fileTmpName = $file['tmp_name'];
        $fileName = $file['name'];
        $fileSize = $file['size'];

        if ($fileSize > 10 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'Размер должен быть до 10 МБ']);
            exit;
        }

        $allowedExtensions = [ 'jpg', 'gif', 'png', 'jpeg', 'webp' ];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['status' => 'error', 'message' => 'Допустимые типы файлов: .jpg, .gif, .png, .jpeg, .webp']);
            exit;
        }

        if (!getimagesize($fileTmpName)) {
            echo json_encode(['status' => 'error', 'message' => 'Изображение повреждено. Замените его на не поврежденный вариант.']);
            exit;
        }

        $fileData = file_get_contents($fileTmpName);

        $stmt = $pdo->prepare("UPDATE users SET profilePhoto = ? WHERE id = ?");
        $stmt->execute([$fileData, $userId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Не удалось обновить фото профиля.']);
        }
        exit;
    }
} catch (Exception $ex) {
    file_put_contents('debug.txt', $ex->getMessage());
    $a = 'Произошла ошибка на сервере. ' . $ex->getMessage();
    echo json_encode(['status' => 'error', 'message' => $a]);
}