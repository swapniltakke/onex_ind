<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";

$projectNo = trim($_GET["projectNo"]);

if(!is_numeric($projectNo))
    returnHttpResponse(400, "ProjectNo is not numeric");

if(strlen($projectNo) < 4){
    echo json_encode([]); exit;
}

$result = MToolManager::searchProject($projectNo);
$searchResultArray['items'] = [];
foreach ($result as $row) {
    $searchResultArray['items'][] = array(
        "project_no" => $row["FactoryNumber"],
        "name" => $row["FactoryNumber"] . "-" . $row["ProjectName"] . " - " . $row["Product"] . " [" . (int)$row["Qty"] . "]",
    );
}

$object = (object)$searchResultArray;
echo json_encode($object);