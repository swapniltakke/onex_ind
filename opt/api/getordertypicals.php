<?php

SharedManager::checkAuthToModule(5);

$project = $_GET["project"];
if(!is_numeric($project) || strlen($project) !== 10)
    returnHttpResponse(400, "Incorrect project number");

$querypos = "
    SELECT 
        SUM(Quantity) AS Quantity, 
        TypicalCode 
    FROM 
        dbo.OneX_SapPosData 
    WHERE 
        ProjectNo = :p1 AND 
        LocationCode IS NOT NULL
    GROUP BY
	    TypicalCode
";
$resultpos = DbManager::fetchPDOQueryData('MTool_INKWA', $querypos, [":p1" => $project])["data"];
$resultData = [];
foreach ($resultpos as $row) {
    $typicalQty = (int) $row["Quantity"];
    $typicalName = str_replace('=', '', $row["TypicalCode"]);

    $resultData[] = [
        "typicalQty" => $typicalQty,
        "typicalName" => $typicalName
    ];
}

header('Content-type: application/json');
echo json_encode($resultData); exit;
