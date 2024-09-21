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
<!-- Админ интерфейс-->
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


	<h1 class="title mb20 mt20">Заявки на поставку</h1>

	<table>
		<thead>
			<tr>
				<th class="column-name"><a
						href="?order_by_supplier=name&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Название</a></th>
				<th class="column-supplier"><a
						href="?order_by_supplier=customer&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Заказчик</a></th>
				<th class="column-manufacturer"><a
						href="?order_by_supplier=manufacturer&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Производитель</a>
				</th>
				<th class="column-price"><a
						href="?order_by_supplier=price&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Цена</a></th>
				<th class="column-quantity"><a
						href="?order_by_supplier=quantity&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Количество</a></th>
				<th class="column-cost"><a
						href="?order_by_supplier=cost&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Стоимость</a></th>
				<th class="column-actions">Действия</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (isset($result_supplier_orders)) {
				while ($row = $result_supplier_orders->fetch_assoc()) {
					?>
			<tr>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['name']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['customer']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['manufacturer']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['price']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['quantity']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['cost']); ?></td>
				<td>
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="display:inline;">
						<input type="hidden" name="delete_supplier_order" class="input" value="<?php echo htmlspecialchars($row['id']); ?>">
						<button type="submit" class="button"
							onclick="return confirm('Вы уверены, что хотите отказать в предзаказе?');">Удалить</button>
					</form>
				</td>
			</tr>
			<?php
				}
			} else {
				echo '<tr><td colspan="6">Нет данных для отображения</td></tr>';
				
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

	<div class="message">
		<div class="message__inner">
			<div class="message__item">
				<p class="message__name">
					<span>Имя: </span>Имя
				</p>
				<p class="message__date">
					<span>Дата: </span>12.12.2012
				</p>
				<div class="message__text">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Veniam nesciunt temporibus consequuntur pariatur
					quis
					laborum adipisci a aperiam vitae laboriosam? Officiis, perspiciatis. Labore quis quos temporibus voluptatibus recusandae veritatis
					placeat. Lorem ipsum dolor sit amet consectetur adipisicing elit. Eos asperiores soluta quis dolorum animi labore eaque quibusdam,
					molestias ipsam nemo recusandae aspernatur aliquam ipsum quod debitis illo iste similique neque.</div>
				<button class="button message__button">Кнопка</button>
			</div>
			<div class="message__item">
				<p class="message__name">
					<span>Имя: </span>Имя
				</p>
				<p class="message__date">
					<span>Дата: </span>12.12.2012
				</p>
				<div class="message__text">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Veniam nesciunt temporibus consequuntur pariatur
					quis
					laborum adipisci a aperiam vitae laboriosam? Officiis, perspiciatis. Labore quis quos temporibus voluptatibus recusandae veritatis
					placeat. Lorem ipsum dolor sit amet consectetur adipisicing elit. Eos asperiores soluta quis dolorum animi labore eaque quibusdam,
					molestias ipsam nemo recusandae aspernatur aliquam ipsum quod debitis illo iste similique neque.</div><button
					class="button message__button">Кнопка</button>
			</div>
		</div>
		<div class="message__buttons">
			<!-- <button class="button message__button">Удалить</button> -->
			<button id="message__button" class="button message__button">Закрыть</button>
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
	document.querySelector('#message__button').addEventListener('click', function() {
		document.querySelector('.message').classList.remove('message_open');
	});
});
</script>

</html>

<?php
else:
	if($_SESSION["user_type"] == 0):

    // echo "<p>У вас нет доступа к этой странице.</p>";
?>
<!--Поставщик интерфейс -->
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
		<!-- <input type="text" name="manufacturer_name" class="input" placeholder="Производитель" required> -->

		<p class="title">Производитель</p>
		<select name="manufacturer_name">
			<?php if (isset($res_manuf)) {
				// Loop through the result set
				while ($row = $res_manuf->fetch_assoc()) {
					// Check if the current option should be selected, e.g., based on a user input or a default value
					$selected = ($row['name'] == $selected_manufacturer_name) ? 'selected="selected"' : '';
					?>
			<option value="<?php echo htmlspecialchars($row['name']); ?>" <?php echo $selected; ?>>
				<?php echo htmlspecialchars($row['name']); ?>
			</option>
			<?php }
			} else { ?>
			<option value="" disabled selected>Select a manufacturer</option>
			<?php } ?>
		</select>

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

	<h1 class="title mb20 mt20">Заявки на поставку</h1>

	<table>
		<thead>
			<tr>
				<th class="column-name"><a
						href="?order_by_supplier=name&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Название</a></th>
				<th class="column-supplier"><a
						href="?order_by_supplier=customer&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Заказчик</a></th>
				<th class="column-manufacturer"><a
						href="?order_by_supplier=manufacturer&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Производитель</a>
				</th>
				<th class="column-price"><a
						href="?order_by_supplier=price&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Цена</a></th>
				<th class="column-quantity"><a
						href="?order_by_supplier=quantity&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Количество</a></th>
				<th class="column-cost"><a
						href="?order_by_supplier=cost&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Стоимость</a></th>
				<th class="column-actions">Действия</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (isset($result_supplier_orders)) {
				while ($row = $result_supplier_orders->fetch_assoc()) {
					?>
			<tr>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['name']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['customer']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['manufacturer']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['price']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['quantity']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['cost']); ?></td>
				<td>
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="display:inline;">
						<input type="hidden" name="delete_supplier_order" class="input" value="<?php echo htmlspecialchars($row['id']); ?>">
						<button type="submit" class="button"
							onclick="return confirm('Вы уверены, что хотите отказать в предзаказе?');">Удалить</button>
					</form>
				</td>
			</tr>
			<?php
				}
			} else {
				echo '<tr><td colspan="6">Нет данных для отображения</td></tr>';
				
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

	<div class="message">
		<p class="message__name">
			<span>Имя: </span>Имя
		</p>
		<p class="message__date">
			<span>Дата: </span>12.12.2012
		</p>
		<div class="message__text">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Veniam nesciunt temporibus consequuntur pariatur quis
			laborum adipisci a aperiam vitae laboriosam? Officiis, perspiciatis. Labore quis quos temporibus voluptatibus recusandae veritatis
			placeat. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vitae velit vel doloribus delectus at, harum omnis illo molestias
			pariatur dolor? Laboriosam fugiat voluptatum nulla officia voluptate, laudantium repellendus est culpa.</div>
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

</html>

<?php	
else:

?>
<!-- Покупатель интерфейс -->

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>Добро пожаловать домой, Сиджей</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
	<h1 class="title mb20 mt20">Закупка лекарствами</h1>
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
		<input type="text" name="search_for_shopper" class="input" placeholder="Поиск..."
			value="<?php echo htmlspecialchars($search_query_shopper); ?>">
		<button type="submit" name="search" class="button">Поиск</button>
		<?php if (isset($_SESSION['error_message'])): ?>
		<div class="auth__message">
			✖ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
			<?php unset($_SESSION['error_message']); ?>
		</div>
		<?php endif; ?>
	</form>

	<!-- Таблица с данными о лекарствах -->
	<h1 class="title mb20 mt20">Все лекарства</h1>

	<table>
		<thead>
			<tr>
				<th class="column-name"><a
						href="?order_by_shopper=name&order_dir_shopper=<?php echo htmlspecialchars($order_dir_shopper); ?>">Название</a></th>
				<th class="column-manufacturer"><a
						href="?order_by_shopper=manufacturer&order_dir_shopper=<?php echo htmlspecialchars($order_dir_shopper); ?>">Производитель</a>
				</th>
				<th class="column-supplier"><a
						href="?order_by_shopper=supplier&order_dir_shopper=<?php echo htmlspecialchars($order_dir_shopper); ?>">Поставщик</a></th>
				<th class="column-price"><a
						href="?order_by_shopper=price&order_dir_shopper=<?php echo htmlspecialchars($order_dir_shopper); ?>">Цена</a></th>
				<th class="column-quantity"><a
						href="?order_by_shopper=quantity&order_dir_shopper=<?php echo htmlspecialchars($order_dir_shopper); ?>">Количество</a></th>
				<th class="column-actions">Действия</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (isset($result_shopper)) {
				while ($row = $result_shopper->fetch_assoc()) {
					?>
			<tr>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['name']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['manufacturer']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['supplier']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['price']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['quantity']); ?></td>
				<td>
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="display:inline;"
						data-drug-id="<?php echo htmlspecialchars($row['id']); ?>">
						<input type="hidden" name="add_to_cart" class="input" value="<?php echo htmlspecialchars($row['id']); ?>">
						<input type="hidden" name="desired_quantity" value="">
						<button type="button" class="button" onclick="getQuantity(<?php echo htmlspecialchars($row['id']); ?>)">Добавить</button>
					</form>
				</td>
			</tr>
			<?php
				}
			} else {
				echo '<tr><td colspan="6">Нет данных для отображения</td></tr>';
			}
			?>
		</tbody>
	</table>

	<!-- Таблица с данными о лекарствах пользователя-->
	<h1 class="title mb20 mt20">Моя корзина</h1>

	<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="checkboxForm">

		<table>
			<thead>
				<tr>
					<th class="column-name"><a
							href="?order_by_shopper_cart=name&order_dir_shopper_cart=<?php echo htmlspecialchars($order_dir_shopper_cart); ?>">Название</a>
					</th>
					<th class="column-manufacturer"><a
							href="?order_by_shopper_cart=manufacturer&order_dir_shopper_cart=<?php echo htmlspecialchars($order_dir_shopper_cart); ?>">Производитель</a>
					</th>
					<th class="column-supplier"><a
							href="?order_by_shopper_cart=supplier&order_dir_shopper_cart=<?php echo htmlspecialchars($order_dir_shopper_cart); ?>">Поставщик</a>
					</th>
					<th class="column-price"><a
							href="?order_by_shopper_cart=price&order_dir_shopper_cart=<?php echo htmlspecialchars($order_dir_shopper_cart); ?>">Цена</a>
					</th>
					<th class="column-quantity"><a
							href="?order_by_shopper_cart=quantity&order_dir_shopper_cart=<?php echo htmlspecialchars($order_dir_shopper_cart); ?>">Количество</a>
					</th>
					<th class="column-cost"><a
							href="?order_by_shopper_cart=cost&order_dir_shopper_cart=<?php echo htmlspecialchars($order_dir_shopper_cart); ?>">Стоимость</a>
					</th>
					<th class="column-actions">Действия</th>
				</tr>
			</thead>
			<tbody>
				<?php
			if (isset($result_cart_user)) {
				while ($row = $result_cart_user->fetch_assoc()) {
					?>
				<tr>
					<td style="cursor:pointer">
						<?php
        $checkbox_id = 'check_' . $row['id']; // Генерация уникального id для каждого чекбокса
    ?>
						<input type="checkbox" value="<?php echo htmlspecialchars($row['id']); ?>" id="<?php echo $checkbox_id; ?>"
							name="check_all[]">
						<label for="<?php echo $checkbox_id; ?>"><?php echo htmlspecialchars($row['name']); ?></label>
					</td>

					<td style="cursor:pointer"><?php echo htmlspecialchars($row['manufacturer']); ?></td>
					<td style="cursor:pointer"><?php echo htmlspecialchars($row['supplier']); ?></td>
					<td style="cursor:pointer"><?php echo htmlspecialchars($row['price']); ?></td>
					<td style="cursor:pointer" data-type="quantity" data-id="<?php echo htmlspecialchars($row['id']); ?>"
						data-table="drugs_shopper_cart" data-field="quantity" class="openPopup"><?php echo htmlspecialchars($row['quantity']); ?></td>
					<td style="cursor:pointer"><?php echo htmlspecialchars($row['cost']); ?></td>
					<td>
						<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="display:inline;">
							<input type="hidden" name="delete_shopper_drug" class="input" value="<?php echo htmlspecialchars($row['id']); ?>">
							<button type="submit" class="button"
								onclick="return confirm('Вы уверены, что хотите отменить покупку этого лекарства?');">Удалить</button>
						</form>
					</td>
				</tr>
				<?php
				}
			} else {
				echo '<tr><td colspan="6">Нет данных для отображения</td></tr>';
			}
			?>
			</tbody>
		</table>

		<button   class="button form__button" type="button" type="submit">Обработать выбранные чекбоксы</button>
	</form>



	<div id="popup" class="popup">
		<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="popup__content">
			<input type="hidden" id="formType" name="formType">
			<input type="hidden" id="formId" name="formId">
			<input type="hidden" id="tableName" name="tableName">
			<input type="hidden" id="fieldName" name="fieldName">
			<input type="text" id="popupInput" name="input" class="input" placeholder="Название" required>
			<button type="submit" class="button popup__button">Сохранить</button>
		</form>
	</div>

	<div class="message">
		<p class="message__name">
			<span>Имя: </span>Имя
		</p>
		<p class="message__date">
			<span>Дата: </span>12.12.2012
		</p>
		<div class="message__text">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Veniam nesciunt temporibus consequuntur pariatur quis
			laborum adipisci a aperiam vitae laboriosam? Officiis, perspiciatis. Labore quis quos temporibus voluptatibus recusandae veritatis
			placeat. Lorem ipsum dolor sit amet consectetur adipisicing elit. Reiciendis, sint alias minus totam placeat commodi eligendi, dolorem
			omnis ipsa, sed at iure architecto? Hic in, doloribus necessitatibus quaerat veritatis dolores.</div>
		<div class="message__buttons">
			<!-- <button class="button message__button">Удалить</button> -->
			<button class="button message__button">Закрыть</button>
		</div>
	</div>

</body>

<script>
function getQuantity(drugId) {
	let quantity = prompt("Введите количество (целое число):");

	// Проверка на целое число
	if (quantity !== null && Number.isInteger(+quantity) && +quantity > 0) {
		// Устанавливаем значение в скрытое поле
		let form = document.querySelector(`form[data-drug-id='${drugId}']`);
		form.querySelector("input[name='desired_quantity']").value = quantity;

		// Отправляем форму
		form.submit();
	} else {
		alert("Пожалуйста, введите корректное целое число больше нуля.");
	}
}
</script>

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

</html>

<?php	
endif;
endif;
?>


<?php
// Закрываем соединение
if (isset($conn)) {
    $conn->close();
}
?>