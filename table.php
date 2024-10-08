<?php
include 'sessionConf.php';

session_start(); // Начинаем сессию
$_SESSION['sql_error_message'] = 'Ошибка базы данных:';
$_SESSION['server_error_message'] = 'Ошибка сервера';
$_SESSION['server_conn_error'] = false;

if (!isset($_SESSION['user'])) {
    // Если пользователь не авторизован, перенаправляем на страницу входа
    header("Location: index.php");
    exit(); // Прекращаем выполнение скрипта после перенаправления
}

include 'manage_drugs.php'; // Подключаем файл обработки регистрации

// Проверяем, произошла ли ошибка при выполнении запроса
if (!$result) {
    $_SESSION['error_message'] = "Ошибка запроса: " . htmlspecialchars($mysqli->error);
}
//Закоммент
// echo $_SESSION["user"];
// echo $_SESSION["user_id"];
// echo $_SESSION["user_type"];
?>
<?php
if($_SESSION["user_type"] == 1):

?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>Управление Лекарствами</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
	<h1 class="title mb20 mt20">Управление Лекарствами</h1>
	<form method="POST" action="
    <?php   
        // session_unset();
        // session_destroy(); 
    ?>">
		<button type="submit" name="logout" class="button button__fixed button__fixed_right">
			Выйти
		</button>
	</form>

	<a href="index.php" class="button button__fixed">
		На главную
	</a>
	<!-- Форма поиска лекарств-->
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form">
		<p class="title">Поиск</p>
		<input type="text" name="search_query" class="input" placeholder="Поиск..." value="<?php echo htmlspecialchars($search_query); ?>">
		<button type="submit" name="search" class="button">Поиск</button>
	</form>

	<!-- Форма добавления новой записи -->
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form">
		<p class="title">Добавление лекарств</p>
		<input type="text" name="name" class="input" placeholder="Название" required>
		<input type="number" name="manufacturer_id" class="input" placeholder="ID производителя" step="1" required>
		<input type="number" name="price" class="input" placeholder="Цена" step="0.01" required>
		<input type="number" name="quantity" class="input" placeholder="Количество" step="1" required>
		<input type="number" name="provider_id" class="input" placeholder="ID поставщика" step="1" required>
		<button type="submit" name="add" class="button">Добавить</button>
		<?php if (isset($_SESSION['error_message'])): ?>
		<div class="auth__message">
			✖ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
			<?php unset($_SESSION['error_message']); ?>
		</div>
		<?php endif; ?>
	</form>

	<!-- Таблица с данными о лекарствах -->
	<h1 class="title mb20 mt20">Лекарства</h1>

	<table>
		<thead>
			<tr>
				<th class="column-id"><a href="?order_by=id&order_dir=<?php echo htmlspecialchars($order_dir); ?>">ID</a></th>
				<th class="column-name"><a href="?order_by=name&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Название</a></th>
				<th class="column-manufacturer-id"><a href="?order_by=manufacturer_id&order_dir=<?php echo htmlspecialchars($order_dir); ?>">ID
						Производитель</a></th>
				<th class="column-provider-id"><a href="?order_by=provider_id&order_dir=<?php echo htmlspecialchars($order_dir); ?>">ID Поставщик</a>
				</th>
				<th class="column-price"><a href="?order_by=price&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Цена</a></th>
				<th class="column-quatity"><a href="?order_by=quantity&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Количество</a></th>
				<th class="column-cost"><a href="?order_by=cost&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Стоимость</a></th>
				<th class="column-actions">Действия</th>
			</tr>
		</thead>
		<tbody>
			<?php
            if (isset($result)) {
                while ($row = $result->fetch_assoc()) {
                    ?>
			<tr>
				<td><?php echo htmlspecialchars($row['id']); ?></td>
				<td data-type="name" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs" data-field="name" class="openPopup"
					style="cursor:pointer">
					<?php echo htmlspecialchars($row['name']); ?></td>
				<td data-type="manufacturer_id" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs" data-field="manufacturer_id"
					class="openPopup" style="cursor:pointer">
					<?php echo htmlspecialchars($row['manufacturer_id']); ?></td>
				<td data-type="provider_id" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs" data-field="provider_id"
					class="openPopup" style="cursor:pointer">
					<?php echo htmlspecialchars($row['provider_id']); ?></td>
				<td data-type="price" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs" data-field="price" class="openPopup"
					style="cursor:pointer">
					<?php echo htmlspecialchars($row['price']); ?></td>
				<td data-type="quantity" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs" data-field="quantity"
					class="openPopup" style="cursor:pointer">
					<?php echo htmlspecialchars($row['quantity']); ?></td>
				<td data-type="cost" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs" data-field="cost"
					style="cursor:pointer">
					<?php echo htmlspecialchars($row['cost']); ?></td>
				<td>
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="display:inline;">
						<input type="hidden" name="delete" class="input" value="<?php echo htmlspecialchars($row['id']); ?>">
						<button type="submit" class="button" onclick="return confirm('Вы уверены, что хотите удалить эту запись?');">Удалить</button>
					</form>
				</td>
			</tr>
			<?php
                }
            } else {
                echo '<tr><td colspan="5">Нет данных для отображения</td></tr>';
            }
            ?>
		</tbody>
	</table>

	<!-- Форма добавления новой записи о производителях-->
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form">
		<p class="title">Добавление производителя</p>
		<input type="text" name="name" class="input" placeholder="Название" required>
		<button type="submit" name="add_manufacturer" class="button">Добавить</button>
		<?php if (isset($_SESSION['error_message'])): ?>
		<div class="auth__message">
			✖ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
			<?php unset($_SESSION['error_message']); ?>
		</div>
		<?php endif; ?>
	</form>

	<h1 class="title mb20 mt20">Производители</h1>

	<!-- Таблица с данными о производителях!-->

	<table>
		<thead>
			<tr>
				<th class="column-id"><a href="?manufacturers_order_by=id&manufacturers_order_dir=<?php echo $order_dir; ?>">ID</a></th>
				<th class="column-name"><a href="?manufacturers_order_by=name&manufacturers_order_dir=<?php echo $order_dir; ?>">Производитель</a>
				</th>
				<th class="column-actions">Действия</th>
			</tr>
		</thead>
		<tbody>
			<?php 
      if (isset($res_manufacturers)) {
      while ($row = $res_manufacturers->fetch_assoc()): ?>
			<tr>
				<td><?php echo htmlspecialchars($row['id']); ?></td>
				<td data-type="manufacturers" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="manufacturers" data-field="name"
					class="openPopup" style="cursor:pointer"><?php echo htmlspecialchars($row['name']); ?></td>
				<td>
					<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
						<input type="hidden" name="delete_manufacturer" class="input" value="<?php echo $row['id']; ?>">
						<button type="submit" class="button" onclick="return confirm('Вы уверены, что хотите удалить эту запись?');">Удалить</button>
					</form>
				</td>
			</tr>
			<?php endwhile; 
        } else {
            // Можно добавить сообщение, если результат не установлен
            echo '<tr><td colspan="5">Нет данных для отображения</td></tr>';
        }
    ?>
		</tbody>
	</table>

	<!-- Таблица с данными о пользователях!-->
	<h1 class="title mb20 mt20">Поставщики</h1>

	<table>
		<thead>
			<tr>
				<th class="column-id"><a href="?users_order_by=id&users_order_dir=<?php echo $order_dir; ?>">ID</a></th>
				<th class="column-name"><a href="?users_order_by=name&users_order_dir=<?php echo $order_dir; ?>">Пользователь</a></th>
				<th class="column-user-type"><a href="?users_order_by=type&users_order_dir=<?php echo $order_dir; ?>">Роль</a></th>
				<th class="column-actions">Действия</th>
			</tr>
		</thead>
		<tbody>
			<?php 
      if (isset($res)) {
      while ($row = $res->fetch_assoc()): ?>
			<tr>
				<td><?php echo htmlspecialchars($row['id']); ?></td>
				<td data-type="name" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="users" data-field="name" class="openPopup"
					style="cursor:pointer"><?php echo htmlspecialchars($row['name']); ?></td>
				<td data-type="type" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="users" data-field="type" class="openPopup"
					style="cursor:pointer"><?php echo htmlspecialchars($row['type']); ?></td>
				<td>
					<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
						<input type="hidden" name="delete_user" class="input" value="<?php echo $row['id']; ?>">
						<button type="submit" class="button" onclick="return confirm('Вы уверены, что хотите удалить эту запись?');">Удалить</button>
					</form>
				</td>
			</tr>
			<?php endwhile; 
        } else {
            // Можно добавить сообщение, если результат не установлен
            echo '<tr><td colspan="5">Нет данных для отображения</td></tr>';
        }
    ?>
		</tbody>
	</table>




	<div id="popup" class="popup">
		<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="popup__content">
			<input type="hidden" id="formType" name="formType">
			<input type="hidden" id="formId" name="formId">
			<input type="hidden" id="tableName" name="tableName"> <!-- Новое поле для таблицы -->
			<input type="hidden" id="fieldName" name="fieldName"> <!-- Новое поле для поля -->
			<input type="text" id="popupInput" name="input" class="input" placeholder="Название" required>
			<button type="submit" class="button popup__button">Сохранить</button>
		</form>
	</div>

	<div class="message message_open">
		<p class="message__name">
			<span>Имя: </span>Имя
		</p>
		<p class="message__date">
			<span>Дата: </span>12.12.2012
		</p>
		<div class="message__text">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Veniam nesciunt temporibus consequuntur pariatur quis
			laborum adipisci a aperiam vitae laboriosam? Officiis, perspiciatis. Labore quis quos temporibus voluptatibus recusandae veritatis
			placeat.</div>
		<div class="message__buttons">
			<!-- <button class="button message__button">Удалить</button> -->
			<button class="button message__button">Закрыть</button>
		</div>
	</div>


</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Открываем попап при клике на кнопку
	document.querySelectorAll('.openPopup').forEach((element) => {
		element.addEventListener('click', function(event) {
			const clickedText = event.target.innerText;
			const type = event.target.dataset.type;
			const id = event.target.dataset.id;
			const table = event.target.dataset.table; // Получаем таблицу
			const field = event.target.dataset.field; // Получаем поле

			document.getElementById('popupInput').value = clickedText;
			event.stopPropagation();
			openPopup(type, id, table, field); // Передаем таблицу и поле
		});
	})


	// Функция для открытия попапа
	function openPopup(type, id, table, field) {
		const formType = document.querySelector('#formType');
		const formId = document.querySelector('#formId');
		const tableName = document.querySelector('#tableName');
		const fieldName = document.querySelector('#fieldName');

		formType.value = type; // Измени на .value
		formId.value = id; // Измени на .value
		tableName.value = table; // Измени на .value
		fieldName.value = field; // Измени на .value

		const popup = document.getElementById("popup");
		popup.classList.add("popup_open");
	}

	// Обработчик для закрытия попапа при клике вне формы
	document.addEventListener('click', function(event) {
		const popup = document.getElementById("popup");
		const popupContent = document.querySelector(".popup__content");

		// Проверяем, был ли клик не по форме (вне .popup__content)
		if (popup.classList.contains("popup_open") && !popupContent.contains(event.target)) {
			popup.classList.remove("popup_open"); // Закрываем попап
		}
	});

	// Предотвращаем закрытие попапа при клике внутри формы
	document.querySelector('.popup__content').addEventListener('click', function(event) {
		event.stopPropagation();
	});

	// Обработчик для кнопки "Удалить"
	document.querySelector('.message__button').addEventListener('click', function() {
		document.querySelector('.message').classList.remove('message_open');
	});
});
</script>


<style>
.message {
	text-align: justify;
	max-width: 300px;
	max-height: 300px;
	position: fixed;
	top: 20px;
	left: 65%;
	z-index: 999;
	display: none;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	padding: 20px;
	border-radius: 20px;
	gap: 12px;
	border: 2px solid #000;
	background-color: #fff;

	p {
		align-self: flex-start;
		text-align: left;
		font-size: 20px;
		font-weight: 600;
	}

	span {
		font-size: 20px;
		font-weight: 400;
	}
}

.message__buttons {
	display: flex;
	gap: 8px;
}

.message__buttons {
	width: 100%;
}

.message_open {
	display: flex;
}

.popup {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	z-index: 999;
	display: none;
	justify-content: center;
	align-items: center;
}

.popup__button {
	max-width: 120px;

}

.popup__content {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 12px;
	padding: 24px;
	background-color: #fff;
	border-radius: 20px;
	border: 2px solid #000;
}

.popup_open {
	display: flex;
}
</style>

</html>

<?php
else:
    // echo "<p>У вас нет доступа к этой странице.</p>";
?>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>Управление Лекарствами</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
	<h1 class="title mb20 mt20">Управление Лекарствами</h1>
	<form method="POST" action="
    <?php   
        // session_unset();
        // session_destroy(); 
    ?>">
		<button type="submit" name="logout" class="button button__fixed button__fixed_right">
			Выйти
		</button>
	</form>

	<a href="index.php" class="button button__fixed">
		На главную
	</a>
	<!-- Форма поиска лекарств-->
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form">
		<p class="title">Поиск</p>
		<input type="text" name="search_for_user" class="input" placeholder="Поиск..." value="<?php 
		echo htmlspecialchars($search_query_user); ?>">
		<button type="submit" name="search" class="button">Поиск</button>
	</form>

	<!-- Форма добавления новой записи -->
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form">
		<p class="title">Добавление лекарств</p>
		<input type="text" name="name" class="input" placeholder="Название" required>
		<input type="text" name="manufacturer_name" class="input" placeholder="Производитель" required>
		<input type="number" name="price" class="input" placeholder="Цена" step="0.01" required>
		<input type="number" name="quantity" class="input" placeholder="Количество" step="1" required>
		<button type="submit" name="add_drugs_user" class="button">Добавить</button>
		<?php if (isset($_SESSION['error_message'])): ?>
		<div class="auth__message">
			✖ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
			<?php unset($_SESSION['error_message']); ?>
		</div>
		<?php endif; ?>
	</form>

	<!-- Таблица с данными о лекарствах -->
	<h1 class="title mb20 mt20">Ваши лекарства</h1>

	<table>
		<thead>
			<tr>
				<th class="column-id"><a href="?order_by_user=id&order_dir_user=<?php echo htmlspecialchars($order_dir_user); ?>">ID</a></th>
				<th class="column-name"><a href="?order_by_user=name&order_dir_user=<?php echo htmlspecialchars($order_dir_user); ?>">Название</a>
				</th>
				<th class="column-manufacturer"><a
						href="?order_by_user=manufacturer_id&order_dir_user=<?php echo htmlspecialchars($order_dir_user); ?>">Производитель</a></th>
				<th class="column-price"><a href="?order_by_user=price&order_dir_user=<?php echo htmlspecialchars($order_dir_user); ?>">Цена</a></th>
				<th class="column-quatity"><a
						href="?order_by_user=quantity&order_dir_user=<?php echo htmlspecialchars($order_dir_user); ?>">Количество</a></th>
				<th class="column-cost"><a href="?order_by_user=cost&order_dir_user=<?php echo htmlspecialchars($order_dir_user); ?>">Стоимость</a>
				</th>
				<th class="column-actions">Действия</th>
			</tr>
		</thead>
		<tbody>
			<?php
            if (isset($result_user)) {
                while ($row = $result_user->fetch_assoc()) {
                    ?>
			<tr>
				<td><?php echo htmlspecialchars($row['id']); ?></td>
				<td data-type="name" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs_user" data-field="name" class="openPopup"
					style="cursor:pointer">
					<?php echo htmlspecialchars($row['name']); ?></td>
				<td data-type="manufacturer" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs_user" data-field="manufacturer"
					class="openPopup" style="cursor:pointer">
					<?php echo htmlspecialchars($row['manufacturer']); ?></td>
				<td data-type="price" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs_user" data-field="price"
					class="openPopup" style="cursor:pointer">
					<?php echo htmlspecialchars($row['price']); ?></td>
				<td data-type="quantity" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs_user" data-field="quantity"
					class="openPopup" style="cursor:pointer">
					<?php echo htmlspecialchars($row['quantity']); ?></td>
				<td data-type="cost" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs_user" data-field="cost"
					style="cursor:pointer">
					<?php echo htmlspecialchars($row['cost']); ?></td>
				<td>
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="display:inline;">
						<input type="hidden" name="delete_drug_user" class="input" value="<?php echo htmlspecialchars($row['id']); ?>">
						<button type="submit" class="button" onclick="return confirm('Вы уверены, что хотите удалить эту запись?');">Удалить</button>
					</form>
				</td>
			</tr>
			<?php
                }
            } else {
                echo '<tr><td colspan="5">Нет данных для отображения</td></tr>';
            }
            ?>
		</tbody>
	</table>

	<div id="popup" class="popup">
		<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="popup__content">
			<input type="hidden" id="formType" name="formType">
			<input type="hidden" id="formId" name="formId">
			<input type="hidden" id="tableName" name="tableName"> <!-- Новое поле для таблицы -->
			<input type="hidden" id="fieldName" name="fieldName"> <!-- Новое поле для поля -->
			<input type="text" id="popupInput" name="input" class="input" placeholder="Название" required>
			<button type="submit" class="button popup__button">Сохранить</button>
		</form>
	</div>

</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Открываем попап при клике на кнопку
	document.querySelectorAll('.openPopup').forEach((element) => {
		element.addEventListener('click', function(event) {
			const clickedText = event.target.innerText;
			const type = event.target.dataset.type;
			const id = event.target.dataset.id;
			const table = event.target.dataset.table; // Получаем таблицу
			const field = event.target.dataset.field; // Получаем поле

			document.getElementById('popupInput').value = clickedText;
			event.stopPropagation();
			openPopup(type, id, table, field); // Передаем таблицу и поле
		});
	})


	// Функция для открытия попапа
	function openPopup(type, id, table, field) {
		const formType = document.querySelector('#formType');
		const formId = document.querySelector('#formId');
		const tableName = document.querySelector('#tableName');
		const fieldName = document.querySelector('#fieldName');

		formType.value = type; // Измени на .value
		formId.value = id; // Измени на .value
		tableName.value = table; // Измени на .value
		fieldName.value = field; // Измени на .value

		const popup = document.getElementById("popup");
		popup.classList.add("popup_open");
	}

	// Обработчик для закрытия попапа при клике вне формы
	document.addEventListener('click', function(event) {
		const popup = document.getElementById("popup");
		const popupContent = document.querySelector(".popup__content");

		// Проверяем, был ли клик не по форме (вне .popup__content)
		if (popup.classList.contains("popup_open") && !popupContent.contains(event.target)) {
			popup.classList.remove("popup_open"); // Закрываем попап
		}
	});

	// Предотвращаем закрытие попапа при клике внутри формы
	document.querySelector('.popup__content').addEventListener('click', function(event) {
		event.stopPropagation();
	});
});
</script>


<style>
.popup {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	z-index: 999;
	display: none;
	justify-content: center;
	align-items: center;
}

.popup__button {
	max-width: 120px;

}

.popup__content {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 12px;
	padding: 24px;
	background-color: #fff;
	border-radius: 20px;
	border: 2px solid #000;
}

.popup_open {
	display: flex;
}
</style>

</html>
<?php	
endif;

?>


<?php
// Закрываем соединение
if (isset($conn)) {
    $conn->close();
}
?>