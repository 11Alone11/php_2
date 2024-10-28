<?php
try {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include "../db.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    $userId = $input['userId'];
    $settings = $input['settings'];
    $checkQuery = "SELECT COUNT(*) FROM medicineweights WHERE user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $checkStmt->bind_result($userExists);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($userExists > 0) {
        $query = "UPDATE medicineweights SET 
                    C_quantity_in_orders = ?,
                    C_frequency_of_use = ?,
                    C_availability_in_stock = ?,
                    C_comparative_price = ?,
                    C_demand_for_medicine = ?,
                    C_manufacturer = ?,
                    last_updated = current_timestamp()
                WHERE user_id = ?";

        $stmt = $conn->prepare($query);
        $params = array_merge($settings, [$userId]); 
        $types = str_repeat("d", count($settings)) . "i"; 
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            $errorInfo = $stmt->error;
            echo json_encode(['error' => 'Database error: ' . $errorInfo]);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Settings updated successfully.']);
    } else {
        $insertQuery = "INSERT INTO medicineweights (user_id, C_quantity_in_orders, C_frequency_of_use, 
                        C_availability_in_stock, C_comparative_price, C_demand_for_medicine, C_manufacturer, last_updated)
                        VALUES (?, ?, ?, ?, ?, ?, ?, current_timestamp())";

        $stmt = $conn->prepare($insertQuery);
        $params = array_merge([$userId], $settings); 
        $types = "i" . str_repeat("d", count($settings)); 
        $stmt->bind_param($types, ...$params); 

        if (!$stmt->execute()) {
            $errorInfo = $stmt->error;
            echo json_encode(['error' => 'Database error: ' . $errorInfo]);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'New settings inserted successfully.']);
    }
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>