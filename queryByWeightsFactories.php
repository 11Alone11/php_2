<?php
abstract class QueryFactory {
    protected $userId;
    protected $sortParams;
    protected $searchParams;
    protected $conn;
    public function __construct($userId, $sortParams, $searchParams, $conn) {
        $this->userId = $userId;
        $this->sortParams = $sortParams;
        $this->searchParams = $searchParams;
        $this->conn = $conn;
    }

    abstract public function createMedicineQuery();
    abstract public function createOrderQuery();
    abstract protected function getCriteriaWeights();

    static public function  getQueryFactory($userId, $sortParams, $searchParams, $conn) {
        $userType = self::getUserType($userId);
    //TODO
        switch ($userType) {
            case '2': //покупатель
                return new BuyerQueryFactoryPointSystem($userId, $sortParams, $searchParams, $conn);
            case '0': //поставщик
                return new SupplierQueryFactoryPointSystem($userId, $sortParams, $searchParams, $conn);
            case '1': // админ
                return new AdminQueryFactoryPointSystem($userId, $sortParams, $searchParams, $conn);
            default:
                throw new Exception("Unknown user type");
        }
    }
    private static function getUserType($userId) {
        return $_SESSION['user_type'] ?? 'Неопределенный тип';
    }
}

class BuyerQueryFactory extends QueryFactory {
    public function createMedicineQuery() {
        $sortedPriorities = $this->getCriteriaWeights();
        $orderByParts = [];
        foreach ($sortedPriorities as $criterion => $priority) {
            switch ($criterion) {
                case 'availability':
                    $orderByParts[] = "availability " . "ASC";
                    break;
                case 'comparative_price':
                    $orderByParts[] = "comparative_price " . "ASC";
                    break;
                case 'gen_demand':
                    $orderByParts[] = "gen_demand " . "DESC";
                    break;
                case 'supl_frequency':
                    $orderByParts[] = "supl_frequency " . "DESC";
                    break;
                case 'frequency':
                    $orderByParts[] = "frequency " . "DESC";
                    break;
                case 'total_quantity':
                    $orderByParts[] = "total_quantity " . "DESC";
                    break;
            }
        }
        $orderBy = implode(', ', $orderByParts) . $this->sortParams;
        $query = "
            SELECT DISTINCT
                drugs.id AS id,
                drugs.name AS name,
                manufacturers.name AS manufacturer,
                users.name AS supplier,
                drugs.price AS price,
                drugs.quantity AS quantity,
                drugs.is_allowed,
                COALESCE(order_counts.total_quantity, 0) AS total_quantity,
                COALESCE(order_frequency.frequency, 0) AS frequency,
                COALESCE(manufSuplFrequency.supl_frequency, 0) AS supl_frequency,
                COALESCE(drug_demand.gen_demand, 0) AS gen_demand,
                COALESCE(comparative.comparative_price, 0) AS comparative_price,
                COALESCE(drugs_availability.availability, 0) AS availability
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
                WHERE user_id = ? AND is_hiden_byShopper <> 1
                GROUP BY 
                    drug_id
            ) AS order_counts ON drugs.id = order_counts.drug_id
            LEFT JOIN (
                SELECT 
                    drug_id,
                    count(*) AS frequency,
                    user_id
                FROM 
                    orders
                WHERE user_id = ? AND is_hiden_byShopper <> 1
                GROUP BY 
                    drug_id
            ) AS order_frequency ON drugs.id = order_frequency.drug_id
            LEFT JOIN(
                SELECT 
                    manufacturers.id as mid,
                    count(*) as supl_frequency,
                    orders.manufacturer_id
                FROM
                    orders
                LEFT JOIN 
                    manufacturers
                ON 
                    orders.manufacturer_id = manufacturers.id
                WHERE is_hiden_byShopper <> 1
                GROUP BY
                    manufacturer_id
            ) AS manufSuplFrequency ON drugs.manufacturer_id = manufSuplFrequency.mid
            LEFT JOIN(
                SELECT
                    count(*) AS gen_demand,
                    drug_id
                FROM
                    orders
                WHERE 
                    is_hiden_byShopper <> 1 
                GROUP BY
                    drug_id
            ) AS drug_demand ON drugs.id = drug_demand.drug_id 
            LEFT JOIN (
                SELECT 
                    d.id AS idFirstSubQuery,
                    d.name AS nameFirstSubQuery,
                    d.price / NULLIF(s.total_price, 0) AS comparative_price
                FROM
                    drugs d
                JOIN (
                    SELECT 
                        name,
                        SUM(price) AS total_price
                    FROM
                        drugs
                    WHERE 
                        is_hiden <> 1
                    GROUP BY
                        name
                ) AS s ON d.name = s.name
                WHERE 
                    is_hiden <> 1
            ) AS comparative ON drugs.id = comparative.idFirstSubQuery
            LEFT JOIN(
                SELECT
                    id,
                    CASE
                        WHEN quantity < (SELECT 
                                    SUM(POWER(quantity, 2)) / NULLIF(SUM(quantity), 0) 
                                FROM 
                                    drugs
                                WHERE 
                                    is_hiden <> 1) 
                        THEN 0
                        ELSE 1
                    END AS availability
                FROM 
                    drugs
            ) AS drugs_availability ON drugs.id = drugs_availability.id
            WHERE 
                drugs.is_allowed = 'Одобрено' 
                AND drugs.is_hiden <> 1
        ";
        
        if (!empty($this->searchParams)) {
            $query .= " AND drugs.name LIKE ? ";
        }
        $query .= "ORDER BY ". $orderBy;
        //file_put_contents('log.txt', $query, FILE_APPEND | LOCK_EX);
        $statement = $this->conn->prepare($query);
        $statement->bind_param('ii', $this->userId, $this->userId);
        if (!empty($this->searchParams)) {
            $searchParam = '%' . $this->searchParams . '%';
            $statement->bind_param('s', $searchParam);
        }
        $statement->execute();
        return $statement->get_result();
    }

    public function createOrderQuery() {
        
    }

    protected function getCriteriaWeights() {
        $query = "SELECT 
                C_quantity_in_orders,
                C_frequency_of_use, 
                C_availability_in_stock,
                C_comparative_price,
                C_demand_for_medicine,
                C_manufacturer
            FROM medicineweights
            WHERE user_id = ?";

        $statement = $this->conn->prepare($query);
        $statement->bind_param("i", $this->userId);
        $statement->execute();
        $result = $statement->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $priorities = [
                'availability' => $row['C_availability_in_stock'],
                'comparative_price' => $row['C_comparative_price'],
                'gen_demand' => $row['C_demand_for_medicine'],
                'supl_frequency' => $row['C_manufacturer'],
                'frequency' => $row['C_frequency_of_use'],
                'total_quantity' => $row['C_quantity_in_orders']
            ];
            arsort($priorities);
            //file_put_contents('log_1.txt', print_r($priorities, true), FILE_APPEND | LOCK_EX);
            return $priorities; 
        } else {
            return [
                'availability' => 0.5,
                'comparative_price' => 0.5,
                'gen_demand' => 0.5,
                'supl_frequency' => 0.5,
                'frequency' => 0.5,
                'total_quantity' => 0.5
            ];
        }
    }
}    

class SupplierQueryFactory extends QueryFactory {
    public function createMedicineQuery() {
        $sortedPriorities = $this->getCriteriaWeights();
        $orderByParts = [];
        foreach ($sortedPriorities as $criterion => $priority) {
            switch ($criterion) {
                case 'availability':
                    $orderByParts[] = "availability " . "ASC";
                    break;
                case 'comparative_price':
                    $orderByParts[] = "comparative_price " . "ASC";
                    break;
                case 'gen_demand':
                    $orderByParts[] = "gen_demand " . "DESC";
                    break;
                case 'supl_frequency':
                    $orderByParts[] = "supl_frequency " . "DESC";
                    break;
                case 'frequency':
                    $orderByParts[] = "frequency " . "DESC";
                    break;
                case 'total_quantity':
                    $orderByParts[] = "total_quantity " . "DESC";
                    break;
            }
        }
        $orderBy = implode(', ', $orderByParts) . $this->sortParams;
        $query = "
            SELECT DISTINCT
                drugs.id AS id,
                drugs.name AS name,
                manufacturers.name AS manufacturer,
                users.name AS supplier,
                drugs.price AS price,
                drugs.quantity AS quantity,
                drugs.cost AS cost,
                drugs.is_allowed,
                COALESCE(order_counts.total_quantity, 0) AS total_quantity,
                COALESCE(order_frequency.frequency, 0) AS frequency,
                COALESCE(manufSuplFrequency.supl_frequency, 0) AS supl_frequency,
                COALESCE(drug_demand.gen_demand, 0) AS gen_demand,
                COALESCE(comparative.comparative_price, 0) AS comparative_price,
                COALESCE(drugs_availability.availability, 0) AS availability
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
                    provider_id
                FROM 
                    orders
                WHERE provider_id = ? AND is_hiden_byProvider <> 1
                GROUP BY 
                    drug_id
            ) AS order_counts ON drugs.id = order_counts.drug_id
            LEFT JOIN (
                SELECT 
                    drug_id,
                    count(*) AS frequency,
                    provider_id
                FROM 
                    orders
                WHERE provider_id = ? AND is_hiden_byProvider <> 1
                GROUP BY 
                    drug_id
            ) AS order_frequency ON drugs.id = order_frequency.drug_id
            LEFT JOIN(
                SELECT 
                    manufacturers.id as mid,
                    count(*) as supl_frequency,
                    orders.manufacturer_id
                FROM
                    orders
                LEFT JOIN 
                    manufacturers
                ON 
                    orders.manufacturer_id = manufacturers.id
                WHERE is_hiden_byProvider <> 1
                GROUP BY
                    manufacturer_id
            ) AS manufSuplFrequency ON drugs.manufacturer_id = manufSuplFrequency.mid
            LEFT JOIN(
                SELECT
                    count(*) AS gen_demand,
                    drug_id
                FROM
                    orders
                WHERE 
                    is_hiden_byProvider <> 1 
                GROUP BY
                    drug_id
            ) AS drug_demand ON drugs.id = drug_demand.drug_id 
            LEFT JOIN (
                SELECT 
                    d.id AS idFirstSubQuery,
                    d.name AS nameFirstSubQuery,
                    d.price / NULLIF(s.total_price, 0) AS comparative_price
                FROM
                    drugs d
                JOIN (
                    SELECT 
                        name,
                        SUM(price) AS total_price
                    FROM
                        drugs
                    WHERE 
                        is_hiden <> 1
                    GROUP BY
                        name
                ) AS s ON d.name = s.name
                WHERE 
                    is_hiden <> 1
            ) AS comparative ON drugs.id = comparative.idFirstSubQuery
            LEFT JOIN(
                SELECT
                    id,
                    CASE
                        WHEN quantity < (SELECT 
                                    SUM(POWER(quantity, 2)) / NULLIF(SUM(quantity), 0) 
                                FROM 
                                    drugs
                                WHERE 
                                    is_hiden <> 1) 
                        THEN 0
                        ELSE 1
                    END AS availability
                FROM 
                    drugs
            ) AS drugs_availability ON drugs.id = drugs_availability.id
            WHERE 
                drugs.is_hiden <> 1 
                AND drugs.provider_id = ?
        ";
        
        if (!empty($this->searchParams)) {
            $query .= " AND drugs.name LIKE ? ";
        }
        $query .= "ORDER BY ". $orderBy;
        //file_put_contents('log.txt', $query, FILE_APPEND | LOCK_EX);
        $statement = $this->conn->prepare($query);
        $statement->bind_param('iii', $this->userId, $this->userId, $this->userId);
        if (!empty($this->searchParams)) {
            $searchParam = '%' . $this->searchParams . '%';
            $statement->bind_param('s', $searchParam);
        }
        //$statement->bind_param('i', $this->userId);
        $statement->execute();
        return $statement->get_result();
    }

    public function createOrderQuery() {
        
    }

    protected function getCriteriaWeights() {
        $query = "SELECT 
                C_quantity_in_orders,
                C_frequency_of_use, 
                C_availability_in_stock,
                C_comparative_price,
                C_demand_for_medicine,
                C_manufacturer
            FROM medicineweights
            WHERE user_id = ?";

        $statement = $this->conn->prepare($query);
        $statement->bind_param("i", $this->userId);
        $statement->execute();
        $result = $statement->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $priorities = [
                'availability' => $row['C_availability_in_stock'],
                'comparative_price' => $row['C_comparative_price'],
                'gen_demand' => $row['C_demand_for_medicine'],
                'supl_frequency' => $row['C_manufacturer'],
                'frequency' => $row['C_frequency_of_use'],
                'total_quantity' => $row['C_quantity_in_orders']
            ];
            arsort($priorities);
            return $priorities; 
        } else {
            return [
                'availability' => 0.5,
                'comparative_price' => 0.5,
                'gen_demand' => 0.5,
                'supl_frequency' => 0.5,
                'frequency' => 0.5,
                'total_quantity' => 0.5
            ];
        }
    }
}

class AdminQueryFactory extends QueryFactory {
    public function createMedicineQuery() {
        $sortedPriorities = $this->getCriteriaWeights();
        $orderByParts = [];
        foreach ($sortedPriorities as $criterion => $priority) {
            switch ($criterion) {
                case 'availability':
                    $orderByParts[] = "availability " . "ASC";
                    break;
                case 'comparative_price':
                    $orderByParts[] = "comparative_price " . "ASC";
                    break;
                case 'gen_demand':
                    $orderByParts[] = "gen_demand " . "DESC";
                    break;
                case 'supl_frequency':
                    $orderByParts[] = "supl_frequency " . "DESC";
                    break;
                case 'frequency':
                    $orderByParts[] = "frequency " . "DESC";
                    break;
                case 'total_quantity':
                    $orderByParts[] = "total_quantity " . "DESC";
                    break;
            }
        }
        $orderBy = implode(', ', $orderByParts) . $this->sortParams;
        $query = "
            SELECT DISTINCT
                drugs.id AS id,
                drugs.name AS name,
                drugs.manufacturer_id,
                drugs.provider_id,
                drugs.price AS price,
                drugs.quantity AS quantity,
                drugs.cost AS cost,
                drugs.is_allowed,
                COALESCE(order_counts.total_quantity, 0) AS total_quantity,
                COALESCE(order_frequency.frequency, 0) AS frequency,
                COALESCE(manufSuplFrequency.supl_frequency, 0) AS supl_frequency,
                COALESCE(drug_demand.gen_demand, 0) AS gen_demand,
                COALESCE(comparative.comparative_price, 0) AS comparative_price,
                COALESCE(drugs_availability.availability, 0) AS availability
            FROM 
                drugs 
            LEFT JOIN (
                SELECT 
                    drug_id,
                    SUM(quantity) AS total_quantity,
                    provider_id
                FROM 
                    orders
                WHERE provider_id = ? AND is_hiden_byProvider <> 1
                GROUP BY 
                    drug_id
            ) AS order_counts ON drugs.id = order_counts.drug_id
            LEFT JOIN (
                SELECT 
                    drug_id,
                    count(*) AS frequency,
                    provider_id
                FROM 
                    orders
                WHERE provider_id = ? AND is_hiden_byProvider <> 1
                GROUP BY 
                    drug_id
            ) AS order_frequency ON drugs.id = order_frequency.drug_id
            LEFT JOIN(
                SELECT 
                    manufacturers.id as mid,
                    count(*) as supl_frequency,
                    orders.manufacturer_id
                FROM
                    orders
                LEFT JOIN 
                    manufacturers
                ON 
                    orders.manufacturer_id = manufacturers.id
                WHERE is_hiden_byProvider <> 1
                GROUP BY
                    manufacturer_id
            ) AS manufSuplFrequency ON drugs.manufacturer_id = manufSuplFrequency.mid
            LEFT JOIN(
                SELECT
                    count(*) AS gen_demand,
                    drug_id
                FROM
                    orders
                WHERE 
                    is_hiden_byProvider <> 1 
                GROUP BY
                    drug_id
            ) AS drug_demand ON drugs.id = drug_demand.drug_id 
            LEFT JOIN (
                SELECT 
                    d.id AS idFirstSubQuery,
                    d.name AS nameFirstSubQuery,
                    d.price / NULLIF(s.total_price, 0) AS comparative_price
                FROM
                    drugs d
                JOIN (
                    SELECT 
                        name,
                        SUM(price) AS total_price
                    FROM
                        drugs
                    WHERE 
                        is_hiden <> 1
                    GROUP BY
                        name
                ) AS s ON d.name = s.name
                WHERE 
                    is_hiden <> 1
            ) AS comparative ON drugs.id = comparative.idFirstSubQuery
            LEFT JOIN(
                SELECT
                    id,
                    CASE
                        WHEN quantity < (
                SELECT 
                    quantity 
                    FROM (
                        SELECT 
                            quantity,
                            ROW_NUMBER() OVER (ORDER BY quantity) AS row_num,
                            COUNT(*) OVER () AS total_count
                        FROM 
                            drugs
                    ) AS ranked
                    WHERE row_num = Round(total_count + 1) / 2) - 1
                        THEN 0
                        ELSE 1
                    END AS availability
                FROM 
                    drugs
            ) AS drugs_availability ON drugs.id = drugs_availability.id 
        ";
        
        if (!empty($this->searchParams)) {
            $query .= " WHERE drugs.name LIKE ? ";
        }
        $query .= "ORDER BY ". $orderBy;
        //file_put_contents('log.txt', $query, FILE_APPEND | LOCK_EX);
        $statement = $this->conn->prepare($query);
        $statement->bind_param('ii', $this->userId, $this->userId);
        if (!empty($this->searchParams)) {
            $searchParam = '%' . $this->searchParams . '%';
            $statement->bind_param('s', $searchParam);
        }
        //$statement->bind_param('i', $this->userId);
        $statement->execute();
        return $statement->get_result();
    }

    public function createOrderQuery() {
        
    }

    protected function getCriteriaWeights() {
        $query = "SELECT 
                C_quantity_in_orders,
                C_frequency_of_use, 
                C_availability_in_stock,
                C_comparative_price,
                C_demand_for_medicine,
                C_manufacturer
            FROM medicineweights
            WHERE user_id = ?";

        $statement = $this->conn->prepare($query);
        $statement->bind_param("i", $this->userId);
        $statement->execute();
        $result = $statement->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $priorities = [
                'availability' => $row['C_availability_in_stock'],
                'comparative_price' => $row['C_comparative_price'],
                'gen_demand' => $row['C_demand_for_medicine'],
                'supl_frequency' => $row['C_manufacturer'],
                'frequency' => $row['C_frequency_of_use'],
                'total_quantity' => $row['C_quantity_in_orders']
            ];
            arsort($priorities);
            return $priorities; 
        } else {
            return [
                'availability' => 0.5,
                'comparative_price' => 0.5,
                'gen_demand' => 0.5,
                'supl_frequency' => 0.5,
                'frequency' => 0.5,
                'total_quantity' => 0.5
            ];
        }
    }
}

class BuyerQueryFactoryPointSystem extends QueryFactory {
    public function createMedicineQuery() {
        $sortedPriorities = $this->getCriteriaWeights();
        $orderBy = $this->sortParams;
        $total_quantity = $sortedPriorities['total_quantity'];
        $frequency = $sortedPriorities['frequency'];
        $gen_demand = $sortedPriorities['gen_demand'];
        $comparative_price = $sortedPriorities['comparative_price'];
        $availability = $sortedPriorities['availability'];
        $supl_frequency = $sortedPriorities['supl_frequency'];
        $maxPointsPreNormalized = $this->getMaxPointsPreNormalized();
        $MAX_total_quantity = $maxPointsPreNormalized['MAX_total_quantity'];
        $MAX_frequency = $maxPointsPreNormalized['MAX_frequency'];
        $MAX_supl_frequency = $maxPointsPreNormalized['MAX_supl_frequency'];
        $MAX_gen_demand = $maxPointsPreNormalized['MAX_gen_demand'];
        $MAX_comparative_price = $maxPointsPreNormalized['MAX_comparative_price'];
        $MAX_availability = $maxPointsPreNormalized['MAX_availability'];
        $totalPoints = "
            COALESCE(order_counts.total_quantity, 0)/$MAX_total_quantity*100*$total_quantity+
            COALESCE(order_frequency.frequency, 0)/$MAX_frequency*100*$frequency+
            COALESCE(manufSuplFrequency.supl_frequency, 0)/$MAX_supl_frequency*100*$supl_frequency+
            COALESCE(drug_demand.gen_demand, 0)/$MAX_gen_demand*100*$gen_demand+
            COALESCE(comparative.comparative_price, 0)/$MAX_comparative_price*100*$comparative_price+
            COALESCE(drugs_availability.availability, 0)/$MAX_availability*100*$availability as totalPoints
        ";
        $query = "
            SELECT DISTINCT
                drugs.id AS id,
                drugs.name AS name,
                manufacturers.name AS manufacturer,
                users.name AS supplier,
                drugs.price AS price,
                drugs.quantity AS quantity,
                drugs.is_allowed,
                drugs.medicinePhoto,
                $totalPoints
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
                WHERE user_id = ? AND is_hiden_byShopper <> 1
                GROUP BY 
                    drug_id
            ) AS order_counts ON drugs.id = order_counts.drug_id
            LEFT JOIN (
                SELECT 
                    drug_id,
                    count(*) AS frequency,
                    user_id
                FROM 
                    orders
                WHERE user_id = ? AND is_hiden_byShopper <> 1
                GROUP BY 
                    drug_id
            ) AS order_frequency ON drugs.id = order_frequency.drug_id
            LEFT JOIN(
                SELECT 
                    manufacturers.id as mid,
                    count(*) as supl_frequency,
                    orders.manufacturer_id
                FROM
                    orders
                LEFT JOIN 
                    manufacturers
                ON 
                    orders.manufacturer_id = manufacturers.id
                WHERE is_hiden_byShopper <> 1
                GROUP BY
                    manufacturer_id
            ) AS manufSuplFrequency ON drugs.manufacturer_id = manufSuplFrequency.mid
            LEFT JOIN(
                SELECT
                    count(*) AS gen_demand,
                    drug_id
                FROM
                    orders
                WHERE 
                    is_hiden_byShopper <> 1 
                GROUP BY
                    drug_id
            ) AS drug_demand ON drugs.id = drug_demand.drug_id 
            LEFT JOIN (
                SELECT 
                    d.id AS idFirstSubQuery,
                    d.name AS nameFirstSubQuery,
                    d.price / NULLIF(s.total_price, 0) AS comparative_price
                FROM
                    drugs d
                JOIN (
                    SELECT 
                        name,
                        SUM(price) AS total_price
                    FROM
                        drugs
                    WHERE 
                        is_hiden <> 1
                    GROUP BY
                        name
                ) AS s ON d.name = s.name
                WHERE 
                    is_hiden <> 1
            ) AS comparative ON drugs.id = comparative.idFirstSubQuery
            LEFT JOIN(
                SELECT
                    id,
                    CASE
                        WHEN quantity < (SELECT 
                                    SUM(POWER(quantity, 2)) / NULLIF(SUM(quantity), 0) 
                                FROM 
                                    drugs
                                WHERE 
                                    is_hiden <> 1) 
                        THEN 0
                        ELSE 1
                    END AS availability
                FROM 
                    drugs
            ) AS drugs_availability ON drugs.id = drugs_availability.id
            WHERE 
                drugs.is_allowed = 'Одобрено' 
                AND drugs.is_hiden <> 1
        ";
        
        if (!empty($this->searchParams)) {
            $query .= " AND drugs.name LIKE ? ";
            $query .= "ORDER BY totalPoints DESC". $orderBy;
            $searchParam = '%' . $this->searchParams . '%';
            $statement = $this->conn->prepare($query);
            $statement->bind_param('iis', $this->userId, $this->userId, $searchParam);
        } else {
            $query .= "ORDER BY totalPoints DESC". $orderBy;
            $statement = $this->conn->prepare($query);
            $statement->bind_param('ii', $this->userId, $this->userId);
        }
        $statement->execute();
        return $statement->get_result();
    }

    public function createOrderQuery() {
        
    }

    protected function getCriteriaWeights() {
        $query = "SELECT 
                C_quantity_in_orders,
                C_frequency_of_use, 
                C_availability_in_stock,
                C_comparative_price,
                C_demand_for_medicine,
                C_manufacturer
            FROM medicineweights
            WHERE user_id = ?";

        $statement = $this->conn->prepare($query);
        $statement->bind_param("i", $this->userId);
        $statement->execute();
        $result = $statement->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $priorities = [
                'availability' => $row['C_availability_in_stock'],
                'comparative_price' => $row['C_comparative_price'],
                'gen_demand' => $row['C_demand_for_medicine'],
                'supl_frequency' => $row['C_manufacturer'],
                'frequency' => $row['C_frequency_of_use'],
                'total_quantity' => $row['C_quantity_in_orders']
            ];
            arsort($priorities);
            //file_put_contents('log_1.txt', print_r($priorities, true), FILE_APPEND | LOCK_EX);
            return $priorities; 
        } else {
            return [
                'availability' => 0.5,
                'comparative_price' => 0.5,
                'gen_demand' => 0.5,
                'supl_frequency' => 0.5,
                'frequency' => 0.5,
                'total_quantity' => 0.5
            ];
        }
    }

    protected function getMaxPointsPreNormalized() {
        $query = "
            SELECT DISTINCT
                drugs.id AS id,
                drugs.name AS name,
                manufacturers.name AS manufacturer,
                users.name AS supplier,
                drugs.price AS price,
                drugs.quantity AS quantity,
                drugs.is_allowed,
                MAX(COALESCE(order_counts.total_quantity, 0)) AS MAX_total_quantity, 
                MAX(COALESCE(order_frequency.frequency, 0)) AS MAX_frequency,
                MAX(COALESCE(manufSuplFrequency.supl_frequency, 0)) AS MAX_supl_frequency,
                MAX(COALESCE(drug_demand.gen_demand, 0)) AS MAX_gen_demand,
                MAX(COALESCE(comparative.comparative_price, 0)) AS MAX_comparative_price,
                MAX(COALESCE(drugs_availability.availability, 0)) AS MAX_availability
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
                WHERE user_id = ? AND is_hiden_byShopper <> 1
                GROUP BY 
                    drug_id
            ) AS order_counts ON drugs.id = order_counts.drug_id
            LEFT JOIN (
                SELECT 
                    drug_id,
                    count(*) AS frequency,
                    user_id
                FROM 
                    orders
                WHERE user_id = ? AND is_hiden_byShopper <> 1
                GROUP BY 
                    drug_id
            ) AS order_frequency ON drugs.id = order_frequency.drug_id
            LEFT JOIN(
                SELECT 
                    manufacturers.id as mid,
                    count(*) as supl_frequency,
                    orders.manufacturer_id
                FROM
                    orders
                LEFT JOIN 
                    manufacturers
                ON 
                    orders.manufacturer_id = manufacturers.id
                WHERE is_hiden_byShopper <> 1
                GROUP BY
                    manufacturer_id
            ) AS manufSuplFrequency ON drugs.manufacturer_id = manufSuplFrequency.mid
            LEFT JOIN(
                SELECT
                    count(*) AS gen_demand,
                    drug_id
                FROM
                    orders
                WHERE 
                    is_hiden_byShopper <> 1 
                GROUP BY
                    drug_id
            ) AS drug_demand ON drugs.id = drug_demand.drug_id 
            LEFT JOIN (
                SELECT 
                    d.id AS idFirstSubQuery,
                    d.name AS nameFirstSubQuery,
                    d.price / NULLIF(s.total_price, 0) AS comparative_price
                FROM
                    drugs d
                JOIN (
                    SELECT 
                        name,
                        SUM(price) AS total_price
                    FROM
                        drugs
                    WHERE 
                        is_hiden <> 1
                    GROUP BY
                        name
                ) AS s ON d.name = s.name
                WHERE 
                    is_hiden <> 1
            ) AS comparative ON drugs.id = comparative.idFirstSubQuery
            LEFT JOIN(
                SELECT
                    id,
                    CASE
                        WHEN quantity < (SELECT 
                                    SUM(POWER(quantity, 2)) / NULLIF(SUM(quantity), 0) 
                                FROM 
                                    drugs
                                WHERE 
                                    is_hiden <> 1) 
                        THEN 0
                        ELSE 1
                    END AS availability
                FROM 
                    drugs
            ) AS drugs_availability ON drugs.id = drugs_availability.id
            WHERE 
                drugs.is_allowed = 'Одобрено' 
                AND drugs.is_hiden <> 1
        ";

        $statement = $this->conn->prepare($query);
        $statement->bind_param("ii", $this->userId, $this->userId);
        $statement->execute();
        $result = $statement->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $priorities = [
                'MAX_total_quantity' => $row['MAX_total_quantity'],
                'MAX_frequency' => $row['MAX_frequency'],
                'MAX_supl_frequency' => $row['MAX_supl_frequency'],
                'MAX_gen_demand' => $row['MAX_gen_demand'],
                'MAX_comparative_price' => $row['MAX_comparative_price'],
                'MAX_availability' => $row['MAX_availability']
            ];
            arsort($priorities);
            //file_put_contents('log_1.txt', print_r($priorities, true), FILE_APPEND | LOCK_EX);
            return $priorities; 
        } else {
            return [
                'MAX_total_quantity' => 0.5,
                'MAX_frequency' => 0.5,
                'MAX_supl_frequency' => 0.5,
                'MAX_gen_demand' => 0.5,
                'MAX_comparative_price' => 0.5,
                'MAX_availability' => 0.5
            ];
        }
    }
}    

class SupplierQueryFactoryPointSystem extends QueryFactory {
    public function createMedicineQuery() {
        $sortedPriorities = $this->getCriteriaWeights();
        $orderBy = $this->sortParams;
        $total_quantity = $sortedPriorities['total_quantity'];
        $frequency = $sortedPriorities['frequency'];
        $gen_demand = $sortedPriorities['gen_demand'];
        $comparative_price = $sortedPriorities['comparative_price'];
        $availability = $sortedPriorities['availability'];
        $supl_frequency = $sortedPriorities['supl_frequency'];
        $maxPointsPreNormalized = $this->getMaxPointsPreNormalized();
        $MAX_total_quantity = $maxPointsPreNormalized['MAX_total_quantity'];
        $MAX_frequency = $maxPointsPreNormalized['MAX_frequency'];
        $MAX_supl_frequency = $maxPointsPreNormalized['MAX_supl_frequency'];
        $MAX_gen_demand = $maxPointsPreNormalized['MAX_gen_demand'];
        $MAX_comparative_price = $maxPointsPreNormalized['MAX_comparative_price'];
        $MAX_availability = $maxPointsPreNormalized['MAX_availability'];
        //file_put_contents('debug.txt', "$MAX_total_quantity $MAX_frequency $MAX_supl_frequency $MAX_gen_demand $MAX_comparative_price $MAX_availability");
        //file_put_contents('debug.txt', "$total_quantity $frequency $gen_demand $comparative_price $availability $supl_frequency");
        $totalPoints = "
            COALESCE(order_counts.total_quantity, 0)/$MAX_total_quantity*100*$total_quantity+
            COALESCE(order_frequency.frequency, 0)/$MAX_frequency*100*$frequency+
            COALESCE(manufSuplFrequency.supl_frequency, 0)/$MAX_supl_frequency*100*$supl_frequency+
            COALESCE(drug_demand.gen_demand, 0)/$MAX_gen_demand*100*$gen_demand+
            COALESCE(comparative.comparative_price, 0)/$MAX_comparative_price*100*$comparative_price+
            COALESCE(drugs_availability.availability, 0)/$MAX_availability*100*$availability as totalPoints
        ";
        $query = "
            SELECT DISTINCT
                drugs.id AS id,
                drugs.name AS name,
                manufacturers.name AS manufacturer,
                users.name AS supplier,
                drugs.price AS price,
                drugs.quantity AS quantity,
                drugs.cost AS cost,
                drugs.is_allowed,
                drugs.medicinePhoto,
                $totalPoints
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
                    provider_id
                FROM 
                    orders
                WHERE provider_id = ? AND is_hiden_byProvider <> 1
                GROUP BY 
                    drug_id
            ) AS order_counts ON drugs.id = order_counts.drug_id
            LEFT JOIN (
                SELECT 
                    drug_id,
                    count(*) AS frequency,
                    provider_id
                FROM 
                    orders
                WHERE provider_id = ? AND is_hiden_byProvider <> 1
                GROUP BY 
                    drug_id
            ) AS order_frequency ON drugs.id = order_frequency.drug_id
            LEFT JOIN(
                SELECT 
                    manufacturers.id as mid,
                    count(*) as supl_frequency,
                    orders.manufacturer_id
                FROM
                    orders
                LEFT JOIN 
                    manufacturers
                ON 
                    orders.manufacturer_id = manufacturers.id
                WHERE is_hiden_byProvider <> 1
                GROUP BY
                    manufacturer_id
            ) AS manufSuplFrequency ON drugs.manufacturer_id = manufSuplFrequency.mid
            LEFT JOIN(
                SELECT
                    count(*) AS gen_demand,
                    drug_id
                FROM
                    orders
                WHERE 
                    is_hiden_byProvider <> 1 
                GROUP BY
                    drug_id
            ) AS drug_demand ON drugs.id = drug_demand.drug_id 
            LEFT JOIN (
                SELECT 
                    d.id AS idFirstSubQuery,
                    d.name AS nameFirstSubQuery,
                    d.price / NULLIF(s.total_price, 0) AS comparative_price
                FROM
                    drugs d
                JOIN (
                    SELECT 
                        name,
                        SUM(price) AS total_price
                    FROM
                        drugs
                    WHERE 
                        is_hiden <> 1
                    GROUP BY
                        name
                ) AS s ON d.name = s.name
                WHERE 
                    is_hiden <> 1
            ) AS comparative ON drugs.id = comparative.idFirstSubQuery
            LEFT JOIN(
                SELECT
                    id,
                    CASE
                        WHEN quantity < (SELECT 
                                    SUM(POWER(quantity, 2)) / NULLIF(SUM(quantity), 0) 
                                FROM 
                                    drugs
                                WHERE 
                                    is_hiden <> 1) 
                        THEN 0
                        ELSE 1
                    END AS availability
                FROM 
                    drugs
            ) AS drugs_availability ON drugs.id = drugs_availability.id
            WHERE 
                drugs.is_hiden <> 1 
                AND drugs.provider_id = ?
        ";
        
        if (!empty($this->searchParams)) {
            $query .= " AND drugs.name LIKE ? ";
            $query .= "ORDER BY totalPoints DESC". $orderBy;
            $searchParam = '%' . $this->searchParams . '%';
            $statement = $this->conn->prepare($query);
            $statement->bind_param('iiis', $this->userId, $this->userId, $this->userId, $searchParam);
        } else {
            $query .= "ORDER BY totalPoints DESC". $orderBy;
            $statement = $this->conn->prepare($query);
            $statement->bind_param('iii', $this->userId, $this->userId, $this->userId);
        }
        //$statement->bind_param('i', $this->userId);
        $statement->execute();
        return $statement->get_result();
    }

    public function createOrderQuery() {
        
    }

    protected function getCriteriaWeights() {
        $query = "SELECT 
                C_quantity_in_orders,
                C_frequency_of_use, 
                C_availability_in_stock,
                C_comparative_price,
                C_demand_for_medicine,
                C_manufacturer
            FROM medicineweights
            WHERE user_id = ?";

        $statement = $this->conn->prepare($query);
        $statement->bind_param("i", $this->userId);
        $statement->execute();
        $result = $statement->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $priorities = [
                'availability' => $row['C_availability_in_stock'],
                'comparative_price' => $row['C_comparative_price'],
                'gen_demand' => $row['C_demand_for_medicine'],
                'supl_frequency' => $row['C_manufacturer'],
                'frequency' => $row['C_frequency_of_use'],
                'total_quantity' => $row['C_quantity_in_orders']
            ];
            arsort($priorities);
            return $priorities; 
        } else {
            return [
                'availability' => 0.5,
                'comparative_price' => 0.5,
                'gen_demand' => 0.5,
                'supl_frequency' => 0.5,
                'frequency' => 0.5,
                'total_quantity' => 0.5
            ];
        }
    }
    
    protected function getMaxPointsPreNormalized() {
        $query = "
            SELECT DISTINCT
                drugs.id AS id,
                drugs.name AS name,
                manufacturers.name AS manufacturer,
                users.name AS supplier,
                drugs.price AS price,
                drugs.quantity AS quantity,
                drugs.is_allowed,
                MAX(COALESCE(order_counts.total_quantity, 0)) AS MAX_total_quantity, 
                MAX(COALESCE(order_frequency.frequency, 0)) AS MAX_frequency,
                MAX(COALESCE(manufSuplFrequency.supl_frequency, 0)) AS MAX_supl_frequency,
                MAX(COALESCE(drug_demand.gen_demand, 0)) AS MAX_gen_demand,
                MAX(COALESCE(comparative.comparative_price, 0)) AS MAX_comparative_price,
                MAX(COALESCE(drugs_availability.availability, 0)) AS MAX_availability
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
                WHERE is_hiden_byShopper <> 1
                GROUP BY 
                    user_id, drug_id
            ) AS order_counts ON drugs.id = order_counts.drug_id
            LEFT JOIN (
                SELECT 
                    drug_id,
                    count(*) AS frequency,
                    user_id
                FROM 
                    orders
                WHERE  is_hiden_byShopper <> 1
                GROUP BY 
                    user_id, drug_id
            ) AS order_frequency ON drugs.id = order_frequency.drug_id
            LEFT JOIN(
                SELECT 
                    manufacturers.id as mid,
                    count(*) as supl_frequency,
                    orders.manufacturer_id
                FROM
                    orders
                LEFT JOIN 
                    manufacturers
                ON 
                    orders.manufacturer_id = manufacturers.id
                WHERE is_hiden_byShopper <> 1
                GROUP BY
                    manufacturer_id
            ) AS manufSuplFrequency ON drugs.manufacturer_id = manufSuplFrequency.mid
            LEFT JOIN(
                SELECT
                    count(*) AS gen_demand,
                    drug_id
                FROM
                    orders
                WHERE 
                    is_hiden_byShopper <> 1 
                GROUP BY
                    drug_id
            ) AS drug_demand ON drugs.id = drug_demand.drug_id 
            LEFT JOIN (
                SELECT 
                    d.id AS idFirstSubQuery,
                    d.name AS nameFirstSubQuery,
                    d.price / NULLIF(s.total_price, 0) AS comparative_price
                FROM
                    drugs d
                JOIN (
                    SELECT 
                        name,
                        SUM(price) AS total_price
                    FROM
                        drugs
                    WHERE 
                        is_hiden <> 1
                    GROUP BY
                        name
                ) AS s ON d.name = s.name
                WHERE 
                    is_hiden <> 1
            ) AS comparative ON drugs.id = comparative.idFirstSubQuery
            LEFT JOIN(
                SELECT
                    id,
                    CASE
                        WHEN quantity < (SELECT 
                                    SUM(POWER(quantity, 2)) / NULLIF(SUM(quantity), 0) 
                                FROM 
                                    drugs
                                WHERE 
                                    is_hiden <> 1) 
                        THEN 0
                        ELSE 1
                    END AS availability
                FROM 
                    drugs
            ) AS drugs_availability ON drugs.id = drugs_availability.id
            WHERE 
                drugs.is_allowed = 'Одобрено' 
                AND drugs.is_hiden <> 1
        ";

        $statement = $this->conn->prepare($query);
        //$statement->bind_param("ii", $this->userId, $this->userId);
        $statement->execute();
        $result = $statement->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $priorities = [
                'MAX_total_quantity' => $row['MAX_total_quantity'],
                'MAX_frequency' => $row['MAX_frequency'],
                'MAX_supl_frequency' => $row['MAX_supl_frequency'],
                'MAX_gen_demand' => $row['MAX_gen_demand'],
                'MAX_comparative_price' => $row['MAX_comparative_price'],
                'MAX_availability' => $row['MAX_availability']
            ];
            arsort($priorities);
            //file_put_contents('log_1.txt', print_r($priorities, true), FILE_APPEND | LOCK_EX);
            return $priorities; 
        } else {
            return [
                'MAX_total_quantity' => 0.5,
                'MAX_frequency' => 0.5,
                'MAX_supl_frequency' => 0.5,
                'MAX_gen_demand' => 0.5,
                'MAX_comparative_price' => 0.5,
                'MAX_availability' => 0.5
            ];
        }
    }
}

class AdminQueryFactoryPointSystem extends QueryFactory {
    public function createMedicineQuery() {
        $sortedPriorities = $this->getCriteriaWeights();
        $orderBy = $this->sortParams;
        $total_quantity = $sortedPriorities['total_quantity'];
        $frequency = $sortedPriorities['frequency'];
        $gen_demand = $sortedPriorities['gen_demand'];
        $comparative_price = $sortedPriorities['comparative_price'];
        $availability = $sortedPriorities['availability'];
        $supl_frequency = $sortedPriorities['supl_frequency'];
        $maxPointsPreNormalized = $this->getMaxPointsPreNormalized();
        $MAX_total_quantity = $maxPointsPreNormalized['MAX_total_quantity'];
        $MAX_frequency = $maxPointsPreNormalized['MAX_frequency'];
        $MAX_supl_frequency = $maxPointsPreNormalized['MAX_supl_frequency'];
        $MAX_gen_demand = $maxPointsPreNormalized['MAX_gen_demand'];
        $MAX_comparative_price = $maxPointsPreNormalized['MAX_comparative_price'];
        $MAX_availability = $maxPointsPreNormalized['MAX_availability'];
        // file_put_contents('debug.txt', "$MAX_total_quantity $MAX_frequency $MAX_supl_frequency $MAX_gen_demand $MAX_comparative_price $MAX_availability");
        // file_put_contents('debug.txt', "$total_quantity $frequency $gen_demand $comparative_price $availability $supl_frequency");
        
        $totalPoints = "
            COALESCE(order_counts.total_quantity, 0)/$MAX_total_quantity*100*$total_quantity+
            COALESCE(order_frequency.frequency, 0)/$MAX_frequency*100*$frequency+
            COALESCE(manufSuplFrequency.supl_frequency, 0)/$MAX_supl_frequency*100*$supl_frequency+
            COALESCE(drug_demand.gen_demand, 0)/$MAX_gen_demand*100*$gen_demand+
            COALESCE(comparative.comparative_price, 0)/$MAX_comparative_price*100*$comparative_price+
            COALESCE(drugs_availability.availability, 0)/$MAX_availability*100*$availability as totalPoints
        ";
        $query = "
            SELECT DISTINCT
                drugs.id AS id,
                drugs.name AS name,
                drugs.manufacturer_id,
                drugs.provider_id,
                drugs.price AS price,
                drugs.quantity AS quantity,
                drugs.cost AS cost,
                drugs.is_allowed,
                drugs.medicinePhoto,
                $totalPoints
            FROM 
                drugs 
            LEFT JOIN (
                SELECT 
                    drug_id,
                    SUM(quantity) AS total_quantity,
                    provider_id
                FROM 
                    orders
                WHERE provider_id = ? AND is_hiden_byProvider <> 1
                GROUP BY 
                    drug_id
            ) AS order_counts ON drugs.id = order_counts.drug_id
            LEFT JOIN (
                SELECT 
                    drug_id,
                    count(*) AS frequency,
                    provider_id
                FROM 
                    orders
                WHERE provider_id = ? AND is_hiden_byProvider <> 1
                GROUP BY 
                    drug_id
            ) AS order_frequency ON drugs.id = order_frequency.drug_id
            LEFT JOIN(
                SELECT 
                    manufacturers.id as mid,
                    count(*) as supl_frequency,
                    orders.manufacturer_id
                FROM
                    orders
                LEFT JOIN 
                    manufacturers
                ON 
                    orders.manufacturer_id = manufacturers.id
                WHERE is_hiden_byProvider <> 1
                GROUP BY
                    manufacturer_id
            ) AS manufSuplFrequency ON drugs.manufacturer_id = manufSuplFrequency.mid
            LEFT JOIN(
                SELECT
                    count(*) AS gen_demand,
                    drug_id
                FROM
                    orders
                WHERE 
                    is_hiden_byProvider <> 1 
                GROUP BY
                    drug_id
            ) AS drug_demand ON drugs.id = drug_demand.drug_id 
            LEFT JOIN (
                SELECT 
                    d.id AS idFirstSubQuery,
                    d.name AS nameFirstSubQuery,
                    d.price / NULLIF(s.total_price, 0) AS comparative_price
                FROM
                    drugs d
                JOIN (
                    SELECT 
                        name,
                        SUM(price) AS total_price
                    FROM
                        drugs
                    WHERE 
                        is_hiden <> 1
                    GROUP BY
                        name
                ) AS s ON d.name = s.name
                WHERE 
                    is_hiden <> 1
            ) AS comparative ON drugs.id = comparative.idFirstSubQuery
            LEFT JOIN(
                SELECT
                    id,
                    CASE
                        WHEN quantity < (SELECT 
                                    SUM(POWER(quantity, 2)) / NULLIF(SUM(quantity), 0) 
                                FROM 
                                    drugs
                                WHERE 
                                    is_hiden <> 1) 
                        THEN 0
                        ELSE 1
                    END AS availability
                FROM 
                    drugs
            ) AS drugs_availability ON drugs.id = drugs_availability.id 
        ";
        
        if (!empty($this->searchParams)) {
            $query .= " AND drugs.name LIKE ? ";
            $query .= "ORDER BY totalPoints DESC". $orderBy;
            $searchParam = '%' . $this->searchParams . '%';
            $statement = $this->conn->prepare($query);
            $statement->bind_param('iis', $this->userId, $this->userId, $searchParam);
        } else {
            $query .= "ORDER BY totalPoints DESC". $orderBy;
            $statement = $this->conn->prepare($query);
            $statement->bind_param('ii', $this->userId, $this->userId);
        }
        //$statement->bind_param('i', $this->userId);
        $statement->execute();
        return $statement->get_result();
    }

    public function createOrderQuery() {
        
    }

    protected function getCriteriaWeights() {
        $query = "SELECT 
                C_quantity_in_orders,
                C_frequency_of_use, 
                C_availability_in_stock,
                C_comparative_price,
                C_demand_for_medicine,
                C_manufacturer
            FROM medicineweights
            WHERE user_id = ?";

        $statement = $this->conn->prepare($query);
        $statement->bind_param("i", $this->userId);
        $statement->execute();
        $result = $statement->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $priorities = [
                'availability' => $row['C_availability_in_stock'],
                'comparative_price' => $row['C_comparative_price'],
                'gen_demand' => $row['C_demand_for_medicine'],
                'supl_frequency' => $row['C_manufacturer'],
                'frequency' => $row['C_frequency_of_use'],
                'total_quantity' => $row['C_quantity_in_orders']
            ];
            arsort($priorities);
            return $priorities; 
        } else {
            return [
                'availability' => 0.5,
                'comparative_price' => 0.5,
                'gen_demand' => 0.5,
                'supl_frequency' => 0.5,
                'frequency' => 0.5,
                'total_quantity' => 0.5
            ];
        }
    }
    
    protected function getMaxPointsPreNormalized() {
        $query = "
            SELECT DISTINCT
                drugs.id AS id,
                drugs.name AS name,
                manufacturers.name AS manufacturer,
                users.name AS supplier,
                drugs.price AS price,
                drugs.quantity AS quantity,
                drugs.is_allowed,
                MAX(COALESCE(order_counts.total_quantity, 0)) AS MAX_total_quantity, 
                MAX(COALESCE(order_frequency.frequency, 0)) AS MAX_frequency,
                MAX(COALESCE(manufSuplFrequency.supl_frequency, 0)) AS MAX_supl_frequency,
                MAX(COALESCE(drug_demand.gen_demand, 0)) AS MAX_gen_demand,
                MAX(COALESCE(comparative.comparative_price, 0)) AS MAX_comparative_price,
                MAX(COALESCE(drugs_availability.availability, 0)) AS MAX_availability
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
                WHERE is_hiden_byShopper <> 1
                GROUP BY 
                    user_id, drug_id
            ) AS order_counts ON drugs.id = order_counts.drug_id
            LEFT JOIN (
                SELECT 
                    drug_id,
                    count(*) AS frequency,
                    user_id
                FROM 
                    orders
                WHERE is_hiden_byShopper <> 1
                GROUP BY 
                   user_id, drug_id
            ) AS order_frequency ON drugs.id = order_frequency.drug_id
            LEFT JOIN(
                SELECT 
                    manufacturers.id as mid,
                    count(*) as supl_frequency,
                    orders.manufacturer_id
                FROM
                    orders
                LEFT JOIN 
                    manufacturers
                ON 
                    orders.manufacturer_id = manufacturers.id
                WHERE is_hiden_byShopper <> 1
                GROUP BY
                    manufacturer_id
            ) AS manufSuplFrequency ON drugs.manufacturer_id = manufSuplFrequency.mid
            LEFT JOIN(
                SELECT
                    count(*) AS gen_demand,
                    drug_id
                FROM
                    orders
                WHERE 
                    is_hiden_byShopper <> 1 
                GROUP BY
                    drug_id
            ) AS drug_demand ON drugs.id = drug_demand.drug_id 
            LEFT JOIN (
                SELECT 
                    d.id AS idFirstSubQuery,
                    d.name AS nameFirstSubQuery,
                    d.price / NULLIF(s.total_price, 0) AS comparative_price
                FROM
                    drugs d
                JOIN (
                    SELECT 
                        name,
                        SUM(price) AS total_price
                    FROM
                        drugs
                    WHERE 
                        is_hiden <> 1
                    GROUP BY
                        name
                ) AS s ON d.name = s.name
                WHERE 
                    is_hiden <> 1
            ) AS comparative ON drugs.id = comparative.idFirstSubQuery
            LEFT JOIN(
                SELECT
                    id,
                    CASE
                        WHEN quantity < (SELECT 
                                    SUM(POWER(quantity, 2)) / NULLIF(SUM(quantity), 0) 
                                FROM 
                                    drugs
                                WHERE 
                                    is_hiden <> 1) 
                        THEN 0
                        ELSE 1
                    END AS availability
                FROM 
                    drugs
            ) AS drugs_availability ON drugs.id = drugs_availability.id
            WHERE 
                drugs.is_allowed = 'Одобрено' 
                AND drugs.is_hiden <> 1
        ";

        $statement = $this->conn->prepare($query);
        //$statement->bind_param("ii", $this->userId, $this->userId);
        $statement->execute();
        $result = $statement->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $priorities = [
                'MAX_total_quantity' => $row['MAX_total_quantity'],
                'MAX_frequency' => $row['MAX_frequency'],
                'MAX_supl_frequency' => $row['MAX_supl_frequency'],
                'MAX_gen_demand' => $row['MAX_gen_demand'],
                'MAX_comparative_price' => $row['MAX_comparative_price'],
                'MAX_availability' => $row['MAX_availability']
            ];
            arsort($priorities);
            //file_put_contents('log_1.txt', print_r($priorities, true), FILE_APPEND | LOCK_EX);
            return $priorities; 
        } else {
            return [
                'MAX_total_quantity' => 0.5,
                'MAX_frequency' => 0.5,
                'MAX_supl_frequency' => 0.5,
                'MAX_gen_demand' => 0.5,
                'MAX_comparative_price' => 0.5,
                'MAX_availability' => 0.5
            ];
        }
    }
}

?>