<?php
// Параметры подключения к базе данных
$servername = "localhost";
$username = "root"; // Ваше имя пользователя MySQL
$password = ""; // Ваш пароль MySQL
$dbname = "pharmacy2"; // Имя вашей базы данных

try {

    // Создаем новое соединение с базой данных
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Устанавливаем режим обработки ошибок
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    header('Content-Type: application/json');

    $query = "SELECT id, name FROM users"; 
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    // Получаем все строки как ассоциативный массив
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (PDOException $e) {
    // Логируем ошибку и выводим сообщение
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>