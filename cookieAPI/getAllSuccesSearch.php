<?php
try {
//cookieAPI/getAllSuccesSearch.php
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
if (isset($_COOKIE[$cookieName])) {
    $allElements = json_decode(base64_decode($_COOKIE[$cookieName]), true);
}
echo json_encode(['cache' => $allElements]);
} catch (Exception $ex) {
    echo json_encode(['error' => $ex->getMessage()]);
}
?>