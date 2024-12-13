<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Сравнение методов сортировки</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Сравнение методов сортировки</h1>
    <div class="controls">
        <input type="text" id="searchInput" placeholder="Поиск...">
        <button onclick="refreshData()">Обновить данные</button>
    </div>
    <div class="container">
        <div class="table-container">
            <h2>SQL Сортировка</h2>
            <div id="sqlExecutionTime" class="execution-time"></div>
            <table id="sqlTable">
                <thead>
                    <tr>
                        <th onclick="sortSqlTable('name')">Название</th>
                        <th onclick="sortSqlTable('price')">Цена</th>
                        <th onclick="sortSqlTable('quantity')">Количество</th>
                        <th onclick="sortSqlTable('manufacturer')">Производитель</th>
                        <th onclick="sortSqlTable('supplier')">Поставщик</th>
                        <th onclick="sortSqlTable('order_count')">Количество заказов</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="table-container">
            <h2>JavaScript Сортировка</h2>
            <div id="jsExecutionTime" class="execution-time"></div>
            <table id="jsTable">
                <thead>
                    <tr>
                        <th onclick="sortJsTable('name')">Название</th>
                        <th onclick="sortJsTable('price')">Цена</th>
                        <th onclick="sortJsTable('quantity')">Количество</th>
                        <th onclick="sortJsTable('manufacturer')">Производитель</th>
                        <th onclick="sortJsTable('supplier')">Поставщик</th>
                        <th onclick="sortJsTable('order_count')">Количество заказов</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <script>
        let jsData = [];
        let sqlSortColumn = 'name';
        let sqlSortOrder = 'ASC';
        let jsSortColumn = 'name';
        let jsSortOrder = 'ASC';

        function refreshData() {
            const searchTerm = document.getElementById('searchInput').value;
            
            // SQL Sort с учетом времени сети
            const sqlStart = performance.now();
            fetch(`sql_sort.php?sort=${sqlSortColumn}&order=${sqlSortOrder}&search=${searchTerm}`)
                .then(response => response.json())
                .then(data => {
                    const sqlEnd = performance.now();
                    const totalSqlTime = (sqlEnd - sqlStart).toFixed(2);
                    updateTable('sqlTable', data.data);
                    document.getElementById('sqlExecutionTime').textContent = 
                        `Время выполнения: ${data.executionTime.toFixed(2)} мс (SQL) + ${(totalSqlTime - data.executionTime).toFixed(2)} мс (сеть) = ${totalSqlTime} мс`;
                });

            // JS Sort
            const jsStart = performance.now();
            fetch('js_sort.php')
                .then(response => response.json())
                .then(data => {
                    jsData = data.data;
                    const networkEnd = performance.now();
                    const networkTime = (networkEnd - jsStart).toFixed(2);
                    
                    const sortedData = sortData(jsData, jsSortColumn, jsSortOrder);
                    const filteredData = filterData(sortedData, searchTerm);
                    const jsEnd = performance.now();
                    const jsTime = (jsEnd - networkEnd).toFixed(2);
                    
                    updateTable('jsTable', filteredData);
                    document.getElementById('jsExecutionTime').textContent = 
                        `Время выполнения: ${data.executionTime.toFixed(2)} мс (SQL) + ${(networkTime - data.executionTime).toFixed(2)} мс (сеть) + ${jsTime} мс (JS) = ${(jsEnd - jsStart).toFixed(2)} мс`;
                });
        }

        function sortData(data, column, order) {
            return [...data].sort((a, b) => {
                let valueA = a[column];
                let valueB = b[column];
                
                if (column === 'price' || column === 'quantity' || column === 'order_count') {
                    valueA = Number(valueA);
                    valueB = Number(valueB);
                }

                if (order === 'ASC') {
                    return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
                } else {
                    return valueA < valueB ? 1 : valueA > valueB ? -1 : 0;
                }
            });
        }

        function filterData(data, searchTerm) {
            if (!searchTerm) return data;
            searchTerm = searchTerm.toLowerCase();
            return data.filter(item => 
                item.name.toLowerCase().includes(searchTerm) ||
                item.manufacturer.toLowerCase().includes(searchTerm)
            );
        }

        function updateTable(tableId, data) {
            const tbody = document.querySelector(`#${tableId} tbody`);
            tbody.innerHTML = '';
            let totalSum = 0;
            
            data.forEach(item => {
                const row = tbody.insertRow();
                row.insertCell().textContent = item.name;
                row.insertCell().textContent = item.price;
                row.insertCell().textContent = item.quantity;
                row.insertCell().textContent = item.manufacturer;
                row.insertCell().textContent = item.supplier;
                row.insertCell().textContent = item.order_count;
                totalSum += parseFloat(item.price) * parseInt(item.quantity);
            });

            // Форматируем сумму для лучшей читаемости
            const formattedSum = new Intl.NumberFormat('ru-RU', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
                useGrouping: true
            }).format(totalSum);

            // Добавляем строку с общей суммой
            const totalRow = tbody.insertRow();
            totalRow.style.fontWeight = 'bold';
            totalRow.style.backgroundColor = '#f0f0f0';
            const cell = totalRow.insertCell();
            cell.colSpan = 6;
            cell.textContent = `Общая сумма: ${formattedSum}`;
            cell.style.textAlign = 'right';
        }

        function sortSqlTable(column) {
            if (sqlSortColumn === column) {
                sqlSortOrder = sqlSortOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                sqlSortColumn = column;
                sqlSortOrder = 'ASC';
            }
            refreshData();
        }

        function sortJsTable(column) {
            const searchTerm = document.getElementById('searchInput').value;
            const jsStart = performance.now();

            if (jsSortColumn === column) {
                jsSortOrder = jsSortOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                jsSortColumn = column;
                jsSortOrder = 'ASC';
            }

            const sortedData = sortData(jsData, column, jsSortOrder);
            const filteredData = filterData(sortedData, searchTerm);
            const jsEnd = performance.now();
            
            updateTable('jsTable', filteredData);
            const baseTime = document.getElementById('jsExecutionTime').textContent.split(':')[1].split('=')[0].trim();
            const jsTime = (jsEnd - jsStart).toFixed(2);
            const [phpTime] = baseTime.split('+')[0].trim().split(' ');
            const totalTime = (parseFloat(phpTime) + parseFloat(jsTime)).toFixed(2);
            document.getElementById('jsExecutionTime').textContent = 
                `Время выполнения: ${phpTime} мс + ${jsTime} мс (JS sort) = ${totalTime} мс`;
        }

        refreshData();
    </script>
</body>
</html>
