<?php
include 'login.php';
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>Login</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>" class="login-form">
		<div class="auth">
			<p class="title">Аутенфикация</p>
			<?php if (!isset($_SESSION['user'])):?>
			<!-- Форма входа -->
			<div class="auth__inputs">
				<div>
					<p class="auth__subtitle">Введите пользователя</p>
					<input type="text" name="name" placeholder="Логин" class="input auth__input" required />
				</div>
				<div>
					<p class="auth__subtitle">Введите пароль</p>
					<input type="password" name="password" placeholder="Пароль" class="input auth__input" required />
				</div>
			</div>

			<?php if (isset($_SESSION['form_submitted']) && isset($_SESSION['error_message'])):			?>
			<div class="auth__message">
				✖ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
			</div>
			<?php endif; ?>

			<div class="auth__buttons">
				<button type="submit" name="login" class="button auth__button auth__button_login">
					Войти
				</button>
				<a href="registration.php" class="button auth__button auth__button_register">
					Зарегистрироваться
				</a>
			</div>

			<?php else: ?>
			<!-- Кнопка выхода -->
			<div class="auth__message">
				✔ Добро пожаловать, <?php echo htmlspecialchars($_SESSION['user']); ?>!
			</div>
			<div class="auth__buttons">
				<button type="submit" name="logout" class="button auth__button auth__button_logout">
					Выйти
				</button>
				<a href="table.php" class="button">
					К лекарствам
				</a>
			</div>
			<?php endif;?>
		</div>
	</form>
</body>

</html>

<?php
$conn->close();
?>