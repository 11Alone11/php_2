<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    if (isset($_SESSION['error_message']) && $_SESSION['error_message'] == "Неверный логин или пароль, попробуйте еще раз.") {
        $_SESSION['error_message'] = '';
    }
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] != 1) {
        header("Location: index.php");
        exit(); 
    }

} catch (Exception $e) {
    ?>
    <div class="error-message">
        ✖ <?php echo htmlspecialchars($_SESSION['server_error_message'] ?? ''); ?> <?php echo htmlspecialchars($e->getMessage()); ?>
    </div>
    <?php
    exit();
}
?>