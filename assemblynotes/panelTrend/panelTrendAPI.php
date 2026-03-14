<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/assemblynotes/core/index.php";
$type = $_GET["type"] ?? $_POST["type"];
selectMethod($type);
#[NoReturn] function selectMethod($type)
{
    switch ($type) {
        case "drawLv":
            getLineGraphData('LV');
            break;
        case "drawMv":
            getLineGraphData('MV');
            break;
        default :
            break;
    }
}

function getPanelDataFromBarcodeDb($type = 'MV')
{
    $timeParams_pdo = array();
    $param = json_decode($_GET["param"] ?? null);
    if (!empty($param->startDate)) {
        $fStartDate = date("Y-m-d", strtotime($param->startDate));
        $whereClause = " AND DATE_FORMAT(EventDate, '%Y-%m-%d') >= :param1 ";
        $timeParams_pdo = array_merge($timeParams_pdo, [":param1" => $fStartDate]);
    }
    if (!empty($param->endDate)) {
        $fEndDate = date("Y-m-d", strtotime($param->endDate));
        $whereClause .= " AND DATE_FORMAT(EventDate, '%Y-%m-%d') <= :param2 ";
        $timeParams_pdo = array_merge($timeParams_pdo, [":param2" => $fEndDate]);
    }

    if ($type == 'MV') {
        $whereClause .= "AND StationID IN ('55','34','71')";
    } else {
        $whereClause .= "AND StationID='140'";
    }

    $queryByDate = "SELECT DATE_FORMAT(EventDate, '%Y-%m-%d') as byDate, count(*) as totalPanelByDate
FROM vw_PanelProduction as v
WHERE IsCheckIn='true' and year(v.EventDate)>=Year(GetDate()) " . $whereClause . "
group BY DATE_FORMAT(EventDate, '%Y-%m-%d') order by byDate";

    $responseByDate = DbManager::fetchPDOQueryData('SI_EA_QR_Tracking', $queryByDate, $timeParams_pdo)["data"];

    if ($responseByDate === false) {
        http_response_code(500);
        echo json_encode(["message" => "Veri yüklenemedi.", "code" => "500"], JSON_THROW_ON_ERROR);
        exit();
    }

    return $responseByDate;
}

function getLineGraphData($type = 'MV')
{
    $timeParams_pdo = array(":param1" => $type);
    // AÇIK
    $response = ["reworkTotal" => [], "panelCount" => [], "avg" => [], "bydate" => []];
    $panelCountByDateArr = getPanelDataFromBarcodeDb($type);

    $param = json_decode($_GET["param"] ?? null);
    if (!empty($param->startDate)) {
        $fStartDate = date("Y-m-d", strtotime($param->startDate));
        $timeParams_pdo = array_merge($timeParams_pdo, [":p1" => $fStartDate]);
        $whereClause = " AND DATE_FORMAT(tn.updated, '%Y-%m-%d')>= :p1";
    }
    if (!empty($param->endDate)) {
        $fEndDate = date("Y-m-d", strtotime($param->endDate));
        $timeParams_pdo = array_merge($timeParams_pdo, [":p2" => $fEndDate]);
        $whereClause .= " AND DATE_FORMAT(tn.updated, '%Y-%m-%d')<=:p2";
    }
    if (!empty($param->subCategories)) {
        $implodeParam = explode(',', $param->subCategories);
        $timeParams_pdo = array_merge($timeParams_pdo, [":p3" => $implodeParam]);
        $whereClause .= " AND tn.subcategoryid IN" . "(:p3)";
    }

    for ($i = strtotime($fStartDate); $i <= strtotime($fEndDate); $i = $i + 86400) {
        $response["data"][date('Y-m-d', $i)]["reworkTotal"] = 0;
        $response["data"][date('Y-m-d', $i)]["panelCount"] = 0;
        $response["data"][date('Y-m-d', $i)]["avg"] = 0;
    }

    $queryByDate = "select date(updated) as updatedByDate, sum(ifnull(tn.ecrTime,0)) as totalByDate
from tnotes as tn
inner join tsub_categories tsc on tsc.id= tn.subcategoryid
inner join tmain_categories tmc on tmc.id=tsc.mainCategoryId
where notestatus = 1 and tmc.mainCategoryType=:param1 $whereClause
group by date(updated)
order by updatedByDate";

    $responseReworkByDate = DbManager::fetchPDOQueryData('assembly_items', $queryByDate, $timeParams_pdo)["data"];

    if ($responseReworkByDate === false) {
        http_response_code(500);
        echo json_encode(["message" => "Veri yüklenemedi.", "code" => "500"], JSON_THROW_ON_ERROR);
        exit();
    }
    $usedIndexArr = [];
    $ReworkDatabyDates = array_column($responseReworkByDate, 'updatedByDate');
    $PanelDatabyDates = array_column($panelCountByDateArr, 'byDate');
    $diff = array_diff($ReworkDatabyDates, $PanelDatabyDates); // rework girişleri olan ama pano üretim durumu olmayan günler
    // rework süreleri bir sonraki güne aktarılmalı
    foreach ($diff as $index => $difItem) {
        $found_key = array_search($difItem, $ReworkDatabyDates, true);
        if ($found_key === false) {
            continue; // null reference önceleme
        }
        foreach ($responseReworkByDate as $ind => $reworkItem) {
            if (in_array($ind, $usedIndexArr, true)) { // dengeli dağıtım için var
                continue;
            }
            if ($reworkItem["updatedByDate"] != $difItem) {
                $responseReworkByDate[$ind]["totalByDate"] += (int)$responseReworkByDate[$found_key]["totalByDate"];
                $usedIndexArr[] = $ind;
                break;
            }
        }
    }
    foreach ($panelCountByDateArr as $index => $pd) { // pano verisi
        $found_key = array_search($pd["byDate"], $ReworkDatabyDates, true);
        if ($found_key === false) {
            // o gün pano üretilmiş ama rework time girişi yapılmamış
            $response["data"][$pd["byDate"]]["reworkTotal"] = 0;
            $response["data"][$pd["byDate"]]["panelCount"] = (int)$pd["totalPanelByDate"];
            $response["data"][$pd["byDate"]]["avg"] = 0;
        } else {
            // o gün pano üretilmiş ve rework süre girişi yapılmış
            $avg = (int)$responseReworkByDate[$found_key]["totalByDate"] / (int)$pd["totalPanelByDate"];
            $response["data"][$pd["byDate"]]["reworkTotal"] = (int)$responseReworkByDate[$found_key]["totalByDate"];
            $response["data"][$pd["byDate"]]["panelCount"] = (int)$pd["totalPanelByDate"];
            $response["data"][$pd["byDate"]]["avg"] = round($avg, 2);

        }
    }

    foreach ($response["data"] as $keys => $values) {
        $response["bydate"][] = $keys;
        foreach ($values as $key => $value) {
            if ($key == "reworkTotal") {
                $response["reworkTotal"][] = $value;
            }
            if ($key == "avg") {
                $response["avg"][] = $value;
            }
            if ($key == "panelCount") {
                $response["panelCount"][] = $value;
            }
        }
    }


    echo json_encode($response, JSON_PRETTY_PRINT);
    exit();
}

