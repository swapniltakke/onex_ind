<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/assemblynotes/core/index.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
$type = $_GET["action"] ?? $_POST["action"];

selectMethod($type);
#[NoReturn] function selectMethod($type)
{
    switch ($type) {
        case "getOrderPanels":
            getOrderPanels();
            break;
        case "getPanelId":
            getPanelId();
            break;
        default :
            break;
    }
}


function getOrderPanels()
{
    $projectNo = $_GET['projectNo'];
    if(!is_numeric($projectNo))
        returnHttpResponse(400, "Incorrect project number");

    $posData = MToolManager::getProjectPosData($projectNo, true);
    $result = [];
    foreach ($posData as $row) {
        $posNo = $row["PosNo"];
        if (strlen($posNo) < 6) {
            $posNo = str_pad($posNo, 6, "0", STR_PAD_LEFT);
        }
        $result[] = ["PosNo" => $posNo, "LocationCode" => $row["LocationCode"], "TypicalCode" => $row["TypicalCode"]];
    }
    echo json_encode($result, JSON_THROW_ON_ERROR);
}

function getPanelId()
{
    $ProductionItemId = 0;
    $ProjectNo = $_GET['ProjectNo'];
    $PanelNo = $_GET['PanelNo'];
    $LotNo = $_GET['LotNo'];
    $query = "SELECT DISTINCT ProductionItemId FROM vw_PanelProduction WHERE OrderDescriptor=:p1 AND PanelNo=:p2 AND LotNo=:p3 ";
    $result = DbManager::fetchPDOQuery('SI_EA_QR_Tracking', $query, [":p1" => $ProjectNo, ":p2" => $PanelNo, ":p3" => $LotNo]);
    echo json_encode($result["data"][0]["ProductionItemID"], true);
}
