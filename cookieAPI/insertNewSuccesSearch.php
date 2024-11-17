<?php
try {
//cookieAPI/insertNewSuccesSearch.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
// if (!isset($_SESSION['user'])) {
//     echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
//     exit;
// }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_result'])) {
    $userId = $_SESSION['user'];
    $searchResult = htmlspecialchars($_POST['search_result']);
    $cookieName = "search_cache_$userId";
    $cache = isset($_COOKIE[$cookieName]) ? json_decode(base64_decode($_COOKIE[$cookieName]), true) : [];
    //if (!in_array($searchResult, $cache)) {
    array_unshift($cache, $searchResult);
    if (count($cache) > 5) {
        array_pop($cache);
    }
    setcookie($cookieName, base64_encode(json_encode($cache)), time() + 31536000, '/');
    //}
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}

} catch (Exception $ex) {
    echo json_encode(['error' => $ex->getMessage()]);
}
?>