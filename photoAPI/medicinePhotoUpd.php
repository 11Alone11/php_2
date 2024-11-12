<?php
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header('Content-Type: application/json');
    include "../db.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null;

        if (isset($_FILES['medicinePhoto']) && $_FILES['medicinePhoto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['medicinePhoto'];
            $fileTmpName = $file['tmp_name'];
            $fileName = $file['name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];

            // Check file size
            if ($fileSize > 10 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'Размер файла не должен превышать 10 МБ.']);
                exit;
            }

            // Validate file type
            $allowedExtensions = ['jpeg', 'jpg', 'png'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($fileExtension, $allowedExtensions)) {
                echo json_encode(['status' => 'error', 'message' => 'Допустимые типы файлов: .jpeg, .jpg, .png.']);
                exit;
            }
            if (!getimagesize($fileTmpName)) {
                echo json_encode(['status' => 'error', 'message' => 'Изображение повреждено. Пожалуйста, замените его на не поврежденный вариант.']);
                exit;
            }
            // Generate unique filename
            $uniqueFileName = uniqid() . '.' . $fileExtension;
            $uploadDir = 'images/';
            $uploadFile = $uploadDir . $uniqueFileName;

            // Move uploaded file
            if (move_uploaded_file($fileTmpName, "../".$uploadFile)) {
                // Update database with new image link
                $stmt = $conn->prepare("UPDATE drugs SET medicinePhoto = ? WHERE id = ?");
                $stmt->bind_param("si", $uploadFile, $id);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Изображение обновлено успешно.']);
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