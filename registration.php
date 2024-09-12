<?php
	include 'sessionConf.php';
	include 'register.php'; // Подключаем файл обработки регистрации
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>Регистрация</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="login-form">
		<div class="auth">
			<p class="title">Регистрация</p>
			<?php 
			if (isset($_SESSION['user'])):
				{unset($_SESSION['user']);}
			endif;
			if (!isset($_SESSION['user'])):
			 ?>
			<!-- Форма регистрации -->
			<div class="auth__inputs">
				<div>
					<p class="auth__subtitle">Введите имя пользователя</p>
					<input type="text" name="name" placeholder="Логин" class="input auth__input" required />
				</div>
				<div>
					<p class="auth__subtitle">Введите пароль</p>
					<input type="password" name="password" placeholder="Пароль" class="input auth__input" required />
				</div>
				<div>
					<p class="auth__subtitle">Подтвердите пароль</p>
					<input type="password" name="confirm_password" placeholder="Подтвердите пароль" class="input auth__input" required />
				</div>
			</div>

			<?php if (isset($_SESSION['form_submitted']) && isset($_SESSION['error_message'])): ?>
			<div class="auth__message">
				✖ <?php echo htmlspecialchars($_SESSION['error_message']); 
				unset($_SESSION['error_message']) ?>
			</div>
			<?php endif;
		 ?>

			<div class="auth__buttons">
				<button type="submit" name="register" class="button auth__button auth__button_register">
					Зарегистрироваться
				</button><a href="index.php" class="button auth__button auth__button_register">
					На главную
				</a>
			</div>

			<?php elseif ($success_message): ?>
			<!-- Сообщение об успешной регистрации и кнопка "Выйти" -->
			<div class="auth__success">
				✔ <?php	echo htmlspecialchars($success_message); ?>
			</div>
			<div class="auth__buttons">
				<button type="submit" name="logout" class="button auth__button auth__button_logout">
					Выйти
				</button>
			</div>
			<?php endif; ?>
		</div>
	</form>
</body>

</html>


<?php
// Закрываем соединение
	$conn->close();
?>