<?php
include 'sessionConf.php';
session_start(); // Начинаем сессию
$_SESSION['sql_error_message'] = 'Ошибка базы данных:';
$_SESSION['server_error_message'] = 'Ошибка сервера';
$_SESSION['server_conn_error'] = false;

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
include 'activity_log_manager.php';
?>
<?php
if($_SESSION["user_type"] == 1):
?><!-- Админ интерфейс-->
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Системный лог событий</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<a href="table.php" class="button button__fixed">
		Назад
</a>

<form method="POST" action="">
    <h1 class="title mb20 mt20">События</h1>
    <div class="filter-container">
        <input type="text" name="event_id" placeholder="ИД события" value="<?php echo htmlspecialchars($event_id); ?>" />
        <input type="text" name="actor_name" placeholder="Действующее лицо" value="<?php echo htmlspecialchars($actor_name); ?>" />
        <input type="date" name="start_date" placeholder="Дата с" value="<?php echo htmlspecialchars($start_date); ?>" />
        <input type="date" name="end_date" placeholder="Дата по" value="<?php echo htmlspecialchars($end_date); ?>" />
        <input type="text" name="action" placeholder="Действие" value="<?php echo htmlspecialchars($action); ?>" />
        <button type="submit" class="button">Поиск</button>
    </div>
</form>
<form method="POST" style="display: none;" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
    <input type="hidden" name="exportExcel" class="input" value="">
    <button type="submit" class="button" >Экспорт в excel</button>					
</form>
<?php if (isset($_SESSION['error_message'])): ?>
	<div class="auth__message">
			✖ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
			<?php unset($_SESSION['error_message']); ?>
	</div>	
<?php endif; ?>

	<table>
		<thead>
			<tr>
				<th class="column-id"><a
						href="?order_by=id&order_dir=<?php echo htmlspecialchars($order_dir); ?>">ИД события</a></th>
				<th class="column-action-login"><a
						href="?order_by=name&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Действующее лицо (логин)</a></th>
				<th class="column-action-dt"><a
						href="?order_by=action_datetime&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Дата-время</a></th>
				<th class="column-action-descr"><a
						href="?order_by=action&order_dir=<?php echo htmlspecialchars($order_dir); ?>">Действие</a>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					?>
			<tr>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['id']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['name']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['action_datetime']); ?></td>
				<td style="cursor:pointer"><?php echo htmlspecialchars($row['action']); ?></td>
			</tr>
			<?php
				}
			} else {
				echo '<tr><td colspan="4">Нет данных для отображения</td></tr>';
			}
			?>
		</tbody>
	</table>
</body>
<?php
else:
    header('Location: index.php');
endif;
// Закрываем соединение
if (isset($conn)) {
    $conn->close();
}
?>