<?php
// cookieAPI/getAllSuccesSearch.php
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header('Content-Type: application/json');

    if (!isset($_SESSION['user'])) {
        echo json_encode(['cache' => []]);
        exit;
    }
    $userId = $_SESSION['user'];
    $allElements = [];
    $cookieName = "search_cache_$userId";
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

    if (isset($_COOKIE[$cookieName])) {
        $encrypted_cache = $_COOKIE[$cookieName];
        $decrypted_cache = decrypt($encrypted_cache, $encryption_key, $key_type);
        $allElements = json_decode($decrypted_cache, true);
    }
    echo json_encode(['cache' => $allElements ?? []]);
} catch (Exception $ex) {
    echo json_encode(['error' => $ex->getMessage()]);
}
?>