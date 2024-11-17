<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css" />
    <title>Color Picker Popup</title>
</head>
<body>

<button onclick="openPopup()">Настройки цвета таблицы</button>

<div id="popup" class="popup">
    <form id="colorForm" class="popup__content__cookie" onsubmit="saveColor(event)">
        <h2>Выберите цвет таблицы</h2>
        <input type="color" id="colorInput" name="color" required>
        <button type="submit" class="button popup__button">Сохранить</button>
        <h3>История изменений цветов</h3>
        <ul id="colorHistory"></ul>
    </form>
</div>

<script>
    const popup = document.getElementById('popup');
    const colorHistoryList = document.getElementById('colorHistory');

    function loadInitialColor() {
        fetch('cookieAPI/color_handler.php')
            .then(response => response.json())
            .then(data => {
                if (data.firstColor) {
                    applyColor(data.firstColor); 
                }
                loadColorHistory();
            });
    }

    function openPopup() {
        popup.style.display = 'flex';
        loadColorHistory();
    }

    function closePopup() {
        popup.style.display = 'none';
    }

    function saveColor(event) {
        event.preventDefault();
        const color = document.getElementById('colorInput').value;

        applyColor(color);

        fetch('cookieAPI/color_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ color })
            })
            .then(response => response.json())
            .then(() => {
                closePopup();
                loadColorHistory();
            });
    }

    function loadColorHistory() {
        fetch('cookieAPI/color_handler.php')
            .then(response => response.json())
            .then(data => {
                colorHistoryList.innerHTML = data.history.map(color => 
                    `<li style="color:${color}; cursor: pointer;" onclick="applyColor('${color}'); saveColorFromHistory('${color}')">${color}</li>`
                ).join('');
            })
            .catch(error => console.error('Ошибка при загрузке истории цветов:', error));
    }

    function applyColor(color) {
        document.getElementById('table').style.backgroundColor = color;
    }

    function saveColorFromHistory(color) {
        applyColor(color);
        fetch('cookieAPI/color_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ color })
        });
        closePopup();
    }

    window.onclick = function(event) {
        if (event.target === popup) {
            closePopup();
        }
    };

    loadInitialColor();
</script>

<table id="table" style="width: 100%; border: 1px solid #000;">
    <tr>
        <th>Заголовок</th>
        <th>Данные</th>
    </tr>
    <tr>
        <td>Пример</td>
        <td>Данные</td>
    </tr>
</table>

</body>
</html>