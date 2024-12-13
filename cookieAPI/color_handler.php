<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$encryption_key = 'ANDREYPROHOR';
$key_type = $_SESSION['KEY_TYPE'] ?? 'aes-128-cbc';

function encrypt($plaintext, $password, $method) {
    $key = hash('sha256', $password, true);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $ciphertext);
}

function decrypt($ciphertext, $password, $method) {
    $key = hash('sha256', $password, true);
    $ciphertext = base64_decode($ciphertext);
    $iv_length = openssl_cipher_iv_length($method);
    $iv = substr($ciphertext, 0, $iv_length);
    $ciphertext_raw = substr($ciphertext, $iv_length);
    return openssl_decrypt($ciphertext_raw, $method, $key, OPENSSL_RAW_DATA, $iv);
}

try {
    $userId = $_SESSION['user'] ?? 'guest';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $color = $_POST['color'] ?? '';

        if ($color) {
            // Expecting color in format "method|encryptedData"
            list($method, $encrypted_color) = explode('|', $color, 2) + [null, null];
            if ($method && $encrypted_color) {
                $encrypted_history = $_COOKIE["colorHistory_$userId"] ?? '';
                $decrypted_history = decrypt($encrypted_history, $encryption_key, $key_type);
                $history = json_decode($decrypted_history, true) ?? [];
                array_unshift($history, $color);
                if (count($history) > 5) {
                    array_pop($history);
                }
                $encrypted_history = encrypt(json_encode($history), $encryption_key, $key_type);
                setcookie("colorHistory_$userId", $encrypted_history, time() + 31536000, '/');
            }
        }
    }
    
    $encrypted_history = $_COOKIE["colorHistory_$userId"] ?? '';
    $decrypted_history = decrypt($encrypted_history, $encryption_key, $key_type);
    $colorHistory = json_decode($decrypted_history, true) ?? [];
    $firstColor = $colorHistory[0] ?? null;
    
    $response = [
        'history' => $colorHistory,
        'firstColor' => $firstColor,
    ];
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>