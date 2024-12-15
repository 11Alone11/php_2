<?php
include 'sessionConf.php';
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "pharmacy"; 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $_SESSION['server_conn_error'] = false;
} catch(mysqli_sql_exception $e){
    ?>
<div class="error-message">
	✖ <?php echo htmlspecialchars($_SESSION['sql_error_message']) . ' ' . htmlspecialchars($e->getMessage());
                $_SESSION['server_conn_error'] = true;
                ?>
</div>

<?php
    error_log($_SESSION['sql_error_message']);
} catch(Exception $e){
    ?>
<div class="error-message">
	✖ <?php echo htmlspecialchars($_SESSION['server_error_message']) . ' ' . htmlspecialchars($e->getMessage()); 
                $_SESSION['server_conn_error'] = true;
                ?>
</div>
<?php
    error_log($_SESSION['server_conn_error']);
}
?>