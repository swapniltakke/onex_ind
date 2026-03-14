<?php
header('Content-Type: application/json; charset=utf-8');
include_once $_SERVER["DOCUMENT_ROOT"] . "/checklogin.php";

$type = $_POST["action"] ?? $_GET["action"];
switch ($type) {
    case "getMaterialList":
        $panelItemsParam = $_GET["panelItemsParam"];
        $panelItemList = json_decode($panelItemsParam, true);
        $projectNoParam = $_GET["projectNo"];
        if (empty($projectNoParam)) {
            http_response_code(400);
            echo "Proje numarası okunamadı. İşlem iptal edildi.";
            exit();
        }
        if (empty($panelItemList)) {
            http_response_code(400);
            echo "Pano numarası parametresi belirlenemedi. İşlem iptal edildi.";
            exit();
        }
        $dataSrc = loadMaterialListByPanelItems($panelItemList, $projectNoParam);
        echo json_encode($dataSrc, JSON_PRETTY_PRINT);
        break;
    default:
        break;
}

exit();

function loadMaterialListByPanelItems($panelItems = [], $projectNo = "")
{
    $queryStr = "select distinct MaterialNumber,MaterialDescription from rpa.sap_spiridon_001 where salesOrder=:p1
and ItemNumber in (:p2) and
ItemIsDeleted<>'x' and
ItemIsDeleted<>'X' and
ReqQuantity>0";
    $response = DbManager::fetchPDOQuery('rpa', $queryStr, [":p1" => $projectNo, ":p2" => $panelItems]);
    if (empty($response["data"])) {
        http_response_code(422);
        echo "Could not read material info of project";
        exit();
    }
    return $response["data"];
}