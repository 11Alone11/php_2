<?php
try{

    // if(isset($_SESSION['user'])):{
    //     session_unset();
    //     session_destroy();
    // }
    // endif;    
session_start();
$_SESSION['sql_error_message'] = 'Ошибка базы данных:';
$_SESSION['server_error_message'] = 'Ошибка сервера';
$_SESSION['server_conn_error'] = false;
include 'db.php';
include 'sessionConf.php';
if($_SESSION['server_conn_error'] === true){
    throw new Exception("Ошибка соединения с сервером");
}
$error_message = '';
$success_message = '';
$input_username = '';



if(!isset($conn)){
    throw new Exception("Ошибка соединения с сервером");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['form_submitted'] = true; // Устанавливаем метку формы
    if (isset($_POST['register'])) {
        $input_username = trim($_POST['name']);
        $input_password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $roleUoD = 2;
        if (isset($_POST['role'])) {
            $role = $_POST['role']; 
            if ($role == '0') {
                $roleUoD = 2;
            } else if ($role == '1') {
                $roleUoD = 0;
            }
        } else {
            $_SESSION['error_message'] = "Пожалуйста, выберите роль.";
        }

        // Валидация ввода
        if (empty($input_username) || empty($input_password) || empty($confirm_password)) {
            $_SESSION['error_message'] = 'Пожалуйста, заполните все поля.';
        } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $input_username)) {
            $_SESSION['error_message'] = 'Имя пользователя должно содержать от 3 до 20 символов, допускаются только буквы, цифры и нижнее подчеркивание.';
        } elseif (strlen($input_password) < 6) {
            $_SESSION['error_message'] = 'Пароль должен быть не менее 6 символов.';
        } elseif ($input_password !== $confirm_password) {
            $_SESSION['error_message'] = 'Пароли не совпадают, попробуйте еще раз.';
        } else {
            // Защита от SQL-инъекций и XSS
            $input_username = $conn->real_escape_string(htmlspecialchars($input_username, ENT_QUOTES, 'UTF-8'));

            // Проверяем, существует ли пользователь
            $query = "SELECT * FROM users WHERE name='$input_username'";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                $_SESSION['error_message'] = 'Пользователь с таким именем уже существует.';
            } else {
                // Хеширование пароля перед сохранением
                $hashed_password = password_hash($input_password, PASSWORD_BCRYPT);

                // Вставка нового пользователя в базу данных
                $insert_query = "INSERT INTO users (name, password, type) VALUES ('$input_username', '$hashed_password', $roleUoD)";

                if ($conn->query($insert_query) === TRUE) {
                    $_SESSION['user'] = $input_username;
                    $_SESSION['user_type'] = $roleUoD;
                    $select_string = "SELECT id, type FROM users WHERE name LIKE '%$input_username%'";
                    $user_info = $conn->query($select_string);
                    $row = $user_info->fetch_assoc(); // Получаем ассоциативный массив
                    $_SESSION['user_id'] = $row['id'];
                    $success_message = "Регистрация успешна. Добро пожаловать, $input_username!";
                    
                    // Перенаправление на index.php после успешной регистрации
                    header("Location: index.php");
                    exit(); // Прекращаем выполнение скрипта после перенаправления
                } else {
                    $_SESSION['error_message'] = 'Ошибка при регистрации. Попробуйте снова.';
                }
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
    unset($_SESSION['form_submitted']);
}

} catch(mysqli_sql_exception $e){
    ?>
    <div class="error-message">
				✖ <?php echo htmlspecialchars($_SESSION['sql_error_message']) . ' ' . htmlspecialchars($e->getMessage()); ?>
    </div>
    <?php
} catch(Exception $e){
    ?>
    <div class="error-message">
                ✖ <?php echo htmlspecialchars($_SESSION['server_error_message']) . ' ' . htmlspecialchars($e->getMessage()); ?>
    </div>
    <?php
}

?>