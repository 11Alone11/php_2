<?php
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header('Content-Type: application/json');
    include "../db.php";
    $uploadDir = 'images/';

    

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null;
        if (isset($_FILES['medicinePhoto']) && $_FILES['medicinePhoto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['medicinePhoto'];
            $fileTmpName = $file['tmp_name'];
            $fileName = $file['name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            if (!is_dir("../" . $uploadDir)) {
                echo json_encode(['status' => 'error', 'message' => 'Папка для загрузки изображений не доступна.']);
                exit;
            }

            if ($fileSize < 1 * 1024 * 1024 || $fileSize > 2 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'Размер фотки должен быть от 1 до 2 МБ.']);
                exit;
            }

            $allowedExtensions = ['jpg', 'gif'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($fileExtension, $allowedExtensions)) {
                echo json_encode(['status' => 'error', 'message' => 'Допустимые типы файлов: .jpg, .gif.']);
                exit;
            }
            if (!getimagesize($fileTmpName)) {
                echo json_encode(['status' => 'error', 'message' => 'Изображение повреждено.']);
                exit;
            }

            $uniqueFileName = uniqid() . '.' . $fileExtension;
            $uploadDir = 'images/';
            $uploadFile = $uploadDir . $uniqueFileName;

            if (move_uploaded_file($fileTmpName, "../" . $uploadFile)) {
                $stmt = $conn->prepare("UPDATE drugs SET medicinePhoto = ? WHERE id = ?");
                $stmt->bind_param("si", $uploadFile, $id);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'fileName' => $uniqueFileName, 'message' => 'Изображение обновлено успешно.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Не удалось обновить изображение в базе данных.']);
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Ошибка при загрузке файла.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Пожалуйста, выберите изображение.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Неверный метод запроса.']);
    }

    $conn->close();
} catch (Exception $ex) {
    file_put_contents('debug.txt', $ex->getMessage());
}

?>