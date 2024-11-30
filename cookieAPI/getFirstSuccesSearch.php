<?php
// include "./key.php";
// try {
// //cookieAPI/getFirstSuccesSearch.php
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
// header('Content-Type: application/json');
// if (!isset($_SESSION['user'])) {
//     echo json_encode(['first' => null]);
//     exit;
// }
// $userId = $_SESSION['user'];
// $firstElement = null;
// $cookieName = "search_cache_$userId";
// if (isset($_COOKIE[$cookieName])) {
//     $cache = json_decode(base64_decode($_COOKIE[$cookieName]), true);
//     $firstElement = isset($cache[0]) ? $cache[0] : null;
// }
// echo json_encode(['first' => $firstElement]);
// } catch (Exception $ex) {
//     echo json_encode(['error' => $ex->getMessage()]);
// }

include "./key.php";
try {
    // cookieAPI/getFirstSuccesSearch.php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header('Content-Type: application/json');
    if (!isset($_SESSION['user'])) {
        echo json_encode(['first' => null]);
        exit;
    }
    $userId = $_SESSION['user'];
    $firstElement = null;
    $cookieName = "search_cache_$userId";
    if (isset($_COOKIE[$cookieName])) {
        $decryptedCache = openssl_decrypt(base64_decode($_COOKIE[$cookieName]), 'AES-256-CBC', key, 0, '');
        $cache = json_decode($decryptedCache ?? '[]', true);
        $firstElement = isset($cache[0]) ? $cache[0] : null;
    }
    echo json_encode(['first' => $firstElement]);
} catch (Exception $ex) {
    echo json_encode(['error' => $ex->getMessage()]);
}
?>