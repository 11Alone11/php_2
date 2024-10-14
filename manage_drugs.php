<?php

include 'db.php'; 
include 'sessionConf.php';
include 'db_executor.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// Лекарства
$search_query = '';
$order_by = 'id';
$order_dir = 'ASC';
//Пользователи
$users_order_by = 'id';
$users_order_dir = 'ASC';
//Производители
$manufacturers_order_by = 'id';
$manufacturers_order_dir = 'ASC';



//default supplier
$search_query_user = '';
$order_by_user = 'id';
$order_dir_user = 'ASC';

$search_query_user_supplier = '';
$order_by_supplier = 'name';
$order_dir_supplier = 'ASC';


//default shopper
$search_query_shopper = '';
$order_by_shopper = 'name';
$order_dir_shopper = 'ASC';

$search_query_shopper_cart = '';
$order_by_shopper_cart = 'name';
$order_dir_shopper_cart = 'ASC';


try{
    if(isset($_SESSION['error_message']) && $_SESSION['error_message'] == "Неверный логин или пароль, попробуйте еще раз."):
        $_SESSION['error_message'] = '';
    endif;
    if($_SESSION['server_conn_error'] === true){
        throw new Exception("Ошибка соединения с сервером");
    }
    if(!isset($conn)){
        throw new Exception("Ошибка соединения с сервером");
    }
    $dbExecuter = new ActionLogger();
    // if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['name'] == "drop_res"){
    //     $search_query_shopper = '';
    //     $order_by_shopper = 'name';
    //     $order_dir_shopper = 'ASC';
    //     $query = "
    //     SELECT DISTINCT
    //         drugs.id AS id,
    //         drugs.name AS name, 
    //         manufacturers.name AS manufacturer, 
    //         users.name AS supplier, 
    //         drugs.price AS price, 
    //         drugs.quantity AS quantity,
    //         drugs.is_allowed
    //     FROM 
    //         drugs 
    //     JOIN 
    //         manufacturers ON drugs.manufacturer_id = manufacturers.id 
    //     JOIN 
    //         users ON drugs.provider_id = users.id 
    //     WHERE 1=1 and is_allowed = 'Одобрено' and is_hiden <> 1
    //     ";

    //     if (!empty($search_query_shopper)) {
    //         $query .= " AND drugs.name LIKE '%$search_query_shopper%' "; // Используем .= для добавления
    //     }

    //     $query .= "
    //         ORDER BY $order_by_shopper $order_dir_shopper
    //     ";

    //     $result_shopper = $conn->query($query);    
    // }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //var_dump($_POST); // Выводим все переменные POST для отладки
        if (isset($_POST['formType'], $_POST['formId'], $_POST['tableName'], $_POST['fieldName'])) {
            $formType = $_POST['formType'];
            $formId = $_POST['formId'];
            $tableName = $_POST['tableName'];
            $fieldName = $_POST['fieldName'];
            $inputValue = $_POST['input'];
            //echo "Table: " . $tableName . " Field: " . $fieldName; // Для проверки
        if ($tableName === 'drugs' && $fieldName === 'name') {
            if (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $inputValue)) {
                $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
            }else{
                $stmt = $conn->prepare("UPDATE drugs SET name = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('si', $inputValue, $formId);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "Ошибка подготовки запроса: " . $conn->error;
            }
            $Actstr = "Обновление поля названия лекарства c идентификатором '$formId' на '$inputValue'";
            $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
        }
        }
        if ($tableName === 'drugs' && $fieldName === 'manufacturer_id') {
            if (!filter_var($inputValue, FILTER_VALIDATE_INT) || $inputValue <= 0) {
                $_SESSION['error_message'] = "ID производителя должен быть положительным целым числом";
            } else {
                // Check if the manufacturer_id exists in the manufacturers table
                $checkManufacturerQuery = $conn->prepare("SELECT COUNT(*) FROM manufacturers WHERE id = ?");
                $checkManufacturerQuery->bind_param('i', $inputValue);
                $checkManufacturerQuery->execute();
                $checkManufacturerQuery->bind_result($count);
                $checkManufacturerQuery->fetch();
                $checkManufacturerQuery->close();
        
                // If manufacturer_id exists, proceed with the update
                if ($count > 0) {
                    $stmt = $conn->prepare("UPDATE drugs SET manufacturer_id = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param('ii', $inputValue, $formId);
                        $stmt->execute();
                        $stmt->close();
                        $Actstr = "Обновление поля идентификатора производителя лекарства c идентификатором '$formId' на '$inputValue'";
                        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                    } else {
                        echo "Ошибка подготовки запроса: " . $conn->error;
                    }
                } else {
                    $_SESSION['error_message'] = "Производитель с указанным ID не найден.";
                }
            }
        }
        if ($tableName === 'drugs' && $fieldName === 'price') {
            if (!filter_var($inputValue, FILTER_VALIDATE_FLOAT) || $inputValue <= 0) {
                $_SESSION['error_message'] = "Цена должна быть положительным числом";
            } else {
                // First, retrieve the current quantity of the drug
                $quantityQuery = $conn->prepare("SELECT quantity FROM drugs WHERE id = ?");
                $quantityQuery->bind_param('i', $formId);
                $quantityQuery->execute();
                $quantityQuery->bind_result($quantity);
                $quantityQuery->fetch();
                $quantityQuery->close();
        
                // Calculate the new cost
                $cost = $inputValue * $quantity;
        
                // Now update both price and cost
                $stmt = $conn->prepare("UPDATE drugs SET price = ?, cost = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('ddi', $inputValue, $cost, $formId);
                    $stmt->execute();
                    $stmt->close();
                    $Actstr = "Обновление поля цены и итоговой стоимости лекарства c идентификатором '$formId'.\nНовые значения: Цена: '$inputValue', Итоговая стоимость: '$cost'";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                } else {
                    echo "Ошибка подготовки запроса: " . $conn->error;
                }
            }
        }
        if ($tableName === 'drugs' && $fieldName === 'quantity') {
            if (!filter_var($inputValue, FILTER_VALIDATE_INT) || $inputValue <= 0) {
                $_SESSION['error_message'] = "Количество должно быть положительным целым числом";
            } else {
                // First, retrieve the price for the drug
                $priceQuery = $conn->prepare("SELECT price FROM drugs WHERE id = ?");
                $priceQuery->bind_param('i', $formId);
                $priceQuery->execute();
                $priceQuery->bind_result($price);
                $priceQuery->fetch();
                $priceQuery->close();
        
                // Calculate the new cost
                $cost = round((float)$price * $inputValue, 2);
        
                // Now update both quantity and cost
                $stmt = $conn->prepare("UPDATE drugs SET quantity = ?, cost = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('idi', $inputValue, $cost, $formId);
                    $stmt->execute();
                    $stmt->close();
                    $Actstr = "Обновление поля количества и итоговой стоимости лекарства c идентификатором '$formId'.\nНовые значения: Количество: '$inputValue', Итоговая стоимость: '$cost'";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                } else {
                    echo "Ошибка подготовки запроса: " . $conn->error;
                }
            }
        }
        if ($tableName === 'drugs' && $fieldName === 'provider_id') {
            if (!filter_var($inputValue, FILTER_VALIDATE_INT) || $inputValue <= 0) {
                $_SESSION['error_message'] = "ID поставщика должен быть положительным целым числом";
            } else {
                // Check if the provider_id exists in the users table
                $checkProviderQuery = $conn->prepare("SELECT COUNT(*) FROM users WHERE id = ? and type <> 2");
                $checkProviderQuery->bind_param('i', $inputValue);
                $checkProviderQuery->execute();
                $checkProviderQuery->bind_result($count);
                $checkProviderQuery->fetch();
                $checkProviderQuery->close();
        
                // If provider_id exists, proceed with the update
                if ($count > 0) {
                    $stmt = $conn->prepare("UPDATE drugs SET provider_id = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param('ii', $inputValue, $formId);
                        $stmt->execute();
                        $stmt->close();
                        $Actstr = "Обновление поля идентификатора поставщика лекарства с идентификатором '$formId'.\nНовое значение: '$inputValue'";
                        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                    } else {
                        echo "Ошибка подготовки запроса: " . $conn->error;
                    }
                } else {
                    $_SESSION['error_message'] = "Поставщик с указанным ID не найден.";
                }
            }
        }
        if ($tableName === 'drugs' && $fieldName === 'is_allowed') {
            if (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $inputValue)) {
                $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
            }else{
                $stmt = $conn->prepare("UPDATE drugs SET is_allowed = ?, checked_by_user = 0, last_updated= CURRENT_TIMESTAMP, who_checked = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('sii', $inputValue, $_SESSION['user_id'], $formId);
                    $stmt->execute();
                    $stmt->close();
                    $Actstr = "Обновление поля доступа к внесению лекарства с идентификатором '$formId'.\nНовое значение: '$inputValue'";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                } else {
                    echo "Ошибка подготовки запроса: " . $conn->error;
                }
            }
        }
        if ($tableName === 'my_orders_requests' && $fieldName === 'status') {
            if (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $inputValue)) {
                $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
            }else{
                $stmt = $conn->prepare("UPDATE orders SET status = ?, checked_by_user = 0, last_updated= CURRENT_TIMESTAMP WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('si', $inputValue, $formId);
                    $stmt->execute();
                    $stmt->close();
                    $Actstr = "Пользователь прочитал уведомление о заказе '$formId'.\nРезультат заказа: '$inputValue'";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                } else {
                    echo "Ошибка подготовки запроса: " . $conn->error;
                }
            }
        }
        if ($tableName === 'manufacturers' && $fieldName === 'name') {
            if (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $inputValue)) {
                $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
            }else{
                $stmt = $conn->prepare("UPDATE manufacturers SET name = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('si', $inputValue, $formId);
                    $stmt->execute();
                    $stmt->close();
                    $Actstr = "Обновление названия производителя с идентификатором '$formId'.\nНовое значение: '$inputValue'";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                } else {
                    echo "Ошибка подготовки запроса: " . $conn->error;
                }
            }
        }
        // if ($tableName === 'users' && $fieldName === 'name') {
        //     if (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $inputValue)) {
        //     $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
        //     }else{
        //     $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        //     if ($stmt) {
        //     $stmt->bind_param('si', $inputValue, $formId);
        //     $stmt->execute();
        //     $stmt->close();
        //     } else {
        //     echo "Ошибка подготовки запроса: " . $conn->error;
        //     }
        //     }
        // }
        if ($tableName === 'users' && $fieldName === 'name') {
            if (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $inputValue)) {
                $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
            } else {
                // Check if the name already exists for another user
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE name = ? AND id <> ?");
                
                if (!$checkStmt) {
                    echo "Ошибка подготовки запроса: " . $conn->error;
                    exit;
                }

                $checkStmt->bind_param('si', $inputValue, $formId);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();

                if ($count > 0) {
                    $_SESSION['error_message'] = 'Пользователь с таким именем уже существует.';
                } else {
                    $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param('si', $inputValue, $formId);
                        $stmt->execute();
                        $stmt->close();
                        $Actstr = "Обновление логина пользователя с идентификатором '$formId'.\nНовое значение: '$inputValue'";
                        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                    } else {
                        echo "Ошибка подготовки запроса: " . $conn->error;
                    }
                }
            }
        }
        if ($tableName === 'users' && $fieldName === 'type') {
            if (!is_numeric($inputValue) || intval($inputValue) != $inputValue || $inputValue < 0) {
                $_SESSION['error_message'] = "Тип поставщика должен быть целым неотрицательным числом";
            }else{
                $stmt = $conn->prepare("UPDATE users SET type = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('ii', $inputValue, $formId);
                    $stmt->execute();
                    $stmt->close();
                    $user_type = true
                    ? ($inputValue == 1 ? 'админ' : ($inputValue == 0 ? 'поставщик' : ($inputValue == 2 ? 'покупатель' : 'неизвестный тип')))
                    : 'неизвестный тип';
                    $Actstr = "Обновление типа пользователя с идентификатором '$formId'.\nНовое значение: '$user_type'";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                } else {
                    echo "Ошибка подготовки запроса: " . $conn->error;
                }
            }
        }
        if ($tableName === 'drugs_user' && $fieldName === 'name') {
            echo "drugs_user name";

            if (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $inputValue)) {
                $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
            } else {
                    $stmt = $conn->prepare("UPDATE drugs SET name = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('si', $inputValue, $formId);
                    $stmt->execute();
                    $stmt->close();
                    $Actstr = "Обновление поля наименования лекарства поставщика с идентификатором '$formId'.\nНовое значение: '$inputValue'";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                    // echo "drugs_user name";
                } else {
                    echo "Ошибка подготовки запроса: " . $conn->error;
                }
            }
        }
        if ($tableName === 'drugs_user' && $fieldName === 'manufacturer') {
            if (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $inputValue)) {
                $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
            } else {
                $checkManufacturerQuery = $conn->prepare("SELECT id FROM manufacturers WHERE name = ?");
                $checkManufacturerQuery->bind_param('s', $inputValue);
                $checkManufacturerQuery->execute();
                $checkManufacturerQuery->bind_result($manufacturerId);
                $checkManufacturerQuery->fetch();
                $checkManufacturerQuery->close();
                if ($manufacturerId) {
                    $stmt = $conn->prepare("UPDATE drugs SET manufacturer_id = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param('ii', $manufacturerId, $formId);
                        $stmt->execute();
                        $stmt->close();
                        $Actstr = "Обновление поля идентификатора производителя лекарства с идентификатором '$formId'.\nНовое значение: '$manufacturerId' или же '$inputValue'";
                        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                    } else {
                        echo "Ошибка подготовки запроса: " . $conn->error;
                    }
                    $stmt = $conn->prepare("UPDATE orders SET manufacturer_id = ? WHERE drug_id = ?");
                    if ($stmt) {
                        $stmt->bind_param('ii', $manufacturerId, $formId);
                        $stmt->execute();
                        $stmt->close();
                        $Actstr = "Обновление поля идентификатора производителя лекарства в таблице заказов с идентификатором '$formId'.\nНовое значение: '$manufacturerId' или же '$inputValue'";
                        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                    } else {
                        echo "Ошибка подготовки запроса: " . $conn->error;
                    }
                } else {
                    $_SESSION['error_message'] = "Производитель не найден.";
                }
            }
        }
        if ($tableName === 'drugs_user' && $fieldName === 'price') {
            if (!filter_var($inputValue, FILTER_VALIDATE_FLOAT) || $inputValue <= 0) {
                $_SESSION['error_message'] = "Цена должна быть положительным числом";
            } else {
                // First, retrieve the current quantity of the drug
                $quantityQuery = $conn->prepare("SELECT quantity FROM drugs WHERE id = ?");
                $quantityQuery->bind_param('i', $formId);
                $quantityQuery->execute();
                $quantityQuery->bind_result($quantity);
                $quantityQuery->fetch();
                $quantityQuery->close();
        
                // Calculate the new cost
                
                $cost =  $inputValue * $quantity;
               
                // Now update both price and cost
                $stmt = $conn->prepare("UPDATE drugs SET price = ?, cost = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('ddi', $inputValue, $cost, $formId);
                    $stmt->execute();
                    $stmt->close();
                    $Actstr = "Обновление поля цены и общей стоимости лекарства с идентификатором '$formId'.\nНовые значения: Цена - '$inputValue', итоговая стоимость - '$cost' ";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                    // echo "drugs_user price";
                } else {
                    echo "Ошибка подготовки запроса: " . $conn->error;
                }
            }
        }
        if ($tableName === 'drugs_user' && $fieldName === 'quantity') {
            if (!filter_var($inputValue, FILTER_VALIDATE_INT) || $inputValue <= 0) {
                $_SESSION['error_message'] = "Количество должно быть положительным целым числом";
            } else {
                // First, retrieve the price for the drug
                $priceQuery = $conn->prepare("SELECT price FROM drugs WHERE id = ?");
                $priceQuery->bind_param('i', $formId);
                $priceQuery->execute();
                $priceQuery->bind_result($price);
                $priceQuery->fetch();
                $priceQuery->close();
        
                // Calculate the new cost
                $cost = round((float)$price * $inputValue, 2);
        
                // Now update both quantity and cost
                $stmt = $conn->prepare("UPDATE drugs SET quantity = ?, cost = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('idi', $inputValue, $cost, $formId);
                    $stmt->execute();
                    $stmt->close();
                    $Actstr = "Обновление поля количества и общей стоимости лекарства с идентификатором '$formId'.\nНовые значения: Количество - '$inputValue', итоговая стоимость - '$cost' ";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                    // echo "drugs_user quantity";
                } else {
                    echo "Ошибка подготовки запроса: " . $conn->error;
                }
            }
        }  
        if ($tableName === 'drugs_shopper_cart' && $fieldName === 'quantity') {
            if (!filter_var($inputValue, FILTER_VALIDATE_INT) || $inputValue <= 0) {
                $_SESSION['error_message'] = "Количество должно быть положительным целым числом";
            } else {
                // Сначала получим price и drug_id из orders
                $priceQuery = $conn->prepare("SELECT price, drug_id FROM orders WHERE id = ?");
                $priceQuery->bind_param('i', $formId);
                $priceQuery->execute();
                $priceQuery->bind_result($price, $drugId);
                $priceQuery->fetch();
                $priceQuery->close();
        
                // Теперь проверим количество на складе
                $stockQuery = $conn->prepare("SELECT quantity FROM drugs WHERE id = ?");
                $stockQuery->bind_param('i', $drugId);
                $stockQuery->execute();
                $stockQuery->bind_result($availableQuantity);
                $stockQuery->fetch();
                $stockQuery->close();
        
                // Проверяем наличие на складе
                if ($inputValue > $availableQuantity) {
                    $_SESSION['error_message'] = "Недостаточно товара на складе. Доступно: $availableQuantity";
                } else {
                    // Рассчитываем новую стоимость
                    $cost = round((float)$price * $inputValue, 2);
        
                    // Обновляем количество и стоимость
                    $stmt = $conn->prepare("UPDATE orders SET quantity = ?, cost = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param('idi', $inputValue, $cost, $formId);
                        $stmt->execute();
                        $stmt->close();
                        $Actstr = "Обновление поля количества и общей стоимости лекарства с идентификатором заказа '$formId'.\nНовые значения: Количество - '$inputValue', итоговая стоимость - '$cost' ";
                        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                    } else {
                        echo "Ошибка подготовки запроса: " . $conn->error;
                    }
                }
            }
        } 
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
        }
    }

    if (isset($_POST['search'])) {
        $search_query = isset($_POST['search_query']) ? $_POST['search_query'] : '';
        $Actstr = "Администратор установил строку поиска '$search_query' для таблицы лекарств.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    }
    

    if (isset($_POST['search_for_user'])) {
        $search_query_user = $conn->real_escape_string($_POST['search_for_user']);
        $Actstr = "Поставщик установил строку поиска '$search_query_user' для таблицы его лекарств.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    }

    if (isset($_GET['order_by_user']) && isset($_GET['order_dir_user'])) {
        $order_by_user = $_GET['order_by_user'];
        $order_dir_user = $_GET['order_dir_user'] === 'ASC' ? 'DESC' : 'ASC';
        $Actstr = "Поставщик установил порядок сортировки '$order_dir_user' и сортировку по полю '$order_by_user' для таблицы его лекарств.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    }


    if (isset($_GET['order_by']) && isset($_GET['order_dir'])) {
        $order_by = $_GET['order_by'];
        $order_dir = $_GET['order_dir'] === 'ASC' ? 'DESC' : 'ASC';
        $Actstr = "Администратор установил порядок сортировки '$order_dir' и сортировку по полю '$order_by' для таблицы лекарств.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    }

    if (isset($_GET['users_order_by']) && isset($_GET['users_order_dir'])) {
        $users_order_by = $_GET['users_order_by'];
        $users_order_dir = $_GET['users_order_dir'] === 'ASC' ? 'DESC' : 'ASC';
        $Actstr = "Администратор установил порядок сортировки '$users_order_dir' и сортировку по полю '$users_order_by' для таблицы поставщиков.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    }

    if (isset($_GET['manufacturers_order_by']) && isset($_GET['manufacturers_order_dir'])) {
        $manufacturers_order_by = $_GET['manufacturers_order_by'];
        $manufacturers_order_dir = $_GET['manufacturers_order_dir'] === 'ASC' ? 'DESC' : 'ASC';
        $Actstr = "Администратор установил порядок сортировки '$manufacturers_order_dir' и сортировку по полю '$manufacturers_order_by' для таблицы производителей.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    }

    if (isset($_GET['order_by_shopper']) && isset($_GET['order_dir_shopper'])) {
        $order_by_shopper = $_GET['order_by_shopper'];
        $order_dir_shopper = $_GET['order_dir_shopper'] === 'ASC' ? 'DESC' : 'ASC';
        $Actstr = "Покупатель установил порядок сортировки '$order_dir_shopper' и сортировку по полю '$order_by_shopper' для таблицы лекарств.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    }

    if (isset($_GET['order_by_shopper_cart']) && isset($_GET['order_dir_shopper_cart'])) {
        $order_by_shopper_cart = $_GET['order_by_shopper_cart'];
        $order_dir_shopper_cart = $_GET['order_dir_shopper_cart'] === 'ASC' ? 'DESC' : 'ASC';
        $Actstr = "Покупатель установил порядок сортировки '$order_dir_shopper_cart' и сортировку по полю '$order_by_shopper_cart' в его корзине лекарств.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    }  

    if (isset($_GET['order_by_supplier']) && isset($_GET['order_dir_supplier'])) {
        $order_by_supplier = $_GET['order_by_supplier'];
        $order_dir_supplier = $_GET['order_dir_supplier'] === 'ASC' ? 'DESC' : 'ASC';
        $Actstr = "Администратор установил порядок сортировки '$order_dir_supplier' и сортировку по полю '$order_by_supplier' в его таблице заказов лекарств.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    } 

    //Add for user

    if (isset($_POST['search_us_btn'])) {
        $search_query_shopper = htmlspecialchars($_POST['search_for_shopper']);
        $Actstr = "Покупатель установил строку поиска '$search_query_shopper' для лекарств.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
        $_SESSION['yummy'] = $search_query_shopper;
    }
    $search_query_shopper = $_SESSION['yummy'];
    $uid = $_SESSION['user_id'];
        $query = "
            SELECT DISTINCT
                drugs.id AS id,
                drugs.name AS name,
                manufacturers.name AS manufacturer,
                users.name AS supplier,
                drugs.price AS price,
                drugs.quantity AS quantity,
                drugs.is_allowed,
                COALESCE(order_counts.total_quantity, 0) AS total_quantity
            FROM 
                drugs 
            JOIN 
                manufacturers ON drugs.manufacturer_id = manufacturers.id 
            JOIN 
                users ON drugs.provider_id = users.id 
            LEFT JOIN (
                SELECT 
                    drug_id,
                    SUM(quantity) AS total_quantity,
                    user_id
                FROM 
                    orders
                WHERE user_id = $uid
                GROUP BY 
                    drug_id
            ) AS order_counts ON drugs.id = order_counts.drug_id
            WHERE 
                drugs.is_allowed = 'Одобрено' 
                AND drugs.is_hiden <> 1
        ";
        
        if (!empty($search_query_shopper)) {
            $query .= " AND drugs.name LIKE '%$search_query_shopper%' ";
        }

        $query .= "
            ORDER BY 
                total_quantity DESC, 
                $order_by_shopper $order_dir_shopper
        ";
        $result_shopper = $conn->query($query);
    

    $user_analytics_querry = "Select sum(cost) as sumCost from orders where user_id = $uid and is_hiden_byShopper <> 1";
    $user_analytics_sum_drugs  = $conn->query($user_analytics_querry);
    $user_analytics_querry = "Select sum(cost)/sum(quantity) as medCost from orders where user_id = $uid and is_hiden_byShopper <> 1";
    $user_analytics_med_drugs  = $conn->query($user_analytics_querry);
    // $uid = $_SESSION['user_id'];
    // $query = "
    //     SELECT DISTINCT
    //         drugs.id AS id,
    //         drugs.name AS name,
    //         manufacturers.name AS manufacturer,
    //         users.name AS supplier,
    //         drugs.price AS price,
    //         drugs.quantity AS quantity,
    //         drugs.is_allowed,
    //         COALESCE(order_counts.total_quantity, 0) AS total_quantity
    //     FROM 
    //         drugs 
    //     JOIN 
    //         manufacturers ON drugs.manufacturer_id = manufacturers.id 
    //     JOIN 
    //         users ON drugs.provider_id = users.id 
    //     LEFT JOIN (
    //         SELECT 
    //             drug_id,
    //             SUM(quantity) AS total_quantity,
    //             user_id
    //         FROM 
    //             orders
    //         WHERE user_id = $uid
    //         GROUP BY 
    //             drug_id
    //     ) AS order_counts ON drugs.id = order_counts.drug_id
    //     WHERE 
    //         drugs.is_allowed = 'Одобрено' 
    //         AND drugs.is_hiden <> 1
    // ";
    
    // if (!empty($search_query_shopper)) {
    //     $query .= " AND drugs.name LIKE '%$search_query_shopper%' ";
    // }

    // $query .= "
    //     ORDER BY 
    //         total_quantity DESC, 
    //         $order_by_shopper $order_dir_shopper
    // ";
    // $result_shopper = $conn->query($query);
    if (isset($_POST['add_drugs_user'])) {
        $name = trim($_POST['name']);
        $manufacturer = trim($_POST['manufacturer_name']);
        $price = trim($_POST['price']);
        $quantity = trim($_POST['quantity']);
    
        if (trim($name) === '') {
            $_SESSION['error_message'] = 'Пожалуйста, введите название.';
        } elseif (trim($manufacturer) === '') {
            $_SESSION['error_message'] = 'Пожалуйста, введите название производителя.';
        } elseif (!isset($price) || $price === '') {
            $_SESSION['error_message'] = 'Пожалуйста, введите цену.';
        } elseif (!isset($quantity) || $quantity === '') {
            $_SESSION['error_message'] = 'Пожалуйста, введите количество.';
        } elseif (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $name)) {
            $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
        } elseif (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $manufacturer)) {
            $_SESSION['error_message'] = 'Название производителя должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
        } elseif (!is_numeric($price) || $price <= 0) {
            $_SESSION['error_message'] = 'Цена должна быть положительным числом.';
        } elseif (!filter_var($quantity, FILTER_VALIDATE_INT) || $quantity <= 0) {
            $_SESSION['error_message'] = 'Число продукции должно быть положительным целым числом.';
        } else {
            $manufacturer_query = $conn->prepare("SELECT id FROM manufacturers WHERE name = ?");
            $manufacturer_query->bind_param('s', $manufacturer);
            $manufacturer_query->execute();
            $manufacturer_result = $manufacturer_query->get_result();
            $manufacturer_id = $manufacturer_result->fetch_assoc();
    
            if (!$manufacturer_id) {
                $insert_manufacturer_query = $conn->prepare("INSERT INTO manufacturers (name) VALUES (?)");
                $insert_manufacturer_query->bind_param('s', $manufacturer);
                if (!$insert_manufacturer_query->execute()) {
                    $_SESSION['error_message'] = 'Ошибка добавления производителя: ' . $conn->error;
                    $insert_manufacturer_query->close();
                    exit;
                }
                $manufacturer_id = $conn->insert_id; // Get the new manufacturer ID
                $insert_manufacturer_query->close();
            } else {
                $manufacturer_id = $manufacturer_id['id'];
            }
    
            $provider_query = $conn->prepare("SELECT id FROM users WHERE name = ?");
            $provider_name = $_SESSION['user'];
            $provider_query->bind_param('s', $provider_name);
            $provider_query->execute();
            $provider_result = $provider_query->get_result();
            $provider_id = $provider_result->fetch_assoc();
    
            if (!$provider_id) {
                $_SESSION['error_message'] = 'Поставщик не найден.';
                exit;
            }
            $provider_id = $provider_id['id'];
    
            $cost = $price * $quantity;
    
            $name = $conn->real_escape_string(htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
            $price = $conn->real_escape_string(htmlspecialchars($price, ENT_QUOTES, 'UTF-8'));
            $quantity = $conn->real_escape_string(htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8'));
            $cost = $conn->real_escape_string(htmlspecialchars($cost, ENT_QUOTES, 'UTF-8'));
    
            $insert_query = $conn->prepare("INSERT INTO drugs (name, manufacturer_id, provider_id, price, quantity, cost) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_query->bind_param('siidid', $name, $manufacturer_id, $provider_id, $price, $quantity, $cost);
            if ($insert_query->execute()) {
                $_SESSION['success_message'] = 'Лекарство успешно добавлено.';
            } else {
                $_SESSION['error_message'] = 'Ошибка добавления лекарства: ' . $conn->error;
            }
            $insert_query->close();
            $Actstr = "Поставщик внес новое лекарство на рассмотрение '$name'. Общая стоимость и количество: '$cost' '$quantity'";
            $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
            header("Location: table.php");
            exit();
        }
    }
    
    if (isset($_POST['add_to_cart'])) {
        $drugId = htmlspecialchars($_POST['add_to_cart']);
        $desiredQuantity = intval($_POST['desired_quantity']);
        $userName = $_SESSION['user']; 
        $userId = null;

        $userQuery = $conn->prepare("SELECT id FROM users WHERE name = ?");
        $userQuery->bind_param('s', $userName);
        $userQuery->execute();
        $userQuery->bind_result($userId);
        $userQuery->fetch();
        $userQuery->close();
    
        $drugQuery = $conn->prepare("SELECT manufacturer_id, price, quantity, provider_id FROM drugs WHERE id = ?");
        $drugQuery->bind_param('i', $drugId);
        $drugQuery->execute();
        $drugQuery->bind_result($manufacturerId, $price, $quantity, $providerId);
        $drugQuery->fetch();
        $drugQuery->close();
    
    
        if ($quantity >= $desiredQuantity) {
            $status = 'В обработке';
            $cost = $price * (float)$desiredQuantity; 
            $insertQuery = $conn->prepare("INSERT INTO orders (user_id, provider_id, manufacturer_id, drug_id, quantity, price, status, cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insertQuery->bind_param('iiiiidsd', $userId, $providerId, $manufacturerId, $drugId, $desiredQuantity, $price, $status, $cost);
            $insertQuery->execute();
            $insertQuery->close();
            $Actstr = "Покупатель добавил лекарство '$drugId' в корзину. Общая стоимость и количество: '$cost' '$desiredQuantity'";
            $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error_message'] = 'Недостаточно количества лекарства на складе.';
        }
    }

    $query = "
    SELECT 
        drugs.id AS id, 
        manufacturers.name AS manufacturer, 
        drugs.name AS name, 
        drugs.price AS price, 
        drugs.quantity AS quantity, 
        drugs.cost AS cost,
        drugs.is_allowed,
        drugs.is_hiden
    FROM 
        drugs 
    JOIN 
        manufacturers ON drugs.manufacturer_id = manufacturers.id 
    WHERE 
        provider_id IN (
            SELECT id FROM users WHERE name LIKE '%" . $conn->real_escape_string($_SESSION['user']) . "%'
        ) AND is_hiden <> 1
    ";
    if (!empty($search_query_user)) {
        if (is_numeric($search_query_user)) {
            $query .= " AND (
                cost = $search_query_user 
                OR price = $search_query_user 
                OR quantity = $search_query_user)";
        } else {
            $query .= " AND drugs.name LIKE '%$search_query_user%' OR manufacturers.name LIKE '%$search_query_user%'";
        }
    }

    // Добавляем условия сортировки
    $query .= " ORDER BY $order_by_user $order_dir_user";
    $result_user = $conn->query($query);

    if (isset($_POST['add_manufacturer'])) {
        $name = trim($_POST['name']);
        if (empty($name)) {
            $_SESSION['error_message'] = 'Пожалуйста, заполните все поля.';
        } elseif (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $name)) {
            $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
        } else {
            $name = $conn->real_escape_string(htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));  
            $check_query = $conn->prepare("SELECT COUNT(*) FROM manufacturers WHERE name = ?");
            $check_query->bind_param('s', $name);
            $check_query->execute();
            $check_query->bind_result($count);
            $check_query->fetch();
            $check_query->close();
            if ($count > 0) {
                $_SESSION['error_message'] = 'Производитель с таким названием уже существует.';
            } else {
                $insert_query = "INSERT INTO manufacturers (name) VALUES ('$name')";
                if ($conn->query($insert_query) === TRUE) {
                    $_SESSION['success_message'] = 'Производитель успешно добавлен.';
                    $Actstr = "Администратор добавил нового производителя '$name'";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                } else {
                    $_SESSION['error_message'] = 'Ошибка добавления производителя: ' . $conn->error;
                }
            }
        }
    }

    if (isset($_POST['delete_drug_user'])) {
        $id = intval($_POST['delete_drug_user']);
        $delete_query = "UPDATE drugs SET is_hiden = 1 WHERE id=$id";
        $conn->query($delete_query);
        $Actstr = "Поставщик удалил свое лекарство с идентификатором '$id'";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);        
        header("Location: table.php");
        exit();
    }

    if (isset($_POST['add'])) {
        $name = trim($_POST['name']);
        $manufacturer_id = trim($_POST['manufacturer_id']);
        $provider_id = trim($_POST["provider_id"]);
        $price = trim($_POST['price']);
        $quantity = trim($_POST["quantity"]);
        // Получаем данные производителя
        $manufacturer_query = "SELECT name FROM manufacturers WHERE id = $manufacturer_id";
        $manufacturer_result = $conn->query($manufacturer_query);
        $manufacturer = $manufacturer_result->fetch_assoc();
        // Получаем данные поставщика
        $provider_query = "SELECT name FROM users WHERE id = $provider_id and type <> 2";
        $provider_result = $conn->query($provider_query);
        $provider = $provider_result->fetch_assoc();
        if (empty($name)) {
            $_SESSION['error_message'] = 'Пожалуйста, введите название.';
        } elseif (empty($manufacturer_id)) {
            $_SESSION['error_message'] = 'Пожалуйста, выберите производителя.';
        } elseif (empty($price)) {
            $_SESSION['error_message'] = 'Пожалуйста, введите цену.';
        } elseif (empty($quantity)) {
            $_SESSION['error_message'] = 'Пожалуйста, введите количество.';
        } elseif (empty($provider_id)) {
            $_SESSION['error_message'] = 'Пожалуйста, выберите поставщика.';
        }  elseif (!preg_match("/^[a-zA-Z0-9а-яА-ЯёЁ_ ]{3,50}$/u", $name)) {
            $_SESSION['error_message'] = 'Название должно содержать от 3 до 50 символов и может включать буквы, цифры и пробелы.';
        } elseif (!is_numeric($manufacturer_id) || $manufacturer_id <= 0) {
            $_SESSION['error_message'] = 'ID производителя должен быть положительным числом.';
        } elseif (!is_numeric($provider_id) || $provider_id <= 0) {
            $_SESSION['error_message'] = 'ID поставщика должен быть положительным числом.';
        } elseif (!is_numeric($price) || floatval($price) <= 0) {
            $_SESSION['error_message'] = 'Цена должна быть положительным числом.';
        } elseif (!filter_var($quantity, FILTER_VALIDATE_INT) || $quantity <= 0) {
            $_SESSION['error_message'] = 'Число продукции должно быть положительным целым числом.';
        } elseif (!$manufacturer) {
            $_SESSION['error_message'] = "Производитель с ID '$manufacturer_id' не найден.";    
        } elseif (!$provider) {
            $_SESSION['error_message'] = "Поставщик с ID '$provider_id' не найден.";    
        }
        else {
            $cost_pre_version = $price * $quantity;
            $name = $conn->real_escape_string(htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
            $manufacturer_id = $conn->real_escape_string(htmlspecialchars($manufacturer_id, ENT_QUOTES, 'UTF-8'));
            $provider_id = $conn->real_escape_string(htmlspecialchars($provider_id, ENT_QUOTES, 'UTF-8'));
            $price = $conn->real_escape_string(htmlspecialchars($price, ENT_QUOTES, 'UTF-8'));
            $quantity = $conn->real_escape_string(htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8'));
            $cost =  $conn->real_escape_string(htmlspecialchars($cost_pre_version, ENT_QUOTES, 'UTF-8'));
            $insert_query = "INSERT INTO drugs (name, manufacturer_id, provider_id, price, quantity, cost) VALUES ('$name', '$manufacturer_id', '$provider_id', '$price', '$quantity', '$cost_pre_version')";
            $conn->query($insert_query);
            $Actstr = "Администратор добавил новое лекарство '$name'. Общая стоимость и количество - '$cost', '$quantity'";
            $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);  
            header("Location: table.php");
            exit();
        }
    }

    if (isset($_POST['delete'])) {
        $id = intval($_POST['delete']);
        $delete_query = "UPDATE drugs SET is_hiden = 1 WHERE id=$id";
        $conn->query($delete_query);
        $Actstr = "Администратор удалил лекарство '$id'.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);  
    }

    if (isset($_POST['logout'])) {
        $name = $_SESSION['user'];
        $Actstr = "Пользователь '$name' вышел из системы.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);  
        session_unset();
        session_destroy();
        header("Location: index.php");
    }
    
    if (isset($_POST['delete_user'])) {
        $id = intval($_POST['delete_user']);
        $current_username = $_SESSION['user'];
        $username_query = "SELECT name FROM users WHERE id=$id";
        $username_result = $conn->query($username_query);
        if ($username_result && $username_result->num_rows > 0) {
            $user_data = $username_result->fetch_assoc();
            $username_to_delete = $user_data['name'];
            if ($username_to_delete === $current_username) {
                $_SESSION['error_message'] = 'Вы не можете удалить свой собственный аккаунт.';
            } else {
                $delete_query = "DELETE FROM users WHERE id=$id";
                if ($conn->query($delete_query) === TRUE) {
                    $Actstr = "Администратор удалил пользователя '$id'.";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr); 
                } else {
                    echo "Ошибка при удалении: " . $conn->error;
                }
            }
        } else {
            $_SESSION['error_message'] = 'Пользователь не найден.';
        }
        
    }

    if (isset($_POST['delete_manufacturer'])) {
        $id = intval($_POST['delete_manufacturer']);
        $delete_query = "DELETE FROM manufacturers WHERE id=$id";
        $conn->query($delete_query);
        $Actstr = "Администратор удалил производителя '$id'.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr); 
    }

    if (isset($_POST['delete_shopper_drug'])) {
        $id = intval($_POST['delete_shopper_drug']);
        $delete_query = "UPDATE orders SET is_hiden_byShopper = 1 WHERE id=$id";
        $conn->query($delete_query);
        $Actstr = "Покупатель удалил из корзины лекарство '$id'.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    }

    if (isset($_POST['delete_supplier_order'])) {
        $id = intval($_POST['delete_supplier_order']);
        $delete_query = "UPDATE orders SET is_hiden_byProvider = 1 WHERE id=$id";
        $conn->query($delete_query);
        $Actstr = "Администратор удалил один из своих заказов '$id'.";
        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['check_all']) && is_array($_POST['check_all'])) {
            if (isset($_POST['action']) && $_POST['action'] === 'delete') {
                echo 'Я русский';
                $selectedItems = $_POST['check_all'];
                foreach ($selectedItems as $itemId) {
                    $delete_query = "DELETE FROM orders WHERE id=$itemId";
                    $conn->query($delete_query);
                    $Actstr = "Покупатель удалил из корзины лекарство '$itemId'.";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                }
            } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
                $selectedItems = $_POST['check_all'];
                foreach ($selectedItems as $itemId) {
                    $update_query = "UPDATE orders SET status = 'Оформлен' WHERE id=$itemId";
                    $conn->query($update_query);
                    $Actstr = "Покупатель оформил заказ на лекарство '$itemId'.";
                    $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                }
            } 
            header("Location: table.php");
            exit();
        } else {
            echo "Ничего не выбрано для обработки.";
        }
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        //Уведомления покупатель
        if (isset($_POST['order_shopper_viewed'])) {
            $orderId = intval($_POST['order_shopper_viewed']);
            $updateOrderQuery = $conn->prepare("UPDATE orders SET checked_by_user = 1 WHERE id = ?");
            $updateOrderQuery->bind_param('i', $orderId);
            $updateOrderQuery->execute();
            $updateOrderQuery->close();
            $Actstr = "Покупатель прочитал уведомление по заказу '$orderId'.";
            $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
        }
        //уведомления поставщик
        if (isset($_POST['order_from_shopper_apply'])) {
            $orderId = intval($_POST['order_from_shopper_apply']);
            $query = $conn->prepare("SELECT quantity, drug_id FROM orders WHERE id = ?");
            $query->bind_param('i', $orderId);
            $query->execute();
            $result = $query->get_result();
            if ($result->num_rows > 0) {
                $order = $result->fetch_assoc();
                $orderQuantity = $order['quantity'];
                $drugId = $order['drug_id'];
                $drugQuery = $conn->prepare("SELECT quantity FROM drugs WHERE id = ?");
                $drugQuery->bind_param('i', $drugId);
                $drugQuery->execute();
                $drugResult = $drugQuery->get_result();
                if ($drugResult->num_rows > 0) {
                    $drug = $drugResult->fetch_assoc();
                    $availableQuantity = $drug['quantity'];
                    if ($availableQuantity >= $orderQuantity) {
                        $updateOrderQuery = $conn->prepare("UPDATE orders SET status = 'Собирается', last_updated = CURRENT_TIMESTAMP WHERE id = ?");
                        $updateOrderQuery->bind_param('i', $orderId);
                        $updateOrderQuery->execute();
                        $updateOrderQuery->close();
                        $Actstr = "Поставщик собирает заказ '$orderId'.";
                        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                        $newQuantity = $availableQuantity - $orderQuantity;
                        $updateDrugQuery = $conn->prepare("UPDATE drugs SET quantity = ? WHERE id = ?");
                        $updateDrugQuery->bind_param('ii', $newQuantity, $drugId);
                        $updateDrugQuery->execute();
                        $updateDrugQuery->close();
                        $Actstr = "Запас лекарства '$drugId' уменьшен на '$orderQuantity' штук.";
                        $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
                    } else {
                        $_SESSION['error_message'] = "Недостаточно товара на складе.";
                    }
                } else {
                    $_SESSION['error_message'] = "Лекарство не найдено.";
                }
                $query->close();
            } else {
                $_SESSION['error_message'] = "Заказ не найден.";
            }
        }
        if (isset($_POST['order_from_shopper_cancel'])) {
            $orderId = intval($_POST['order_from_shopper_cancel']);
            $deleteQuery = $conn->prepare("UPDATE orders SET status = 'Отклонен', last_updated = current_timestamp() WHERE id = ?");
            $deleteQuery->bind_param('i', $orderId);
            $deleteQuery->execute();
            $deleteQuery->close();
            $Actstr = "Администратор отклонил заказ '$orderId'";
            $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
        }
        if (isset($_POST['drug_request_status_viewed'])) {
            $orderId = intval($_POST['drug_request_status_viewed']);
            $deleteQuery = $conn->prepare("UPDATE drugs SET checked_by_user = 1 WHERE id = ?");
            $deleteQuery->bind_param('i', $orderId);
            $deleteQuery->execute();
            $deleteQuery->close();
            $Actstr = "Администратор просмотрел результат заявки на поставку лекарства '$orderId'";
            $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
        }
        //Уведомления администратор
        if (isset($_POST['drug_supply_apply'])) {
            $orderId = intval($_POST['drug_supply_apply']);
            $deleteQuery = $conn->prepare("UPDATE drugs SET is_allowed = 'Одобрено', last_updated = current_timestamp(), who_checked = ? WHERE id = ?");
            $deleteQuery->bind_param('ii', $_SESSION['user_id'], $orderId);
            $deleteQuery->execute();
            $deleteQuery->close();
            $Actstr = "Администратор одобрил заявку на поставку лекарства '$orderId'";
            $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
        }
        if (isset($_POST['drug_supply_cancel'])) {
            $orderId = intval($_POST['drug_supply_cancel']);
            $deleteQuery = $conn->prepare("UPDATE drugs SET is_allowed = 'Отклонено', last_updated = current_timestamp(), who_checked = ? WHERE id = ?");
            $deleteQuery->bind_param('ii', $_SESSION['user_id'], $orderId);
            $deleteQuery->execute();
            $deleteQuery->close();
            $Actstr = "Администратор отклонил заявку на поставку лекарства '$orderId'";
            $dbExecuter->insertAction($_SESSION['user_id'], $Actstr);
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']); 
        exit();
    }

    $query = "SELECT * FROM drugs WHERE 1=1 AND is_hiden <> 1"; // Начинаем с базового условия
    if (!empty($search_query)) {
        if (is_numeric($search_query)) {
            $query .= " AND (manufacturer_id = $search_query 
                OR provider_id = $search_query 
                OR cost = $search_query 
                OR price = $search_query 
                OR quantity = $search_query)";
        } else {
            $query .= " AND name LIKE '%$search_query%'";
        }
    }

    // Добавляем условия сортировки
    $query .= " ORDER BY $order_by $order_dir";
    $result = $conn->query($query);
    

    $query = "SELECT * FROM users ORDER BY $users_order_by $users_order_dir";
    $res = $conn->query($query);

    $query = "SELECT * FROM manufacturers ORDER BY $manufacturers_order_by $manufacturers_order_dir";
    $res_manufacturers = $conn->query($query);

    
    $User_Id = $_SESSION['user_id'];
    $query = "
    SELECT 
    orders.id AS id,
    drugs.name AS name,
    manufacturers.name AS manufacturer,
    users.name AS supplier,
    drugs.price AS price,
    orders.quantity AS quantity,
    orders.cost AS cost,
    orders.status as status,
    orders.last_updated,
    orders.is_hiden_byShopper
    FROM 
        orders
    JOIN 
        drugs ON orders.drug_id = drugs.id
    JOIN 
        manufacturers ON drugs.manufacturer_id = manufacturers.id
    JOIN 
        users ON drugs.provider_id = users.id
    WHERE 
        orders.user_id = $User_Id AND is_hiden_byShopper <> 1
    ";
    $search_query_shopper_cart = $search_query_shopper; 
    if (!empty($search_query_shopper_cart)) {
        $query .= " AND drugs.name LIKE '%$search_query_shopper_cart%' ";
    }

    $query .= "
        ORDER BY $order_by_shopper_cart $order_dir_shopper_cart
    ";

    $result_cart_user = $conn->query($query);

    $query = "
    SELECT 
        orders.id AS id,
        drugs.name AS name,
        manufacturers.name AS manufacturer,
        users.name AS customer,
        drugs.price AS price,
        orders.quantity AS quantity,
        orders.cost AS cost,
        orders.status,
        orders.last_updated,
        orders.is_hiden_byProvider
    FROM 
        orders
    JOIN 
        drugs ON orders.drug_id = drugs.id
    JOIN 
        manufacturers ON drugs.manufacturer_id = manufacturers.id
    JOIN 
        users ON orders.user_id = users.id
    WHERE 
        orders.provider_id = ? AND is_hiden_byProvider <> 1
    ";
    // and status <> 'В обработке'
    $search_query_user_supplier = $search_query_user;
    if (!empty($search_query_user_supplier)) {
        $query .= " AND drugs.name LIKE '%$search_query_user_supplier%' ";
    }

    // Добавляем сортировку
    $query .= "
        ORDER BY $order_by_supplier $order_dir_supplier
    ";

    // Выполнение запроса
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $User_Id); 
    $stmt->execute();
    $result_supplier_orders = $stmt->get_result();

    $query = "SELECT name FROM manufacturers ORDER BY name";
    $res_manuf = $conn->query($query);

    $query = "
    SELECT 
        orders.id AS id,
        drugs.name AS drugName,
        manufacturers.name AS manufacturerName,
        users.name AS userName,
        orders.quantity AS quantity,
        orders.price AS price,
        orders.cost AS cost,
        orders.status AS status,
        orders.last_updated AS update_date
    FROM 
        orders
    JOIN 
        drugs ON orders.drug_id = drugs.id
    JOIN 
        manufacturers ON orders.manufacturer_id = manufacturers.id
    JOIN 
        users ON orders.user_id = users.id
    WHERE 
        orders.provider_id = ? and status = 'В обработке'
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $orders_from_shoppers = $stmt->get_result();


    $query = "
    SELECT 
        orders.id AS id,
        drugs.name AS drugName,
        manufacturers.name AS manufacturerName,
        users.name AS providerName,
        orders.quantity AS quantity,
        orders.price AS price,
        orders.cost AS cost,
        orders.status AS status,
        orders.last_updated AS update_date
    FROM 
        orders
    JOIN 
        drugs ON orders.drug_id = drugs.id
    JOIN 
        manufacturers ON orders.manufacturer_id = manufacturers.id
    JOIN 
        users ON orders.provider_id = users.id
    WHERE 
        orders.user_id = ? AND status <> 'В обработке' AND orders.checked_by_user = 0
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $orders_shopper_feedback = $stmt->get_result();

    $query = "
    SELECT DISTINCT
        drugs.id AS id,
        drugs.name AS name, 
        manufacturers.name AS manufacturer, 
        users.name AS supplier, 
        drugs.price AS price, 
        drugs.quantity AS quantity,
        drugs.is_allowed as status,
        drugs.last_updated as update_date        
    FROM 
        drugs 
    JOIN 
        manufacturers ON drugs.manufacturer_id = manufacturers.id 
    JOIN 
        users ON drugs.provider_id = users.id 
    WHERE is_allowed = 'На рассмотрении'
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $drugs_add_requests =  $stmt->get_result();


    $query = "
    SELECT DISTINCT
        drugs.id AS id,
        drugs.name AS name, 
        manufacturers.name AS manufacturer, 
        users.name AS supplier, 
        drugs.price AS price, 
        drugs.quantity AS quantity,
        drugs.is_allowed AS status,
        drugs.last_updated AS update_date,
        drugs.checked_by_user,
        admin.name AS admin_name 
    FROM 
        drugs 
    JOIN 
        manufacturers ON drugs.manufacturer_id = manufacturers.id 
    JOIN 
        users ON drugs.provider_id = users.id
    LEFT JOIN 
        users AS admin ON drugs.who_checked = admin.id 
    WHERE 
        is_allowed <> 'На рассмотрении' 
        AND checked_by_user = 0 
        AND drugs.provider_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $drugs_add_requests_feedback =  $stmt->get_result();

    
    

} catch(mysqli_sql_exception $e){
    ?>
<div class="error-message">
	✖ <?php echo htmlspecialchars($_SESSION['sql_error_message']) . ' ' . htmlspecialchars($e->getMessage()); ?>
</div>
<?php
    exit();

} catch(Exception $e){
    ?>
<div class="error-message">
	✖ <?php echo htmlspecialchars($_SESSION['server_error_message']) . ' ' . htmlspecialchars($e->getMessage()); ?>
</div>

<?php
    exit();
}

?>