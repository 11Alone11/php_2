<?php
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user'] ?? 'guest';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $color = $_POST['color'] ?? '';

        if ($color) {
            $history = json_decode($_COOKIE["colorHistory_$userId"] ?? '[]', true);
            array_unshift($history, $color);
            if (count($history) > 5) {
                array_pop($history);
            }
            setcookie("colorHistory_$userId", json_encode($history), time() + 31536000, '/');
        }
    }
    $colorHistory = json_decode($_COOKIE["colorHistory_$userId"] ?? '[]', true);
    $firstColor = $colorHistory[0] ?? null;
    $response = [
        'history' => $colorHistory,
        'firstColor' => $firstColor,
    ];
    echo json_encode($response);
} catch (Exception $ex) {
    echo json_encode(['error' => $ex->getMessage()]);
}
?>