<?php
include "tables_settings_management.php"
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>Настройка весов таблицы лекарств</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<style>
	body {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 100vh;
		margin: 0;
	}
	</style>
</head>

<body>
    <a href="index.php" class="button button__fixed">
		На главную
	</a>
	<a href="table.php" class="button button__fixed button__fixed_back_to_adm">
		К таблицам
	</a>
	<div class="menu">
		<div class="menu__item menu__item_left">
			<p class="menu__title">Действия</p>
			<!-- <label for="role" class="menu-label" name="nameUser">Login: </label> -->
			<select id="role" class="menu__select" name="userSelector" onchange="updateSettings()">
				<option value="">Выберите пользователя</option>
			</select>

			<p class="menu__label">ИД: <span id="userID"></span></p>
			<p class="menu__label">Роль: <span id="userType"></span></p>
			<p class="menu__label">Дата обновления: <span id="lastUpdated"></span></p>

			<button class="button" id="updateSettingsButton" style="margin-top: 8px;">Сменить коэффициенты</button>
		</div>
		<div class="menu__item">
			<div class="menu__container">
				<p class="menu__title">Настройки</p>
				<div class="menu__range">
					<p class="menu__range-value">Количество в корзине: <span class="value-label">0.50</span></p>
					<input type="range" min="0" max="1" step="0.01" value="0.5" class="setting-slider" id="setting1" name="C_quantity_in_orders">
				</div>
				<div class="menu__range">
					<p class="menu__range-value">Частота покупок (частная): <span class="value-label">0.50</span></p>
					<input type="range" min="0" max="1" step="0.01" value="0.5" class="setting-slider" id="setting2" name="C_frequency_of_use">
				</div>
				<div class="menu__range">
					<p class="menu__range-value">Доступность лекарства: <span class="value-label">0.50</span></p>
					<input type="range" min="0" max="1" step="0.01" value="0.5" class="setting-slider" id="setting3" name="C_availability_in_stock">
				</div>
				<div class="menu__range">
					<p class="menu__range-value">Относительная цена: <span class="value-label">0.50</span></p>
					<input type="range" min="0" max="1" step="0.01" value="0.5" class="setting-slider" id="setting4" name="C_comparative_price">
				</div>
				<div class="menu__range">
					<p class="menu__range-value">Частота покупок (общая): <span class="value-label">0.50</span></p>
					<input type="range" min="0" max="1" step="0.01" value="0.5" class="setting-slider" id="setting5" name="C_demand_for_medicine">
				</div>
				<div class="menu__range">
					<p class="menu__range-value">Вовлеченность производителей: <span class="value-label">0.50</span></p>
					<input type="range" min="0" max="1" step="0.01" value="0.5" class="setting-slider" id="setting6" name="C_manufacturer">
				</div>
			</div>
		</div>
		<div class="menu__item menu__item_right">
			<p class="menu__title">Старые значения</p>
			<ul class="menu__list">
				<li>Количество в корзине: <span class="old-value" name="Old_C_quantity_in_orders">0.50</span></li>
				<li>Частота покупок (частная): <span class="old-value" name="Old_C_frequency_of_use">0.50</span></li>
				<li>Доступность лекарства: <span class="old-value" name="Old_C_availability_in_stock">0.50</span></li>
				<li>Относительная цена: <span class="old-value" name="Old_C_comparative_price">0.50</span></li>
				<li>Частота покупок (общая): <span class="old-value" name="Old_C_demand_for_medicine">0.50</span></li>
				<li>Вовлеченность производителей: <span class="old-value" name="Old_C_manufacturer">0.50</span></li>
			</ul>
		</div>
	</div>
	<script>
	const sliders = document.querySelectorAll('.setting-slider');
	const oldValues = document.querySelectorAll('.old-value');

	sliders.forEach((slider, index) => {
		slider.addEventListener('input', () => {
			const value = parseFloat(slider.value); // Преобразование значения в число
			const valueLabelElement = slider.parentElement.querySelector('.value-label');
			valueLabelElement.textContent = value.toFixed(2); // Используем toFixed на числе
		});
	});

	document.addEventListener('DOMContentLoaded', () => {
		fetch('medicineweightsAPI/get_users.php') // API для получения списка пользователей
			.then(response => response.json())
			.then(users => {
				const roleSelect = document.getElementById('role');
				users.forEach(user => {
					const option = document.createElement('option');
					option.value = user.id; // Убедитесь, что id соответствует user_id
					option.textContent = user.name; // Отображаем имя пользователя
					roleSelect.appendChild(option);
				});
			})
			.catch(error => console.error('Error fetching users:', error));
	});

	function updateSettings() {
		const userId = document.getElementById('role').value;

		if (!userId) {
			oldValues.forEach(oldValue => oldValue.textContent = 'Настройка не проводилась');
			sliders.forEach(slider => slider.value = 0.5);
			document.getElementById('userID').textContent = 'Неизвестно';
			document.getElementById('userType').textContent = 'Неизвестно';
			document.getElementById('lastUpdated').textContent = 'Не обновлено';
			return;
		}

		fetch(`medicineweightsAPI/get_user_settings.php?user_id=${userId}`)
			.then(response => response.json())
			.then(data => {
				if (data) {
					oldValues[0].textContent = data.old_C_quantity_in_orders || '0.00';
					oldValues[1].textContent = data.old_C_frequency_of_use || '0.00';
					oldValues[2].textContent = data.old_C_availability_in_stock || '0.00';
					oldValues[3].textContent = data.old_C_comparative_price || '0.00';
					oldValues[4].textContent = data.old_C_demand_for_medicine || '0.00';
					oldValues[5].textContent = data.old_C_manufacturer || '0.00';

					sliders[0].value = data.C_quantity_in_orders || 0.5;
					sliders[1].value = data.C_frequency_of_use || 0.5;
					sliders[2].value = data.C_availability_in_stock || 0.5;
					sliders[3].value = data.C_comparative_price || 0.5;
					sliders[4].value = data.C_demand_for_medicine || 0.5;
					sliders[5].value = data.C_manufacturer || 0.5;

					sliders.forEach(slider => {
						const valueLabelElement = slider.parentElement.querySelector('.value-label');
						valueLabelElement.textContent = parseFloat(slider.value).toFixed(2); // Преобразование в число и форматирование
					});

					document.getElementById('userID').textContent = data.userID || 'Неизвестно';
					const userTypeMap = {
						1: 'Администратор',
						0: 'Поставщик',
						2: 'Покупатель'
					};
					document.getElementById('userType').textContent = userTypeMap[data.userType] || 'Неизвестно';
					document.getElementById('lastUpdated').textContent = data.lastUpdated ? new Date(data.lastUpdated).toLocaleDateString('ru-RU') :
						'Не обновлено';
				} else {
					oldValues.forEach(oldValue => oldValue.textContent = 'Настройка не проводилась');
					sliders.forEach(slider => slider.value = 0.5);
					document.getElementById('userID').textContent = 'Неизвестно';
					document.getElementById('userType').textContent = 'Неизвестно';
					document.getElementById('lastUpdated').textContent = 'Не обновлено';
				}
			})
			.catch(error => console.error('Error fetching user settings:', error));
	}

	document.getElementById('updateSettingsButton').addEventListener('click', () => {
		const userId = document.getElementById('role').value;

		if (!userId) {
			alert('Пожалуйста, выберите пользователя.');
			return;
		}

		const confirm = window.confirm('Вы уверены, что хотите обновить настройки?');

		if (confirm) {
			const settings = Array.from(sliders).map(slider => parseFloat(slider.value)); // Преобразование значений в числа

			fetch('medicineweightsAPI/update_settings.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({
						userId,
						settings
					})
				})
				.then(response => {
					if (!response.ok) {
						throw new Error('Ответ сети был неудовлетворительным');
					}
					return response.json();
				})
				.then(data => {
					if (data.success) {
						alert('Настройки успешно обновлены!');
					} else {
						alert('Ошибка при обновлении настроек: ' + (data.error || 'Неизвестная ошибка.'));
					}
				})
				.catch(error => console.error('Ошибка при обновлении настроек:', error));
		}
	});
	</script>
</body>

</html>