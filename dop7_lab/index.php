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
        <!-- SQL Sort -->
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

        <!-- JS Sort -->
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
        let currentSortColumn = 'name';
        let currentSortOrder = 'ASC';

        function refreshData() {
            const searchTerm = document.getElementById('searchInput').value;
            fetch(`sql_sort.php?sort=${currentSortColumn}&order=${currentSortOrder}&search=${searchTerm}`)
                .then(response => response.json())
                .then(data => {
                    updateTable('sqlTable', data.data);
                    document.getElementById('sqlExecutionTime').textContent = 
                        `Время выполнения: ${data.executionTime.toFixed(2)} мс`;
                });
            const jsStart = performance.now();
            fetch(`js_sort.php`)
                .then(response => response.json())
                .then(data => {
                    jsData = data.data;
                    const sortedData = sortData(jsData, currentSortColumn, currentSortOrder);
                    const filteredData = filterData(sortedData, searchTerm);
                    const jsEnd = performance.now();
                    
                    updateTable('jsTable', filteredData);
                    document.getElementById('jsExecutionTime').textContent = 
                        `Время выполнения: ${data.executionTime.toFixed(2)} мс + ${(jsEnd - jsStart).toFixed(2)} мс (JS)`;
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
                    return valueA > valueB ? 1 : -1;
                } else {
                    return valueA < valueB ? 1 : -1;
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
            
            data.forEach(item => {
                const row = tbody.insertRow();
                row.insertCell().textContent = item.name;
                row.insertCell().textContent = item.price;
                row.insertCell().textContent = item.quantity;
                row.insertCell().textContent = item.manufacturer;
                row.insertCell().textContent = item.supplier;
                row.insertCell().textContent = item.order_count;
            });
        }

        function sortSqlTable(column) {
            currentSortColumn = column;
            currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
            refreshData();
        }

        function sortJsTable(column) {
            currentSortColumn = column;
            currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
            const searchTerm = document.getElementById('searchInput').value;
            
            const jsStart = performance.now();
            const sortedData = sortData(jsData, column, currentSortOrder);
            const filteredData = filterData(sortedData, searchTerm);
            const jsEnd = performance.now();

            updateTable('jsTable', filteredData);
            document.getElementById('jsExecutionTime').textContent += 
                ` + ${(jsEnd - jsStart).toFixed(2)} мс (JS sort)`;
        }

        refreshData();
    </script>
</body>
</html>
