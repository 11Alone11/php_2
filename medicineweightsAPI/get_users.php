<?php
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "pharmacy";

try {

    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    header('Content-Type: application/json');

    $query = "SELECT id, name FROM users"; 
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>