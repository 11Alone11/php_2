<?php
try {
    //cookieAPI/getFirstSuccesSearch.php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header('Content-Type: application/json');

    function encrypt($plaintext, $password) {
        $method = 'AES-256-CBC';
        $key = hash('sha256', $password, true);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $ciphertext);
    }

    function decrypt($ciphertext, $password) {
        $method = 'AES-256-CBC';
        $key = hash('sha256', $password, true);
        $ciphertext = base64_decode($ciphertext);
        $iv_length = openssl_cipher_iv_length($method);
        $iv = substr($ciphertext, 0, $iv_length);
        $ciphertext_raw = substr($ciphertext, $iv_length);
        return openssl_decrypt($ciphertext_raw, $method, $key, OPENSSL_RAW_DATA, $iv);
    }

    if (!isset($_SESSION['user'])) {
        echo json_encode(['first' => null]);
        exit;
    }
    $userId = $_SESSION['user'];
    $firstElement = null;
    $cookieName = "search_cache_$userId";
    $password = 'ANDREYPROHOR';
    if (isset($_COOKIE[$cookieName])) {
        $encrypted_cache = $_COOKIE[$cookieName];
        $cache = json_decode(decrypt($encrypted_cache, $password), true);
        $firstElement = isset($cache[0]) ? $cache[0] : null;
    } else {
        $cache = array();
        $encrypted_cache = encrypt(json_encode($cache), $password);
        setcookie($cookieName, $encrypted_cache, time() + (86400 * 30), "/");
    }
    echo json_encode(['first' => $firstElement]);
} catch (Exception $ex) {
    echo json_encode(['error' => $ex->getMessage()]);
}
?>