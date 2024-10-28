<?php
try {
    include "../db.php"; // Используйте полный путь к db.php
    header('Content-Type: application/json');

    if (!isset($_GET['user_id'])) {
        echo json_encode(['error' => 'user_id is required']);
        exit;
    }

    $userId = $_GET['user_id'];

    // Подготовка запроса к базе данных
    $query = "SELECT 
                users.name,
                users.id,
                users.type,
                medicineweights.last_updated,
                medicineweights.C_quantity_in_orders,
                medicineweights.C_frequency_of_use,
                medicineweights.C_availability_in_stock,
                medicineweights.C_comparative_price,
                medicineweights.C_demand_for_medicine,
                medicineweights.C_manufacturer
            FROM
                users
            LEFT JOIN
                medicineweights ON medicineweights.user_id = users.id
            WHERE 
                users.id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId); 
    $stmt->execute();
    $result = $stmt->get_result(); 
    $userData = $result->fetch_assoc(); 

    if ($userData) {
        echo json_encode([
            'old_C_quantity_in_orders' => $userData['C_quantity_in_orders'],
            'old_C_frequency_of_use' => $userData['C_frequency_of_use'],
            'old_C_availability_in_stock' => $userData['C_availability_in_stock'],
            'old_C_comparative_price' => $userData['C_comparative_price'],
            'old_C_demand_for_medicine' => $userData['C_demand_for_medicine'],
            'old_C_manufacturer' => $userData['C_manufacturer'],
            'C_quantity_in_orders' => $userData['C_quantity_in_orders'],
            'C_frequency_of_use' => $userData['C_frequency_of_use'],
            'C_availability_in_stock' => $userData['C_availability_in_stock'],
            'C_comparative_price' => $userData['C_comparative_price'],
            'C_demand_for_medicine' => $userData['C_demand_for_medicine'],
            'C_manufacturer' => $userData['C_manufacturer'],
            'lastUpdated' => $userData['last_updated'],
            'userID' => $userData['id'],
            'nameUser' => $userData['name'],
            'userType' => $userData['type'],
        ]);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}