<?php
abstract class QueryFactory {
    protected $userId;

    public function __construct($userId) {
        $this->userId = $userId;
    }

    abstract public function createMedicineQuery();
    abstract public function createOrderQuery();
    static private function  getQueryFactory($userId) {
        $userType = getUserType($userId);
    //TODO
        switch ($userType) {
            case 'buyer':
                return new BuyerQueryFactory($userId);
            case 'supplier':
                return new SupplierQueryFactory($userId);
            case 'admin':
                return new AdminQueryFactory($userId);
            default:
                throw new Exception("Unknown user type");
        }
    }
}

class BuyerQueryFactory extends QueryFactory {
    public function createMedicineQuery() {
        
    }

    public function createOrderQuery() {
        
    }
}

class SupplierQueryFactory extends QueryFactory {
    public function createMedicineQuery() {
       
    }

    public function createOrderQuery() {
       
    }
}

class AdminQueryFactory extends QueryFactory {
    public function createMedicineQuery() {
        // Получение весов критериев из базы данных
        $criteriaWeights = $this->getCriteriaWeights();
        
        // Начало SQL-запроса
        $query = "SELECT * FROM medicine WHERE is_hiden = 0";
        
        // Добавление условий на основе весов критериев
        $conditions = [];

        // Упорядочиваем критерии по весу в порядке убывания
        arsort($criteriaWeights);

        // Цикл по упорядоченным критериям
        foreach ($criteriaWeights as $criteria => $weight) {
            if ($weight <= 0) continue;

            switch ($criteria) {
                case 'Количество заказов':
                    $conditions[] = "id IN (SELECT drug_id FROM orders GROUP BY drug_id ORDER BY COUNT(*) DESC LIMIT 10)";
                    break;
                
                case 'Частота использования':
                    $conditions[] = "id IN (SELECT drug_id FROM orders WHERE user_id = {$this->userId} GROUP BY drug_id ORDER BY COUNT(*) DESC LIMIT 10)";
                    break;

                case 'Наличие на складе':
                    $conditions[] = "quantity > 0";
                    break;

                case 'Сравнительная цена':
                    // Пример: добавляем условие на минимальную цену
                    $minPrice = 10; // Задайте минимальную цену по вашему усмотрению
                    $conditions[] = "price >= {$minPrice}";
                    break;

                case 'Спрос на лекарство':
                    $conditions[] = "id IN (SELECT drug_id FROM orders GROUP BY drug_id ORDER BY COUNT(*) DESC)";
                    break;

                case 'Производитель':
                    // Пример: фильтрация по конкретному производителю
                    $manufacturerId = 1; // Замените на нужный ID производителя
                    $conditions[] = "manufacturer_id = {$manufacturerId}";
                    break;
            }
        }

        // Объединяем условия, если они есть
        if (!empty($conditions)) {
            $query .= " AND " . implode(" AND ", $conditions);
        }

        return $query;
    }

    public function createOrderQuery() {

    }
}




?>