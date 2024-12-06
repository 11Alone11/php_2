<?php
try {
    //cookieAPI/insertNewSuccesSearch.php
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

    $encryption_key = 'ANDREYPROHOR';
    $encryption_method = 'AES-256-CBC';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_result'])) {
        $userId = $_SESSION['user'];
        $searchResult = htmlspecialchars($_POST['search_result']);
        $cookieName = "search_cache_$userId";
        $encrypted_cache = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : '';
        $cache = json_decode(decrypt($encrypted_cache, $encryption_key), true);
        if (!is_array($cache)) {
            $cache = [];
        }
        //if (!in_array($searchResult, $cache)) {
        array_unshift($cache, $searchResult);
        if (count($cache) > 5) {
            array_pop($cache);
        }
        $encrypted_cache = encrypt(json_encode($cache), $encryption_key);
        setcookie($cookieName, $encrypted_cache, time() + 31536000, '/');
        //}
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }

} catch (Exception $ex) {
    echo json_encode(['error' => $ex->getMessage()]);
}
?>