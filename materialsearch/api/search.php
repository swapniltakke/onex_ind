<?php
error_reporting(E_ERROR | E_PARSE);
$project = trim($_GET["project"]);

if(strlen($project) < 3)
    returnHttpResponse(400, "Incorrect project number");

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
$result = MToolManager::searchProject($project);

$found = false;
foreach ($result as $row) {
    $factoryNumber = $row['FactoryNumber'];
    $project_name = $row['ProjectName'];
    $product = $row['Product'];
    $panel_qty = (int) $row['Qty'];
    $found = $project;
    $arr['items'][] = array(
        "project_no" => $factoryNumber,
        "name" => "$factoryNumber - $project_name - $product [Qty: $panel_qty]",
        "html_url" => "/materialsearch/mat.php?project=$factoryNumber",
    );
}

$isFound = ($found) ? "Found" : "No Found";
SharedManager::saveLog("log_material_search", "Search Keyword: $project ($isFound)");

$object = (object)$arr;
echo json_encode($object);
exit;