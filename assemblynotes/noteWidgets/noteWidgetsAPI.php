<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/assemblynotes/core/index.php";
$type = $_GET["type"] ?? $_POST["type"];
selectMethod($type);
#[NoReturn] function selectMethod($type)
{
    switch ($type) {
        case "listWidgets":
            listWidgets();
            break;
        case "listReworkWidgets":
            listReworkWidgets();
            break;
        default :
            break;
    }
}

function getFromNumberFormat($data)
{
    $item = number_format((empty($data) ? 0 : $data), 0, ',', '.');
    return $item;
}

function listWidgets()
{
    $responseData = ["openNoteCount" => 0, "closeNoteCount" => 0, "totalNoteCount" => 0];
    $whereClause = "";
    $whereCloseClause = "";
    $param = json_decode($_GET["param"] ?? null);
    $query = "
        SELECT 
            count(an.panelno) as noteCounts 
        FROM 
            tnotes AS an
        LEFT JOIN tsub_categories AS ansc ON an.subcategoryid=ansc.id
        LEFT JOIN tmain_categories anc ON anc.id=ansc.mainCategoryId where an.id>0 ";
    $timeParams_pdo = array();
    if (!empty($param->startDate)) {
        $fStartDate = date("Y-m-d", strtotime($param->startDate));
        $whereClause = " and DATE_FORMAT(an.created, '%Y-%m-%d')>= :p1";
        $whereCloseClause = " and DATE_FORMAT(an.updated, '%Y-%m-%d')>= :p1";
        $timeParams_pdo = array_merge($timeParams_pdo, [":p1" => $fStartDate]);
    }
    if (!empty($param->endDate)) {
        $fEndDate = date("Y-m-d", strtotime($param->endDate));
        $whereClause .= " AND DATE_FORMAT(an.created, '%Y-%m-%d')<= :p2";
        $whereCloseClause .= " AND DATE_FORMAT(an.updated, '%Y-%m-%d')<= :p2";
        $timeParams_pdo = array_merge($timeParams_pdo, [":p2" => $fEndDate]);
    }

    if ($param->lv && $param->mv == false) {
        $whereClause .= " AND anc.mainCategoryType='LV'";
        $whereCloseClause .= " AND anc.mainCategoryType='LV'";
    } else if ($param->mv && $param->lv == false) {
        $whereClause .= " AND anc.mainCategoryType='MV'";
        $whereCloseClause .= " AND anc.mainCategoryType='MV'";
    }
    $queryOpenNotes = $query . $whereClause . " and an.notestatus=0";
    $responseO = DbManager::fetchPDOQueryData('assembly_items', $queryOpenNotes, $timeParams_pdo)["data"];
    if ($responseO === false) {
        http_response_code(500);
        echo json_encode(["message" => "Could not load open note count", "code" => "500"], JSON_THROW_ON_ERROR);
        exit();
    }
    $queryCloseNotes = $query . $whereCloseClause . " and an.notestatus=1";

    $responseC = DbManager::fetchPDOQueryData('assembly_items', $queryCloseNotes, $timeParams_pdo)["data"];
    if ($responseC === false) {
        http_response_code(500);
        echo json_encode(["message" => "Could not load open note count", "code" => "500"], JSON_THROW_ON_ERROR);
        exit();
    }

    if (!empty($responseO)) {
        $responseData["openNoteCount"] = getFromNumberFormat($responseO[0]["noteCounts"]);
    }
    if (!empty($responseC)) {
        $responseData["closeNoteCount"] = getFromNumberFormat($responseC[0]["noteCounts"]);
    }
    if (!empty($responseO) && !empty($responseC)) {
        $responseData["totalNoteCount"] = getFromNumberFormat(($responseO[0]["noteCounts"] + $responseC[0]["noteCounts"]));
    }
    echo json_encode(["message" => "", "data" => $responseData, "code" => "200"], JSON_THROW_ON_ERROR);
    exit();
}

function listReworkWidgets()
{
    //  açıklar için rework süresi hesaplanmıcak
    $responseData = ["closeReworkTime" => 0, "totalReworkTime" => 0];
    $whereClause = "";
    $timeParams_pdo = array();
    $param = json_decode($_GET["param"] ?? null);
    $query = "select sum(ifnull(an.ecrTime,0)) as reworkSum from tnotes AS an
LEFT JOIN tsub_categories AS ansc ON an.subcategoryid=ansc.id
LEFT JOIN tmain_categories anc ON anc.id=ansc.mainCategoryId where an.id>0";
    if (!empty($param->startDate)) {
        $fStartDate = date("Y-m-d", strtotime($param->startDate));
        $whereClause = " and DATE_FORMAT(an.updated, '%Y-%m-%d')>= :p1";
        $timeParams_pdo = array_merge($timeParams_pdo, [":p1" => $fStartDate]);
    }
    if (!empty($param->endDate)) {
        $fEndDate = date("Y-m-d", strtotime($param->endDate));
        $whereClause .= " AND DATE_FORMAT(an.updated, '%Y-%m-%d')<= :p2";
        $timeParams_pdo = array_merge($timeParams_pdo, [":p2" => $fEndDate]);
    }


    if ($param->lv && $param->mv == false) {
        $whereClause .= " AND anc.mainCategoryType='LV'";
    } else if ($param->mv && $param->lv == false) {
        $whereClause .= " AND anc.mainCategoryType='MV'";
    }

    $queryCloseNotes = $query . $whereClause . " and an.notestatus=1";

    $responseC = DbManager::fetchPDOQueryData('assembly_items', $queryCloseNotes, $timeParams_pdo)["data"];
    if ($responseC === false) {
        http_response_code(500);
        echo json_encode(["message" => "Could not load open notes total rework time", "code" => "500"], JSON_THROW_ON_ERROR);
        exit();
    }

    if (!empty($responseC)) {
        $responseData["closeReworkTime"] = getFromNumberFormat($responseC[0]["reworkSum"]);
    }
    if (!empty($responseC)) {
        $responseData["totalReworkTime"] = $responseData["closeReworkTime"];
    }
    echo json_encode(["message" => "", "data" => $responseData, "code" => "200"], JSON_THROW_ON_ERROR);
    exit();
}
