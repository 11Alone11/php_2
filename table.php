<?php
include 'sessionConf.php';
// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// //header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");
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
// $directory = 'images/';
// $files = glob($directory . '*', GLOB_MARK);
// foreach ($files as $file) {
//     if (is_file($file)) {
//         clearstatcache(true, $file);
//     }
// }
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
	<!-- <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" /> -->
	<title>Управление Лекарствами</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
	<h1 class="title mb20 mt20">Управление Лекарствами</h1>

	<!-- <div id="imageContainer">Click to upload image
		<input type="file" id="fileInput" accept="image/*">
	</div> -->
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
	<a href="activity_log.php" class="button button__fixed button__fixed_colhoz">
		Лог событий
	</a>
	<a href="tables_settings.php" class="button button__fixed button__fixed_table_settings">
		Веса таблиц
	</a>
	<!-- <div id="imageContainer" class="image__fixed" style="background-image: url('data:image/jpeg;base64,<?php echo $profilePhoto; ?>');">
		<?php// if (!$profilePhoto): ?>
			Загрузить фотку
		<?php// endif; ?>
        <input type="file" id="fileInput" class="image__input" accept="image/*" style="display:none;">
    </div> -->
	<div id="imageContainer" class="image__fixed" style="background-image: url('data:image/jpeg;base64,<?php echo $profilePhoto; ?>');">
		<?php if (!$profilePhoto): ?>
			<span>Загрузить фотку</span>
		<?php else: ?>
			<span class="error-mess" style="display:none;">Ошибка загрузки аватара</span>
		<?php endif; ?>
		<input type="file" id="fileInput" class="image__input" accept="image/*" style="display:none;">
	</div>
	<!-- Форма поиска лекарств-->
	<form style="display:none;" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form">
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
		<!-- <input type="file" name="medicinePhoto" class="input" accept="image/*" required> -->
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
	<?php if (isset($_SESSION['medicine_images_error'])): ?>
		<div class="centr">
			<div class="image_error_message">
				✖ <?php echo htmlspecialchars($_SESSION['medicine_images_error']); ?>
				<?php unset($_SESSION['medicine_images_error']);?>
			</div>
		</div>
	<?php endif; ?>		
	<table>
		<thead>
			<tr>
				<th class="column-id"><a href="?order_by=id&order_dir=<?php echo htmlspecialchars($order_dir); ?>">IMG</a></th>
				<th class="column-id"><a href="?order_by=id&order_dir=<?php echo htmlspecialchars($order_dir); ?>">ID</a></th>
				<th class="column-name"><a href="?order_by=name&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Название</a></th>
				<th class="column-manufacturer-id"><a href="?order_by=manufacturer_id&order_dir=<?php echo htmlspecialchars($order_dir); ?>">ID
						Производитель</a></th>
				<th class="column-provider-id"><a href="?order_by=provider_id&order_dir=<?php echo htmlspecialchars($order_dir); ?>">ID Поставщик</a>
				</th>
				<th class="column-price"><a href="?order_by=price&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Цена</a></th>
				<th class="column-quatity"><a href="?order_by=quantity&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Количество</a></th>
				<th class="column-cost"><a href="?order_by=cost&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Стоимость</a></th>
				<th class="column-status"><a href="?order_by=is_allowed&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Статус</a></th>
				<th class="column-actions">Действия</th>
			</tr>
		</thead>
		<tbody>
			<?php
            if (isset($result)) {
                while ($row = $result->fetch_assoc()) {
                    ?>
			<tr>
				<td>
					<img class="table__img openImageUpdate" src="<?php echo htmlspecialchars(empty($row['medicinePhoto']) ? 'https://cms.imgworlds.com/assets/473cfc50-242c-46f8-80be-68b867e28919.jpg?key=home-gallery' : $row['medicinePhoto']); ?>"
						onerror='this.src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSN8eeyOk32x2hdhjf1kO4sFmM9WUcId9ayv-VNF4yd7PLL_9Bkl6CFMVvrBc9yYp_ZNow&usqp=CAU";'
						data-id='<?php echo htmlspecialchars($row['id']); ?>'>
						
						<!-- <img class="table__img openImageUpdate" src="..." data-image="<?php //echo htmlspecialchars($row['medicinePhoto']); ?>" /> -->
				</td>
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
				<td data-type="is_allowed" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs" data-field="is_allowed"
					style="cursor:pointer" class="openPopup">
					<?php echo htmlspecialchars($row['is_allowed']); ?></td>
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
	<?php $rowSum = $supplier_analytics_sum_drugs->fetch_assoc();
	 $rowMedDrug = $supplier_analytics_med_drugs->fetch_assoc();?>
	<h1 class="extrasubtitle mb20-extrasubtitle mt20-extrasubtitle">Суммарная стоимость заказов:
		<?php echo htmlspecialchars(number_format($rowSum['sumCost'], 2, '.', ''))?></h1>
	<h1 class="extrasubtitle mb20-extrasubtitle mt20-extrasubtitle">Средний доход с продажи единицы:
		<?php echo htmlspecialchars(number_format($rowMedDrug['medCost'], 2, '.', ''))?></h1>

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
				<th class="column-status"><a
						href="?order_by_supplier=status&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Статус</a></th>
				<th class="column-status"><a
						href="?order_by_supplier=last_updated&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Время
						обновления</a></th>
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
				<td data-type="status" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="my_orders_requests" data-field="status"
					style="cursor:pointer" class="openPopup"><?php echo htmlspecialchars($row['status']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['last_updated']); ?></td>
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
			<input type="hidden" id="tableName" name="tableName">
			<input type="hidden" id="fieldName" name="fieldName">
			<input type="text" id="popupInput" name="input" class="input" placeholder="Название" required style="display: block;">
			<select id="statusSelect" name="input" class="input" required style="display: none;">
			</select>
			<button type="submit" class="button popup__button">Сохранить</button>
		</form>
	</div>

	<div id="vsplyvImage" class="vsplyvImage">
			<div class="vsplyvImage__content">
				<img id="currentImage" class="vsplyvImage__img" src="" alt="Текущая картинка" />
				<input type="file" id="newImageFile" class="input" accept="image/*" required>
				<button id="uploadImageButton" class="button vsplyvImage__button">Обновить изображение</button>
				<button id="closeImageButton" class="button vsplyvImage__button">Закрыть</button>
			</div>
	</div>

	<?php if ($orders_from_shoppers->num_rows > 0 ||  $drugs_add_requests->num_rows > 0 || $drugs_add_requests_feedback->num_rows): ?>
	<div class="message message_open">
		<div class="message__inner">
			<?php
			while ($row = $orders_from_shoppers->fetch_assoc()) {
				$name = htmlspecialchars($row['userName']);
				$date = htmlspecialchars($row['update_date']);
				$status = htmlspecialchars($row['status']);
				$drugName =  htmlspecialchars($row['drugName']);
				$manufacturerName =  htmlspecialchars($row['manufacturerName']);
				$cost = htmlspecialchars($row['cost']);
				$quantity =  htmlspecialchars($row['quantity']);
				$orderId = htmlspecialchars($row['id']);
				?>
			<div class="message__item" data-order-id="<?php echo $orderId; ?>">
				<p class="message__name">
					<span>Имя: </span><?php echo $name;?>
				</p>
				<p class="message__date">
					<span>Дата: </span><?php echo $date; ?>
				</p>
				<div class="message__text">
					<?php echo "$status. Покупатель заказал лекарство '$drugName' от производителя '$manufacturerName' в количестве $quantity штук на стоимость $cost у.е."; ?>
				</div>
				<div class="button-container">
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
						<input type="hidden" name="order_from_shopper_apply" value="<?php echo $orderId; ?>">
						<button type="submit" class="button message__button">Одобрить</button>
					</form>
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
						<input type="hidden" name="order_from_shopper_cancel" value="<?php echo $orderId; ?>">
						<button type="submit" class="button message__button">Отклонить</button>
					</form>
				</div>
			</div>
			<?php
			}
			?>
			<?php
			while ($row = $drugs_add_requests->fetch_assoc()) {
				$name = htmlspecialchars($row['supplier']);
				$status = htmlspecialchars($row['status']);
				$drugName =  htmlspecialchars($row['name']);
				$date = htmlspecialchars($row['update_date']);
				$manufacturerName =  htmlspecialchars($row['manufacturer']);
				$cost = htmlspecialchars($row['price']);
				$quantity =  htmlspecialchars($row['quantity']);
				$drugId = htmlspecialchars($row['id']);
				?>
			<div class="message__item" data-order-id="<?php echo $orderId; ?>">
				<p class="message__name">
					<span>Имя поставщика: </span><?php echo $name;?>
				</p>
				<p class="message__date">
					<span>Дата: </span><?php echo $date; ?>
				</p>
				<div class="message__text">
					<?php echo "$status. Поставщик '$name' поставляет лекарство '$drugName' от производителя '$manufacturerName' в количестве $quantity штук по цене $cost у.е. за штуку. Одобрить поставку?"; ?>
				</div>
				<div class="button-container">
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
						<input type="hidden" name="drug_supply_apply" value="<?php echo $drugId; ?>">
						<button type="submit" class="button message__button">Одобрить</button>
					</form>
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
						<input type="hidden" name="drug_supply_cancel" value="<?php echo $drugId; ?>">
						<button type="submit" class="button message__button">Отклонить</button>
					</form>
				</div>
			</div>
			<?php
			}
			?>
			<?php
			while ($row = $drugs_add_requests_feedback->fetch_assoc()) {
				$name = htmlspecialchars($row['admin_name']);
				$date = htmlspecialchars($row['update_date']);
				$status = htmlspecialchars($row['status']);
				$drugName =  htmlspecialchars($row['name']);
				$manufacturerName =  htmlspecialchars($row['manufacturer']);
				$cost = htmlspecialchars($row['price']);
				$quantity =  htmlspecialchars($row['quantity']);
				$orderId = htmlspecialchars($row['id']);
				?>
			<div class="message__item" data-order-id="<?php echo $orderId; ?>">
				<p class="message__name">
					<span>Имя: </span><?php echo $name; ?>
				</p>
				<p class="message__date">
					<span>Дата: </span><?php echo $date; ?>
				</p>
				<div class="message__text">
					<?php $money = $cost*$quantity; echo "Доступен результат по обработке поставки лекарства '$drugName' со стороны админитсрации от производителя '$manufacturerName' на сумму $money у.е. Результат: $status"; ?>
				</div>
				<div class="button-container">
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
						<input type="hidden" name="drug_request_status_viewed" value="<?php echo $orderId; ?>">
						<button type="submit" class="button message__button">Понятно</button>
					</form>
				</div>
			</div>
			<?php
			}
			?>
		</div>
		<div class="message__buttons">
			<button id="message__button" class="button message__button__close">Закрыть</button>
			<button id="expand__button" class="button message__button__expand" style="display:none;">Развернуть сообщения</button>
		</div>
		<?php  endif;?>


</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
		try {
			const bfr = 400;
			document.getElementById('fileInput').addEventListener('change', function(event) {
            var file = event.target.files[0];
            if (file) {
                var fileSize = file.size;
                var maxSize = bfr * 1024 * 1024;
                if (fileSize > maxSize) {
                    alert('Файл слишком большой. Максимальный размер: 10 MB.');
                }
            }
        	});
			document.getElementById('newImageFile').addEventListener('change', function(event) {
            var file = event.target.files[0];
            if (file) {
                var fileSize = file.size;
                var maxSize = bfr * 1024 * 1024;
                if (fileSize > maxSize) {
                    alert('Файл слишком большой. Максимальный размер: 10 MB.');
                }
            }
        	});
			const imageContainer1 = document.getElementById('imageContainer');
			const profilePhoto = '<?php echo $profilePhoto; ?>';

			// Проверка на битое изображение
			const img = new Image();
			img.src = 'data:image/jpeg;base64,' + profilePhoto;

			img.onerror = function() {
				// Если изображение не загружается, показываем сообщение об ошибке
				document.querySelector('.error-mess').style.display = 'block';
				imageContainer1.style.backgroundImage = 'none'; // опционально убираем фон
			};

			const vsplyvImage = document.getElementById('vsplyvImage');
			const newImageLinkInput = document.getElementById('newImageLink');
			const currentImage = document.getElementById('currentImage');
			const updateImageButton = document.getElementById('updateImageButton');
			const closeImageButton = document.getElementById('closeImageButton');
			baseDrugImage  = "";
			let currentID = 0;
			// Функция для открытия vsplyvImage
			function openVsplyvImage(imageSrc, id) {
				currentImage.src = imageSrc;
				currentID = id;
				vsplyvImage.classList.add('open');
			}

			// Обработчик клика для открытия vsplyvImage при необходимости
			document.querySelectorAll('.openImageUpdate').forEach((element) => {
				element.addEventListener('click', function() {
					const imageSrc = element.src; // Get the image source
					const id = element.dataset.id; // Get the ID
					openVsplyvImage(imageSrc, id);
				});
			});

			// Обработчик для обновления ссылки на изображение
			uploadImageButton.addEventListener('click', function() {
				const fileInput = document.getElementById('newImageFile');
				const file = fileInput.files[0];

				if (file) {
					const formData = new FormData();
					formData.append('medicinePhoto', file);
					formData.append('id', currentID);

					fetch('photoAPI/medicinePhotoUpd.php', {
						method: 'POST',
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						if (data.status === 'success') {
							currentImage.src = `images/${data.fileName}`;
							const newImageUrl = `images/${data.fileName}`;
							const imageElement = document.querySelector(`img[data-id='${currentID}']`);
							if (imageElement) {
								imageElement.src = newImageUrl; // Обновляем изображение в таблице
							}
							alert('Изображение обновлено успешно.');
							//vsplyvImage.classList.remove('open');
						} else {
							alert(data.message);
						}
					})
					.catch(error => {
						console.error('Ошибка:', error);
					});
				} else {
					alert('Пожалуйста, выберите изображение.');
				}
			});

			// Обработчик для закрытия vsplyvImage
			closeImageButton.addEventListener('click', function() {
				vsplyvImage.classList.remove('open');
			});
			// Закрытие vsplyvImage при клике вне содержимого
			vsplyvImage.addEventListener('click', function(event) {
				if (event.target === vsplyvImage) {
					vsplyvImage.classList.remove('open');
				}
			});

			const imageContainer = document.getElementById('imageContainer');
			const fileInput = document.getElementById('fileInput');

			// Добавляем обработчик клика по div
			imageContainer.addEventListener('click', () => {
				fileInput.click(); // Открывает диалоговое окно выбора файла
			});
			
			// Обработчик для загрузки файла
			fileInput.addEventListener('change', (event) => {
				const file = event.target.files[0];
				let res = true;
				if (file) {
					var fileSize = file.size;
					var maxSize = bfr * 1024 * 1024;
					if (fileSize > maxSize) {
						res = false;
					}
				}
				if (file && res) {
					const formData = new FormData();
					formData.append('profilePhoto', file);
					const reader = new FileReader();

					fetch('photoAPI/userProfilePhoto.php', {
						method: 'POST',
						body: formData
					})
					.then(response => {
						if (!response.ok) {
							throw new Error('Сетевая ошибка: ответ не был получен');
						}
						return response.json(); 
					})
					.then(data => {
						if (data.status === 'success') {
							// Только после успешного ответа обновляем изображение
							reader.onload = function(e) {
								imageContainer.style.backgroundImage = `url(${e.target.result})`;
								imageContainer.textContent = ''; 
							};
							reader.readAsDataURL(file); // Чтение файла для отображения после успешного ответа
						} else {
							// Если статус не success, выводим сообщение об ошибке
							alert(data.message);
							console.error(data.message);
						}
					})
					.catch(error => {
						const errorMessage = 'Ошибка при загрузке изображения: ' + error.message; // Используем error.message для более ясного сообщения
						console.error('Ошибка:', error);
						alert(errorMessage + ' Пожалуйста, попробуйте еще раз.');
					});
				}
			});	
			const messageContainer = document.querySelector('.message__inner');
			const closeButton = document.getElementById('message__button');
			const expandButton = document.getElementById('expand__button');

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
				const popupInput = document.getElementById('popupInput');
				const statusSelect = document.getElementById('statusSelect');

				formType.value = type;
				formId.value = id;
				tableName.value = table;
				fieldName.value = field;

				if (table === 'my_orders_requests' && field === 'status') {
					statusSelect.innerHTML = `
				<option value="" disabled selected>Выберите статус</option>
				<option value="Собирается">Собирается</option>
				<option value="Отклонено">Отклонено</option>
				<option value="В обработке">В обработке</option>
			`;
					popupInput.style.display = 'none'; // Скрыть текстовое поле
					statusSelect.style.display = 'block'; // Показать селект
				} else if (table === 'drugs' && field === 'is_allowed') {
					statusSelect.innerHTML = `
				<option value="" disabled selected>Выберите статус</option>
				<option value="Одобрено">Одобрено</option>
				<option value="Отклонено">Отклонено</option>
				<option value="В обработке">В обработке</option>
			`;
					popupInput.style.display = 'none'; // Скрыть текстовое поле
					statusSelect.style.display = 'block'; // Показать селект
				} else {
					popupInput.style.display = 'block'; // Показать текстовое поле
					statusSelect.style.display = 'none'; // Скрыть селект
				}

				const popup = document.getElementById("popup");
				popup.classList.add("popup_open");
			}

			// Обработчик для закрытия попапа при клике вне формы
			document.addEventListener('click', function(event) {
				const popup = document.getElementById("popup");
				const popupContent = document.querySelector(".popup__content");

				if (popup.classList.contains("popup_open") && !popupContent.contains(event.target)) {
					popup.classList.remove("popup_open"); // Закрываем попап
				}
			});

			// Предотвращаем закрытие попапа при клике внутри формы
			document.querySelector('.popup__content').addEventListener('click', function(event) {
				event.stopPropagation();
			});

			// Обработчик для кнопки "Удалить"
			// document.querySelector('.message__button__close').addEventListener('click', function() {
			// 	document.querySelector('.message').classList.remove('message_open');
			// });

			if (closeButton) {
				closeButton.addEventListener('click', function() {
					messageContainer.style.display = 'none'; // Скрыть сообщения
					closeButton.style.display = 'none'; // Скрыть кнопку закрытия
					expandButton.style.display = 'block'; // Показать кнопку развернуть
				});
			}

			if (expandButton) {
				expandButton.addEventListener('click', function() {
					messageContainer.style.display = 'block'; // Показать сообщения
					closeButton.style.display = 'block'; // Показать кнопку закрытия
					expandButton.style.display = 'none'; // Скрыть кнопку развернуть
				});
			}

		} catch (error) {
			console.log(error)
		}



	}


);
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
	<!-- <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" /> -->
	<meta charset="UTF-8">
	<title>Управление Лекарствами</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
	<h1 class="title mb20 mt20">Управление Лекарствами</h1>

	<!-- <div id="imageContainer" class="image__fixed" style="background-image: url('data:image/jpeg;base64,<?php echo $profilePhoto; ?>');">
		<?php //if (!$profilePhoto): ?>
			Загрузить фотку
		<?php //endif; ?>
        <input type="file" id="fileInput" class="image__input" accept="image/*" style="display:none;">
    </div> -->
	<div id="imageContainer" class="image__fixed" style="background-image: url('data:image/jpeg;base64,<?php echo $profilePhoto; ?>');">
		<?php if (!$profilePhoto): ?>
			<span>Загрузить фотку</span>
		<?php else: ?>
			<span class="error-mess" style="display:none;">Ошибка загрузки аватара</span>
		<?php endif; ?>
		<input type="file" id="fileInput" class="image__input" accept="image/*" style="display:none;">
	</div>
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
	<!-- style="display:none;" Форма поиска лекарств-->
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form">
		<p class="title">Поиск</p>
		<input type="text" name="search_for_user" class="input" placeholder="Поиск..." value="<?php 
		echo htmlspecialchars($search_query_user); ?>">
		<button type="submit" name="search_btn_post" class="button">Поиск</button>
	</form>

	<!-- Форма добавления новой записи -->
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form" enctype="multipart/form-data">
		<p class="title">Добавление лекарств</p>
		<input type="text" name="name" class="input" placeholder="Название" required>
		<!-- <input type="text" name="manufacturer_name" class="input" placeholder="Производитель" required> -->

		<p class="subtitle">Производитель</p>
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
		<input type="file" name="medicinePhoto" class="input" accept="image/*" required>
		<!-- <input type="text" name="imgLink" class="input" placeholder="Ссылка на картинку"> -->
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
	<?php if (isset($_SESSION['medicine_images_error'])): ?>
		<div class="centr">
			<div class="image_error_message">
				✖ <?php echo htmlspecialchars($_SESSION['medicine_images_error']); ?>
				<?php unset($_SESSION['medicine_images_error']);?>
			</div>
		</div>
	<?php endif; ?>		
	<table>
		<thead>
			<tr>
				<th class="column-id"><a href="?order_by_user=id&order_dir_user=<?php echo htmlspecialchars($order_dir_user); ?>">IMG</a></th>
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
				<th class="column-status"><a
						href="?order_by_user=is_allowed&order_dir_user=<?php echo htmlspecialchars($order_dir_user); ?>">Статус</a>
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
				<td>
					<img class="table__img openImageUpdate" src="<?php echo htmlspecialchars(empty($row['medicinePhoto']) ? 'https://cms.imgworlds.com/assets/473cfc50-242c-46f8-80be-68b867e28919.jpg?key=home-gallery' : $row['medicinePhoto']); ?>"
						onerror='this.src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSN8eeyOk32x2hdhjf1kO4sFmM9WUcId9ayv-VNF4yd7PLL_9Bkl6CFMVvrBc9yYp_ZNow&usqp=CAU";'
						data-id='<?php echo htmlspecialchars($row['id']); ?>'>
						
						<!-- <img class="table__img openImageUpdate" src="..." data-image="<?php //echo htmlspecialchars($row['medicinePhoto']); ?>" /> -->
				</td>
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
					style="cursor:pointer">
					<?php echo htmlspecialchars($row['quantity']); ?></td>
				<td data-type="cost" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs_user" data-field="cost"
					style="cursor:pointer">
					<?php echo htmlspecialchars($row['cost']); ?></td>
				<td data-type="is_allowed" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs_user" data-field="is_allowed"
					style="cursor:pointer">
					<?php echo htmlspecialchars($row['is_allowed']); ?></td>
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
	<?php $rowSum = $supplier_analytics_sum_drugs->fetch_assoc();
	 $rowMedDrug = $supplier_analytics_med_drugs->fetch_assoc();?>
	<h1 class="extrasubtitle mb20-extrasubtitle mt20-extrasubtitle">Суммарная стоимость заказов:
		<?php echo htmlspecialchars(number_format($rowSum['sumCost'], 2, '.', ''))?></h1>
	<h1 class="extrasubtitle mb20-extrasubtitle mt20-extrasubtitle">Средний доход с продажи единицы:
		<?php echo htmlspecialchars(number_format($rowMedDrug['medCost'], 2, '.', ''))?></h1>

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
				<th class="column-status"><a
						href="?order_by_supplier=status&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Статус</a></th>
				<th class="column-status"><a
						href="?order_by_supplier=last_updated&order_dir_supplier=<?php echo htmlspecialchars($order_dir_supplier); ?>">Время
						обновления</a></th>
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
				<td data-id="<?php echo htmlspecialchars($row['id']); ?>" data-type="status" data-table="my_orders_requests" data-field="status"
					class="openPopup" style="cursor:pointer"><?php echo htmlspecialchars($row['status']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['last_updated']); ?></td>
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
			<select id="statusSelect" name="input" class="input" required>
				<option value="" disabled selected>Выберите статус</option>
				<option value="Собирается">Собирается</option>
				<option value="Отклонено">Отклонено</option>
				<option value="В обработке">В обработке</option>
			</select>
			<button type="submit" class="button popup__button">Сохранить</button>
		</form>
	</div>

	<!-- <div id="statusPopup" class="popup">
		<form method="POST" action="<?php //echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="popup__content">
			<input type="hidden" id="statusFormId" name="formId">
			<select id="statusSelect" name="input" class="input" required>
				<option value="" disabled selected>Выберите статус</option>
				<option value="approved">Собирается</option>
				<option value="rejected">Отклонено</option>
				<option value="pending">В обработке</option>
			</select>
			<button type="submit" class="button popup__button">Сохранить</button>
		</form>
	</div> -->
	<div id="vsplyvImage" class="vsplyvImage">
		<div class="vsplyvImage__content">
			<img id="currentImage" class="vsplyvImage__img" src="" alt="Текущая картинка" />
			<input type="file" id="newImageFile" class="input" accept="image/*" required>
			<button id="uploadImageButton" class="button vsplyvImage__button">Обновить изображение</button>
			<button id="closeImageButton" class="button vsplyvImage__button">Закрыть</button>
		</div>
	</div>

	<?php if ($orders_from_shoppers->num_rows > 0 || $drugs_add_requests_feedback->num_rows > 0): ?>
	<div class="message message_open">
		<div class="message__inner">
			<?php
			while ($row = $orders_from_shoppers->fetch_assoc()) {
				$name = htmlspecialchars($row['userName']);
				$date = htmlspecialchars($row['update_date']);
				$status = htmlspecialchars($row['status']);
				$drugName =  htmlspecialchars($row['drugName']);
				$manufacturerName =  htmlspecialchars($row['manufacturerName']);
				$cost = htmlspecialchars($row['cost']);
				$quantity =  htmlspecialchars($row['quantity']);
				$orderId = htmlspecialchars($row['id']);
				?>
			<div class="message__item" data-order-id="<?php echo $orderId; ?>">
				<p class="message__name">
					<span>Имя: </span><?php echo $name;?>
				</p>
				<p class="message__date">
					<span>Дата: </span><?php echo $date; ?>
				</p>
				<div class="message__text">
					<?php echo "$status. Покупатель заказал лекарство '$drugName' от производителя '$manufacturerName' в количестве $quantity штук на стоимость $cost у.е."; ?>
				</div>
				<div class="button-container">
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
						<input type="hidden" name="order_from_shopper_apply" value="<?php echo $orderId; ?>">
						<button type="submit" class="button message__button">Одобрить</button>
					</form>
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
						<input type="hidden" name="order_from_shopper_cancel" value="<?php echo $orderId; ?>">
						<button type="submit" class="button message__button">Отклонить</button>
					</form>
				</div>
			</div>
			<?php
			}
			?>
			<?php
			while ($row = $drugs_add_requests_feedback->fetch_assoc()) {
				$name = htmlspecialchars($row['admin_name']);
				$date = htmlspecialchars($row['update_date']);
				$status = htmlspecialchars($row['status']);
				$drugName =  htmlspecialchars($row['name']);
				$manufacturerName =  htmlspecialchars($row['manufacturer']);
				$cost = htmlspecialchars($row['price']);
				$quantity =  htmlspecialchars($row['quantity']);
				$orderId = htmlspecialchars($row['id']);
				?>
			<div class="message__item" data-order-id="<?php echo $orderId; ?>">
				<p class="message__name">
					<span>Имя: </span><?php echo $name; ?>
				</p>
				<p class="message__date">
					<span>Дата: </span><?php echo $date; ?>
				</p>
				<div class="message__text">
					<?php $money = $cost*$quantity; echo "Доступен результат по обработке поставки лекарства '$drugName' со стороны админитсрации от производителя '$manufacturerName' на сумму $money у.е. Результат: $status"; ?>
				</div>
				<div class="button-container">
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
						<input type="hidden" name="drug_request_status_viewed" value="<?php echo $orderId; ?>">
						<button type="submit" class="button message__button">Понятно</button>
					</form>
				</div>
			</div>
			<?php
			}
			?>
		</div>
		<div class="message__buttons">
			<!-- <button class="button message__button">Удалить</button> -->
			<button id="message__button" class="button message__button__close">Закрыть</button>
			<button id="expand__button" class="button message__button__expand" style="display:none;">Развернуть сообщения</button>
		</div>
	</div>
	<?php  endif;?>


</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
	try {
		const bfr = 400;
		document.getElementById('fileInput').addEventListener('change', function(event) {
		var file = event.target.files[0];
		if (file) {
			var fileSize = file.size;
			var maxSize = bfr * 1024 * 1024;
			if (fileSize > maxSize) {
				alert('Файл слишком большой. Максимальный размер: 10 MB.');
			}
		}
		});
		document.getElementById('newImageFile').addEventListener('change', function(event) {
		var file = event.target.files[0];
		if (file) {
			var fileSize = file.size;
			var maxSize = bfr * 1024 * 1024;
			if (fileSize > maxSize) {
				alert('Файл слишком большой. Максимальный размер: 10 MB.');
			}
		}
		});
		const imageContainer1 = document.getElementById('imageContainer');
		const profilePhoto = '<?php echo $profilePhoto; ?>';

		// Проверка на битое изображение
		const img = new Image();
		img.src = 'data:image/jpeg;base64,' + profilePhoto;

		img.onerror = function() {
			// Если изображение не загружается, показываем сообщение об ошибке
			document.querySelector('.error-mess').style.display = 'block';
			imageContainer1.style.backgroundImage = 'none'; // опционально убираем фон
		};

		const vsplyvImage = document.getElementById('vsplyvImage');
		const newImageLinkInput = document.getElementById('newImageLink');
		const currentImage = document.getElementById('currentImage');
		const updateImageButton = document.getElementById('updateImageButton');
		const closeImageButton = document.getElementById('closeImageButton');
		baseDrugImage  = "";
		let currentID = 0;
		// Функция для открытия vsplyvImage
		function openVsplyvImage(imageSrc, id) {
			currentImage.src = imageSrc;
			currentID = id;
			vsplyvImage.classList.add('open');
		}

		// Обработчик клика для открытия vsplyvImage при необходимости
		document.querySelectorAll('.openImageUpdate').forEach((element) => {
			element.addEventListener('click', function() {
				const imageSrc = element.src; // Get the image source
				const id = element.dataset.id; // Get the ID
				openVsplyvImage(imageSrc, id);
			});
		});

		// Обработчик для обновления ссылки на изображение
		uploadImageButton.addEventListener('click', function() {
			const fileInput = document.getElementById('newImageFile');
			const file = fileInput.files[0];

			if (file) {
				const formData = new FormData();
				formData.append('medicinePhoto', file);
				formData.append('id', currentID);

				fetch('photoAPI/medicinePhotoUpd.php', {
					method: 'POST',
					body: formData
				})
				.then(response => response.json())
				.then(data => {
					if (data.status === 'success') {
						currentImage.src = `images/${data.fileName}`;
						const newImageUrl = `images/${data.fileName}`;
                        const imageElement = document.querySelector(`img[data-id='${currentID}']`);
                        if (imageElement) {
                            imageElement.src = newImageUrl;
                        }
						alert('Изображение обновлено успешно.');
						//vsplyvImage.classList.remove('open');
					} else {
						alert(data.message);
					}
				})
				.catch(error => {
					console.error('Ошибка:', error);
				});
			} else {
				alert('Пожалуйста, выберите изображение.');
			}
		});

		// Обработчик для закрытия vsplyvImage
		closeImageButton.addEventListener('click', function() {
			vsplyvImage.classList.remove('open');
		});
		// Закрытие vsplyvImage при клике вне содержимого
		vsplyvImage.addEventListener('click', function(event) {
			if (event.target === vsplyvImage) {
				vsplyvImage.classList.remove('open');
			}
		});

		//console.log(2)
		const imageContainer = document.getElementById('imageContainer');
		const fileInput = document.getElementById('fileInput');

		// Добавляем обработчик клика по div
		imageContainer.addEventListener('click', () => {
			fileInput.click(); // Открывает диалоговое окно выбора файла
		});
		
		// Обработчик для загрузки файла
		fileInput.addEventListener('change', (event) => {
			const file = event.target.files[0];
			let res = true;
			if (file) {
				var fileSize = file.size;
				var maxSize = bfr * 1024 * 1024;
				if (fileSize > maxSize) {
					res = false;
				}
			}
			if (file && res) {
				const formData = new FormData();
				formData.append('profilePhoto', file);
				const reader = new FileReader();

				fetch('photoAPI/userProfilePhoto.php', {
					method: 'POST',
					body: formData
				})
				.then(response => {
					if (!response.ok) {
						throw new Error('Сетевая ошибка: ответ не был получен');
					}
					return response.json(); 
				})
				.then(data => {
					if (data.status === 'success') {
						// Только после успешного ответа обновляем изображение
						reader.onload = function(e) {
							imageContainer.style.backgroundImage = `url(${e.target.result})`;
							imageContainer.textContent = ''; 
						};
						reader.readAsDataURL(file); // Чтение файла для отображения после успешного ответа
					} else {
						// Если статус не success, выводим сообщение об ошибке
						alert(data.message);
						console.error(data.message);
					}
				})
				.catch(error => {
					const errorMessage = 'Ошибка при загрузке изображения: ' + error.message; // Используем error.message для более ясного сообщения
					console.error('Ошибка:', error);
					alert(errorMessage + ' Пожалуйста, попробуйте еще раз.');
				});
			}
		});

		const messageContainer = document.querySelector('.message__inner');
		const closeButton = document.getElementById('message__button');
		const expandButton = document.getElementById('expand__button');

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
			const popupInput = document.getElementById('popupInput');
			const statusSelect = document.getElementById('statusSelect');

			formType.value = type;
			formId.value = id;
			tableName.value = table;
			fieldName.value = field;

			if (field === 'status') {
				popupInput.style.display = 'none'; // Скрыть текстовое поле
				statusSelect.style.display = 'block'; // Показать селект
			} else {
				popupInput.style.display = 'block'; // Показать текстовое поле
				statusSelect.style.display = 'none'; // Скрыть селект
			}

			const popup = document.getElementById("popup");
			popup.classList.add("popup_open");
		}

		// Обработчик для закрытия попапа при клике вне формы
		document.addEventListener('click', function(event) {
			const popup = document.getElementById("popup");
			const popupContent = document.querySelector(".popup__content");

			if (popup.classList.contains("popup_open") && !popupContent.contains(event.target)) {
				popup.classList.remove("popup_open"); // Закрываем попап
			}
		});

		// Предотвращаем закрытие попапа при клике внутри формы
		document.querySelector('.popup__content').addEventListener('click', function(event) {
			event.stopPropagation();
		});

		// Обработчик для кнопки "Удалить"
		// document.querySelector('.message__button__close').addEventListener('click', function() {
		// 	document.querySelector('.message').classList.remove('message_open');
		// });



		if (closeButton) {
			closeButton.addEventListener('click', function() {
				messageContainer.style.display = 'none'; // Скрыть сообщения
				closeButton.style.display = 'none'; // Скрыть кнопку закрытия
				expandButton.style.display = 'block'; // Показать кнопку развернуть
			});
		}

		if (expandButton) {
			expandButton.addEventListener('click', function() {
				messageContainer.style.display = 'block'; // Показать сообщения
				closeButton.style.display = 'block'; // Показать кнопку закрытия
				expandButton.style.display = 'none'; // Скрыть кнопку развернуть
			});
		}


	} catch (error) {
		console.log(error)
	}
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
	<!-- <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" /> -->
	<meta charset="UTF-8">
	<title>Добро пожаловать домой, Сиджей</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
	<h1 class="title mb20 mt20">Закупка лекарствами</h1>
	<!-- <div id="imageContainer" class="image__fixed" style="background-image: url('data:image/jpeg;base64,<?php echo $profilePhoto; ?>');">
		<?php //if (!$profilePhoto): ?>
			Загрузить фотку
		<?php //endif; ?>
        <input type="file" id="fileInput" class="image__input" accept="image/*" style="display:none;">
    </div> -->
	<div id="imageContainer" class="image__fixed" style="background-image: url('data:image/jpeg;base64,<?php echo $profilePhoto; ?>');">
		<?php if (!$profilePhoto): ?>
			<span>Загрузить фотку</span>
		<?php else: ?>
			<span class="error-mess" style="display:none;">Ошибка загрузки аватара</span>
		<?php endif; ?>
		<input type="file" id="fileInput" class="image__input" accept="image/*" style="display:none;">
	</div>
	<form method="POST">


		<button type="submit" name="logout" class="button button__fixed button__fixed_right">
			Выйти
		</button>
	</form>

	<a href="index.php" class="button button__fixed">
		На главную
	</a>

	<form method="POST" style="display:none;">
		<button type="submit" name="drop_res" class="button button_not_fixed button__fixed_colhoz">
			Убрать рекомендации
		</button>
	</form>
	<!-- Форма поиска лекарств style="display:none;" -->
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form">
		<p class="title">Поиск</p>
		<input type="text" name="search_for_shopper" class="input" placeholder="Поиск..."
			value="<?php echo htmlspecialchars($search_query_shopper); ?>">
		<button type="submit" name="search_us_btn" class="button">Поиск</button>
		<?php if (isset($_SESSION['error_message'])): ?>
		<div class="auth__message">
			✖ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
			<?php unset($_SESSION['error_message']); ?>
		</div>
		<?php endif; ?>
	</form>

	<!-- Таблица с данными о лекарствах -->
	<h1 class="title mb20 mt20">Все лекарства</h1>
	<?php if (isset($_SESSION['medicine_images_error'])): ?>
		<div class="centr">
			<div class="image_error_message">
				✖ <?php echo htmlspecialchars($_SESSION['medicine_images_error']); ?>
				<?php unset($_SESSION['medicine_images_error']);?>
			</div>
		</div>
	<?php endif; ?>
	<table class="tbody_drugs_shopper">
		<thead>
			<tr>
				<th class="column-id"><a href="?order_by_user=id&order_dir_user=<?php echo htmlspecialchars($order_dir_user); ?>">IMG</a></th>
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
		<tbody class="tbody_drugs_shopper">
			<?php
			if (isset($result_shopper)) {
				while ($row = $result_shopper->fetch_assoc()) {
					?>
			<tr>
				<td>
					<img class="table__img" src="<?php echo htmlspecialchars(empty($row['medicinePhoto']) ? 'https://cms.imgworlds.com/assets/473cfc50-242c-46f8-80be-68b867e28919.jpg?key=home-gallery' : $row['medicinePhoto']); ?>"
						onerror='this.src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSN8eeyOk32x2hdhjf1kO4sFmM9WUcId9ayv-VNF4yd7PLL_9Bkl6CFMVvrBc9yYp_ZNow&usqp=CAU";'
						data-id='<?php echo htmlspecialchars($row['id']); ?>'>
						
						<!-- <img class="table__img openImageUpdate" src="..." data-image="<?php //echo htmlspecialchars($row['medicinePhoto']); ?>" /> -->
				</td>
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
	<?php $rowSum = $user_analytics_sum_drugs->fetch_assoc();
	 $rowMedDrug = $user_analytics_med_drugs->fetch_assoc();?>
	<h1 class="extrasubtitle mb20-extrasubtitle mt20-extrasubtitle">Суммарные затраты:
		<?php echo htmlspecialchars(number_format($rowSum['sumCost'], 2, '.', ''))?></h1>
	<h1 class="extrasubtitle mb20-extrasubtitle mt20-extrasubtitle">Средние затраты на единицу товара:
		<?php echo htmlspecialchars(number_format($rowMedDrug['medCost'], 2, '.', ''))?></h1>
	<div class="button-container" style="margin: 0 auto;">
		<form style="margin-bottom: 12px" class="form__checkbox" method="POST" id="checkboxForm">
			<input type="hidden" name="action" value="delete"> <!-- Добавляем скрытое поле для действия удаления -->
			<button class="button form__button" type="button" id="processButton">Удалить</button>
		</form>

		<form style="display: none;" style="margin-bottom: 12px" class="form__checkbox" method="POST" id="updateForm">
			<input type="hidden" name="action" value="update"> <!-- Добавляем скрытое поле для действия обновления -->
			<button class="button form__button" type="button" id="updateButton">Оформить</button>
		</form>
	</div>
	<table class="table_cart">
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
				<th class="column-status"><a
						href="?order_by_shopper_cart=status&order_dir_shopper=<?php echo htmlspecialchars($order_dir_shopper); ?>">Статус</a>
				</th>
				<th class="column-status"><a
						href="?order_by_shopper_cart=last_updated&order_dir_shopper=<?php echo htmlspecialchars($order_dir_shopper); ?>">Время
						обновления</a>
				</th>
				<th class="column-quantity"><a
						href="?order_by_shopper=percent&order_dir_shopper=<?php echo htmlspecialchars($order_dir_shopper); ?>">Процент</a></th>
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
					<input type="checkbox" value="<?php echo htmlspecialchars($row['id']); ?>" id="<?php echo $checkbox_id; ?>" name="check_all[]">
					<label for="<?php echo $checkbox_id; ?>"><?php echo htmlspecialchars($row['name']); ?></label>
				</td>

				<td style="cursor:pointer"><?php echo htmlspecialchars($row['manufacturer']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['supplier']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['price']); ?></td>
				<td style="cursor:pointer" data-type="quantity" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-table="drugs_shopper_cart"
					data-field="quantity" class="openPopup"><?php echo htmlspecialchars($row['quantity']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['cost']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['status']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['last_updated']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['percent']); ?></td>
				<td>
					<!-- Внутренняя форма удаления -->
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="display:inline;" class="deleteForm">
						<input type="hidden" name="delete_shopper_drug" class="input" value="<?php echo htmlspecialchars($row['id']); ?>">
						<button type="submit" class="button deleteButton"
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

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		
		const bfr = 400;
		document.getElementById('fileInput').addEventListener('change', function(event) {
		var file = event.target.files[0];
		if (file) {
			var fileSize = file.size;
			var maxSize = bfr * 1024 * 1024;
			if (fileSize > maxSize) {
				alert('Файл слишком большой. Максимальный размер: 10 MB.');
			}
		}
		});
		document.getElementById('newImageFile').addEventListener('change', function(event) {
		var file = event.target.files[0];
		if (file) {
			var fileSize = file.size;
			var maxSize = bfr * 1024 * 1024;
			if (fileSize > maxSize) {
				alert('Файл слишком большой. Максимальный размер: 10 MB.');
			}
		}
		});

		const imageContainer1 = document.getElementById('imageContainer');
		const profilePhoto = '<?php echo $profilePhoto; ?>';

		// Проверка на битое изображение
		const img = new Image();
		img.src = 'data:image/jpeg;base64,' + profilePhoto;

		img.onerror = function() {
			// Если изображение не загружается, показываем сообщение об ошибке
			document.querySelector('.error-mess').style.display = 'block';
			imageContainer1.style.backgroundImage = 'none'; // опционально убираем фон
		};

		console.log("Script loaded and DOM is ready");

		// Получаем кнопку для удаления и форму
		const processButton = document.getElementById('processButton');
		const checkboxForm = document.getElementById('checkboxForm');

		// Обработчик на кнопку "Удалить"
		processButton.addEventListener('click', function() {
			console.log("Delete button clicked");

			// Удаляем только динамически добавленные скрытые инпуты для чекбоксов
			const hiddenInputs = checkboxForm.querySelectorAll('input[name="check_all[]"]');
			hiddenInputs.forEach(input => input.remove());

			// Получаем все отмеченные чекбоксы
			const checkedCheckboxes = document.querySelectorAll('input[name="check_all[]"]:checked');

			// Если нет выбранных чекбоксов
			if (checkedCheckboxes.length === 0) {
				alert('Пожалуйста, выберите хотя бы один элемент для удаления.');
				return;
			}

			console.log("Checkboxes selected:", checkedCheckboxes);

			// Для каждого отмеченного чекбокса создаем скрытый инпут внутри формы
			checkedCheckboxes.forEach(checkbox => {
				console.log("Processing checkbox with value:", checkbox.value);
				const hiddenInput = document.createElement('input');
				hiddenInput.type = 'hidden';
				hiddenInput.name = 'check_all[]';
				hiddenInput.value = checkbox.value;
				checkboxForm.appendChild(hiddenInput); // Добавляем скрытый инпут в форму
			});

			// Программно отправляем форму
			checkboxForm.submit();
		});
	});
	</script>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		console.log("Script loaded and DOM is ready");

		// Получаем кнопку для удаления и форму
		const processButton = document.getElementById('updateButton');
		const updateForm = document.getElementById('updateForm');

		// Обработчик на кнопку "Удалить"
		processButton.addEventListener('click', function() {
			console.log("Delete button clicked");

			// Удаляем только динамически добавленные скрытые инпуты для чекбоксов
			const hiddenInputs = updateForm.querySelectorAll('input[name="check_all[]"]');
			hiddenInputs.forEach(input => input.remove());

			// Получаем все отмеченные чекбоксы
			const checkedCheckboxes = document.querySelectorAll('input[name="check_all[]"]:checked');

			// Если нет выбранных чекбоксов
			if (checkedCheckboxes.length === 0) {
				alert('Пожалуйста, выберите хотя бы один элемент для оформления.');
				return;
			}

			console.log("Checkboxes selected:", checkedCheckboxes);

			// Для каждого отмеченного чекбокса создаем скрытый инпут внутри формы
			checkedCheckboxes.forEach(checkbox => {
				console.log("Processing checkbox with value:", checkbox.value);
				const hiddenInput = document.createElement('input');
				hiddenInput.type = 'hidden';
				hiddenInput.name = 'check_all[]';
				hiddenInput.value = checkbox.value;
				updateForm.appendChild(hiddenInput); // Добавляем скрытый инпут в форму
			});

			// Программно отправляем форму
			updateForm.submit();
		});
	});
	</script>


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
	<?php if ($orders_shopper_feedback->num_rows > 0): ?>
	<div class="message message_open">
		<div class="message__inner">
			<?php
			while ($row = $orders_shopper_feedback->fetch_assoc()) {
				$name = htmlspecialchars($row['providerName']);
				$date = htmlspecialchars($row['update_date']);
				$status = htmlspecialchars($row['status']);
				$drugName =  htmlspecialchars($row['drugName']);
				$manufacturerName =  htmlspecialchars($row['manufacturerName']);
				$cost = htmlspecialchars($row['cost']);
				$quantity =  htmlspecialchars($row['quantity']);
				$orderId = htmlspecialchars($row['id']);
				?>
			<div class="message__item" data-order-id="<?php echo $orderId; ?>">
				<p class="message__name">
					<span>Имя: </span><?php echo $name; ?>
				</p>
				<p class="message__date">
					<span>Дата: </span><?php echo $date; ?>
				</p>
				<div class="message__text">
					<?php echo "Доступен результат по заказу лекарства '$drugName' от производителя '$manufacturerName' на сумму $cost у.е. Результат: $status"; ?>
				</div>
				<div class="button-container">
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
						<input type="hidden" name="order_shopper_viewed" value="<?php echo $orderId; ?>">
						<button type="submit" class="button message__button">Понятно </button>
					</form>
				</div>
			</div>
			<?php
			}
			?>
		</div>
		<div class="message__buttons">
			<button id="message__button" class="button message__button__close">Закрыть</button>
			<button id="expand__button" class="button message__button__expand" style="display:none;">Развернуть сообщения</button>
		</div>
	</div>
	<?php  endif;?>

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
	try {

		const imageContainer = document.getElementById('imageContainer');
		const fileInput = document.getElementById('fileInput');

		// Добавляем обработчик клика по div
		imageContainer.addEventListener('click', () => {
			fileInput.click(); // Открывает диалоговое окно выбора файла
		});
		
		// Обработчик для загрузки файла
		fileInput.addEventListener('change', (event) => {
			const file = event.target.files[0];
			let res = true;
			if (file) {
				var fileSize = file.size;
				var maxSize = bfr * 1024 * 1024;
				if (fileSize > maxSize) {
					res = false;
				}
			}
			if (file && res) {
				const formData = new FormData();
				formData.append('profilePhoto', file);
				const reader = new FileReader();

				fetch('photoAPI/userProfilePhoto.php', {
					method: 'POST',
					body: formData
				})
				.then(response => {
					if (!response.ok) {
						throw new Error('Сетевая ошибка: ответ не был получен');
					}
					return response.json(); 
				})
				.then(data => {
					if (data.status === 'success') {
						// Только после успешного ответа обновляем изображение
						reader.onload = function(e) {
							imageContainer.style.backgroundImage = `url(${e.target.result})`;
							imageContainer.textContent = ''; 
						};
						reader.readAsDataURL(file); // Чтение файла для отображения после успешного ответа
					} else {
						// Если статус не success, выводим сообщение об ошибке
						alert(data.message);
						console.error(data.message);
					}
				})
				.catch(error => {
					const errorMessage = 'Ошибка при загрузке изображения: ' + error.message; // Используем error.message для более ясного сообщения
					console.error('Ошибка:', error);
					alert(errorMessage + ' Пожалуйста, попробуйте еще раз.');
				});
			}
		});
		console.log(1)
		

		const messageContainer = document.querySelector('.message__inner');
		const closeButton = document.getElementById('message__button');
		const expandButton = document.getElementById('expand__button');

		if (closeButton) {
			closeButton.addEventListener('click', function() {
				messageContainer.style.display = 'none'; // Скрыть сообщения
				closeButton.style.display = 'none'; // Скрыть кнопку закрытия
				expandButton.style.display = 'block'; // Показать кнопку развернуть
			});
		}

		if (expandButton) {
			expandButton.addEventListener('click', function() {
				messageContainer.style.display = 'block'; // Показать сообщения
				closeButton.style.display = 'block'; // Показать кнопку закрытия
				expandButton.style.display = 'none'; // Скрыть кнопку развернуть
			});
		}
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


		// // Обработчик для кнопки "Закрыть"
		// document.querySelector('.message__button__close').addEventListener('click', function() {
		// 	document.querySelector('.message').classList.remove('message_open');
		// });


	} catch (error) {
		console.log(error)
	}
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