<?php
// include "./key.php";
// try {
//     if (session_status() === PHP_SESSION_NONE) {
//         session_start();
//     }
//     $userId = $_SESSION['user'] ?? 'guest';
//     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//         $color = $_POST['color'] ?? '';
//         //    $encryptedValue = openssl_encrypt($value, 'AES-256-CBC', $key, 0, $iv);
//         if ($color) {
//             $history = json_decode(openssl_decrypt($_COOKIE["colorHistory_$userId"], 'AES-256-CBC', key, 0, '') ?? '[]', true); //$_COOKIE["colorHistory_$userId"] 
//             array_unshift($history, $color);
//             if (count($history) > 5) {
//                 array_pop($history);
//             }
//             setcookie("colorHistory_$userId", json_encode(openssl_encrypt($history, 'AES-256-CBC', key, 0, '')), time() + 31536000, '/'); //$_COOKIE["colorHistory_$userId"] 
//         }
//     }
//     $colorHistory = json_decode(openssl_decrypt($_COOKIE["colorHistory_$userId"], 'AES-256-CBC', key, 0, '') ?? '[]', true); //$_COOKIE["colorHistory_$userId"] 
//     $firstColor = $colorHistory[0] ?? null;
//     $response = [
//         'history' => $colorHistory,
//         'firstColor' => $firstColor,
//     ];
//     echo json_encode($response);
// } catch (Exception $ex) {
//     echo json_encode(['error' => $ex->getMessage()]);
// }

include "./key.php";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user'] ?? 'guest';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $color = $_POST['color'] ?? '';
        if ($color) {
            $decryptedHistory = openssl_decrypt($_COOKIE["colorHistory_$userId"] ?? '', 'AES-256-CBC', key, 0, '');
            $history = json_decode($decryptedHistory ?? '[]', true);
            if (!is_array($history)) {
                $history = [];
            }
            array_unshift($history, $color);
            if (count($history) > 5) {
                array_pop($history);
            }
            $encryptedHistory = openssl_encrypt(json_encode($history), 'AES-256-CBC', key, 0, '');
            setcookie("colorHistory_$userId", $encryptedHistory, time() + 31536000, '/');
        }
    }
    $decryptedHistory = openssl_decrypt($_COOKIE["colorHistory_$userId"] ?? '', 'AES-256-CBC', key, 0, '');
    $colorHistory = json_decode($decryptedHistory ?? '[]', true);
    if (!is_array($colorHistory)) {
        $colorHistory = [];
    }
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