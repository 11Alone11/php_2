<?php
session_start();
require_once('../db.php');
function measureExecutionTime($callback){
    $start=microtime(true);
    $result=$callback();
    $end=microtime(true);
    return['result'=>$result,'time'=>($end-$start)*1000];
}
$sortColumn=$_GET['sort']??'name';
$sortOrder=$_GET['order']??'ASC';
$searchTerm=$_GET['search']??'';
$sql="SELECT d.id,
    d.name,
    d.price,
    d.quantity,
    m.name 
    as manufacturer,
    u.name as supplier,
    COUNT(o.id) as order_count 
    FROM drugs d LEFT JOIN manufacturers m 
    ON d.manufacturer_id=m.id LEFT JOIN users u 
    ON d.provider_id=u.id LEFT JOIN orders o 
    ON d.id=o.drug_id 
    WHERE d.name LIKE ? OR m.name LIKE ? 
    GROUP BY d.id 
    ORDER BY ".$sortColumn." ".$sortOrder;
$executionData=measureExecutionTime(function() use ($conn,$sql,$searchTerm){
    $stmt=$conn->prepare($sql);
    $searchPattern="%$searchTerm%";
    $stmt->bind_param("ss",$searchPattern,$searchPattern);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
});
$drugs=$executionData['result'];
$executionTime=$executionData['time'];
header('Content-Type: application/json');
echo json_encode(['data'=>$drugs,'executionTime'=>$executionTime,'method'=>'SQL Sort']);
?>
