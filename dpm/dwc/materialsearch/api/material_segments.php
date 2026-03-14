<?php
$projectFilter = $_GET["projectFilter"];
if(strlen($projectFilter) < 3)
    returnHttpResponse(400, "Incorrect project number");

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
$result = MToolManager::searchProject($projectFilter);
$projectData = $result[0];

$projectNo = $projectData["FactoryNumber"];
$projectName = $projectData["ProjectName"];
$keyword = $projectData["Keyword"];
$panelType = $projectData["Product"];
$quantity = $projectData["Qty"];

header('Content-type: application/json');
echo json_encode([
    "projectNo" => $projectNo,
    "projectName" => $projectName,
    "panelType" => $panelType,
    "quantity" => $quantity
]); exit;