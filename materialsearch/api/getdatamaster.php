<?php
$project = $_GET["filter"];

// Material Search Pano Dropdown Itemleri
$query = "
    SELECT 
        DISTINCT `ItemNumber`
    FROM `rpa`.`sap_spiridon_001` 
    WHERE SalesOrder = :p1
    ORDER BY `ItemNumber`
";
$result = DbManager::fetchPDOQueryData("rpa", $query, [":p1" => $project])["data"];

$items = array();
foreach ($result as $row) {
    $items[] = $row['ItemNumber'];
}
$items = array_unique($items);


header('Content-type: application/json');
echo json_encode([
    "panels" => $items,
    "kmats" => []
]); exit;
