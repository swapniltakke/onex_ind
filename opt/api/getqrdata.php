<?php

$project = $_GET["project"];
if(!is_numeric($project))
    returnHttpResponse(400, "Incorrect project number");

SharedManager::saveLog("log_opt", "QR Code Search for $project");

$mtoolProjectInfoQuery = "
    SELECT 
        SapPosData.*,
        dbo.OneX_ProjectLeads.ProjectName,
        dbo.OneX_ProjectLeads.OrderManager,
        dbo.OneX_ProjectTrackingEE.Product
    FROM(
        SELECT 
            ProjectNo,
            PosNo,
            LocationCode AS PanelName,
            TypicalCode AS TypicalName
        FROM 
            dbo.OneX_SapPosData 
        WHERE 
            ProjectNo = :p1 AND
            TypicalCode IS NOT NULL
    ) AS SapPosData
    JOIN
        dbo.OneX_ProjectLeads
    ON
        dbo.OneX_ProjectLeads.FactoryNumber = SapPosData.ProjectNo
    JOIN
        dbo.OneX_ProjectTrackingEE
    ON
        dbo.OneX_ProjectTrackingEE.FactoryNumber = SapPosData.ProjectNo
";

$mtoolProjectInfoQueryData = DbManager::fetchPDOQueryData('MTool_INKWA', $mtoolProjectInfoQuery, [":p1" => $project])["data"];
$output = [];
foreach ($mtoolProjectInfoQueryData as $row){
    $posNo = $row["PosNo"];
    $panelName = $row["PanelName"];
    $typicalName = $row["TypicalName"];
    $projectName = $row["ProjectName"];
    $orderManager = $row["OrderManager"];
    $product = $row["Product"];


    $output[] = [
        "posNo" => $posNo,
        "panelName" => $panelName,
        "typicalName" => $typicalName,
        "projectName" => $projectName,
        "orderManager" => $orderManager,
        "product" => $product
    ];
}

header('Content-type: application/json');
echo json_encode($output); exit;