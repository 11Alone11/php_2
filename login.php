<?php
// if(isset($_SESSION['user'])):{
//     session_unset();
//     session_destroy();
// }
// endif;
session_start();
include 'db.php';
include 'db_executor.php';

$_SESSION['sql_error_message'] = 'Ошибка базы данных:';
$_SESSION['server_error_message'] = 'Ошибка сервера';
$_SESSION['server_conn_error'] = false;

$error_message = '';
$success_message = '';
$input_username = '';
try{
    $dbExecutor = new ActionLogger();
// Проверка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($_SESSION['server_conn_error'] === true){
        throw new Exception("Ошибка соединения с сервером");
    }
    
    if(!isset($conn)){
        throw new Exception("Ошибка соединения с сервером");
    }
    $_SESSION['form_submitted'] = true; // Устанавливаем метку формы

    if (isset($_POST['login'])) {
        $input_username = trim($_POST['name']);
        $input_password = trim($_POST['password']);

        // Проверка пустых полей
        if (empty($input_username) || empty($input_password)) {
            $_SESSION['error_message'] = 'Пожалуйста, заполните все поля.';
        } else {
            // Защита от SQL-инъекций
            $input_username = $conn->real_escape_string($input_username);

            // Запрос на проверку существования пользователя
            $query = "SELECT * FROM users WHERE name='$input_username'";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Проверка пароля
                if (password_verify($input_password, $user['password'])) {
                    $_SESSION['user'] = $input_username;
                    $success_message = "Вход успешен. Добро пожаловать, $input_username!";
                    $select_string = "SELECT id, type FROM users WHERE name LIKE '%$input_username%'";
                    $user_info = $conn->query($select_string);
                    if ($user_info && $user_info->num_rows > 0) {
                        $row = $user_info->fetch_assoc(); // Получаем ассоциативный массив
                        $_SESSION['user_id'] = $row['id']; // Записываем ID в сессию
                        $_SESSION['user_type'] = $row['type'];// Записываем тип пользователя в сессию   
                    } else {
                        // Обработка случая, когда пользователь не найден
                        $_SESSION['user_id'] = null; // Или любое другое значение по умолчанию
                        $_SESSION['user_type'] = 2; // По умолчанию - покупатель
                    }
                    $user_type = isset($_SESSION['user_id']) 
                    ? ($_SESSION['user_type'] == 1 ? 'админ' : ($_SESSION['user_type'] == 0 ? 'поставщик' : ($_SESSION['user_type'] == 2 ? 'покупатель' : 'неизвестный тип')))
                    : 'неизвестный тип';
                    $Actstr = "Пользователь $input_username типа '$user_type' зашел в систему.";
                    $dbExecutor->insertAction($_SESSION['user_id'], $Actstr);
                    header("Location: table.php");
                    unset( $_SESSION['error_message']);
                    exit(); // Прекращаем выполнение скрипта после перенаправления
                } else {
                    $_SESSION['error_message'] = 'Неверный логин или пароль, попробуйте еще раз.';
                }
            } else {
                $_SESSION['error_message'] = 'Неверный логин или пароль, попробуйте еще раз.';
            }
        }
    } elseif (isset($_POST['logout'])) {
        // Обработка выхода пользователя
        session_unset();
        session_destroy();
        $success_message = '';
        $input_username = '';
    }
} else {
    // Если страница загружена без отправки формы, сбрасываем метку формы
    unset($_SESSION['form_submitted']);
}
} catch(mysqli_sql_exception $e){
    ?>
    <div class="error-message">
				✖ <?php echo htmlspecialchars($_SESSION['sql_error_message']) . ' ' . htmlspecialchars($e->getMessage()); ?>
    </div>
    <?php
}catch(Exception $e){
    ?>
    <div class="error-message">
                ✖ <?php echo htmlspecialchars($_SESSION['server_error_message']) . ' ' . htmlspecialchars($e->getMessage()); ?>
    </div>
    <?php
}
?>