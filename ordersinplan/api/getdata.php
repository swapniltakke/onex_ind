<?php
SharedManager::checkAuthToModule(1);
header('Content-Type:application/json;charset=utf-8;');
$breakerLines = ["Line-SION-M40-31plus", "Line-SION-M25", "Line-SION-M36"];

$query = "
    SELECT 
        projectNo, 
        productionLine, 
        ProjectName,
        productionWeek, 
        productionday, 
        productId, 
        SUM(quantityonday) AS quantityonday 
    FROM
         assembly_plan_mv
    WHERE 
        revisionNr = (SELECT MAX(revisionNr) FROM assembly_plan_mv_index ) AND 
        ProjectNo!='' 
    GROUP BY 
        projectNo,
        productionLine
";
$result = DbManager::fetchPDOQueryData('planning', $query)["data"];
$output = [];
foreach ($result as $row) {
    $projectNo = $row['projectNo'];
    $line = $row['productionLine'];
    $projectName = $row['ProjectName'];
    $productionWeek = $row['productionWeek'];
    $productionDay = $row['productionday'];
    $quantityOnDay = $row['quantityonday'];
    $productId = $row['productId'];

    if(in_array($line, $breakerLines))
        $productId = "Breaker/ $productId";

    $output[$projectNo] = [
        "projectno" => $projectNo,
        "line" => $line,
        "projectname" => $projectName,
        "dates" => [
            "assdate" => $productionDay,
            "asstime" => $quantityOnDay,
            "mecrelplantime" => "",
            "mecrelplandate" => "",
            "mecrelactualtime" => "",
            "mecrelactualdate" => "",
            "cwdate" => $productionWeek,
            "cwtime" => $productionWeek
        ],
        "paneltype" => $productId,
        "qty" => "",
        "om" => ""
    ];
}

$projectNos = array_keys($output);

$mtoolQuery = "
    SELECT 
        T.*,
        T2.OrderManager
    FROM(
        SELECT 
            FactoryNumber,
            Qty
        FROM 
            dbo.OneX_ProjectTrackingEE 
        WHERE 
            FactoryNumber IN(:projectNos)
    ) AS T
    JOIN(
        SELECT 
            FactoryNumber, 
            OrderManager
        FROM
            dbo.OneX_ProjectContacts
       WHERE 
            FactoryNumber IN(:projectNos)
    ) AS T2
    ON
        T.FactoryNumber = T2.FactoryNumber
";
$result = DbManager::fetchPDOQueryData('MTool_INKWA', $mtoolQuery, [':projectNos' => $projectNos])["data"];

foreach ($result as $row) {
    $factoryNumber = $row["FactoryNumber"];
    $qty = $row["Qty"];
    $orderManager = $row["OrderManager"];

    $output[$factoryNumber]["qty"] = $qty;
    $output[$factoryNumber]["om"] = $orderManager;
}

echo json_encode(["data" => array_values($output)]);

