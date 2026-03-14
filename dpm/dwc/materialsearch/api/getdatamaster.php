<?php
$project = $_GET["filter"];

// Product check
$product_query = "
    SELECT Product
    FROM 
        dbo.OneX_ProjectDetails 
    WHERE 
        FactoryNumber = :p2
";
$product = DbManager::fetchPDOQueryData('MTool_INKWA', $product_query, [":p2" => $project])["data"][0]['Product'];

if ($product == "Components" || $product == "Component") {
    // Material Search Pano Dropdown Itemleri
    $query = "
        SELECT 
            RIGHT('000000' + CAST(REPLACE(ISNULL(PosNo, ''), ',', '') AS VARCHAR(6)), 6) AS PosNo
        FROM 
            dbo.OneX_SapPosData 
        WHERE 
            ProjectNo = :p1
            AND LEN(ISNULL(PosNo, '')) > 0
    ";
} else {
    $query = "
            SELECT 
                RIGHT('000000' + CAST(REPLACE(ISNULL(PosNo, ''), ',', '') AS VARCHAR(6)), 6) AS PosNo,
                LocationCode AS PanelName,
                TypicalCode AS TypicalName
            FROM 
                dbo.OneX_SapPosData 
            WHERE 
                ProjectNo = :p1
                AND LEN(ISNULL(PosNo, '')) > 0
                AND LEN(ISNULL(LocationCode, '')) > 0
                AND LEN(ISNULL(TypicalCode, '')) > 0
        ";
}
$result = DbManager::fetchPDOQueryData('MTool_INKWA', $query, [":p1" => $project])["data"];

$items = array();
foreach ($result as $row) {
    if ($product == "Components" || $product == "Component") {
        $items[] = $row['PosNo'];
    } else {
        $items[] = $row['PosNo']."|".$row['PanelName']."|".$row['TypicalName'];
    }
}
$items = array_unique($items);


header('Content-type: application/json');
echo json_encode([
    "panels" => $items,
    "kmats" => []
]); exit;
