<?php
include_once __DIR__ . "./../core/index.php";
$type = $_GET["type"] ?? $_POST["type"];
selectMethod($type);
#[NoReturn] function selectMethod($type)
{
    switch ($type) {
        case "list":
            getParetoData();
            break;
        case "listMain":
            getParetoDataForMainCategories();
            break;
        case "listMainCategories":
            getMainCategories();
            break;
        case "listSubCategories":
            getSubCategories();
            break;
        default :
            break;
    }
}

function getChartQuery()
{
    $pdo_params = [];
    $param = json_decode($_GET["param"] ?? null);
    $whereClause = "";
    $queryCategories = "SELECT COUNT(an.panelno) AS qty, ansc.subCategory 
FROM tnotes AS an
LEFT JOIN tsub_categories AS ansc ON an.subcategoryid=ansc.id
LEFT JOIN tmain_categories anc ON anc.id=ansc.mainCategoryId";

    if (!empty($param->startDate)) {
        $fStartDate = date("Y-m-d", strtotime($param->startDate));
        $whereClause = " where DATE_FORMAT(created, '%Y-%m-%d')>=" . ":start_date";
        $pdo_params = array_merge($pdo_params, [":start_date" => $fStartDate]);
    }
    if (!empty($param->endDate)) {
        $fEndDate = date("Y-m-d", strtotime($param->endDate));
        $whereClause .= " AND DATE_FORMAT(created, '%Y-%m-%d')<=" . ":end_date";
        $pdo_params = array_merge($pdo_params, [":end_date" => $fEndDate]);
    }

    if ($param->lv && $param->mv == false) {
        $whereClause .= " AND anc.mainCategoryType='LV'";
    } else if ($param->mv && $param->lv == false) {
        $whereClause .= " AND anc.mainCategoryType='MV'";
    }
    if (!empty($param->subCategories)) {
        $whereClause .= " AND an.subcategoryid IN" . "(:implodeParam)";
        $pdo_params = array_merge($pdo_params, [":implodeParam" => explode(',', $param->subCategories)]);
    }
    if (!empty($param->category) && $param->category != 'All') {
        $whereClause .= " AND an.category = :param_category";
        $pdo_params = array_merge($pdo_params, [":param_category" => $param->category]);
    }

    $queryCategories .= $whereClause . " and notestatus=0 GROUP BY ansc.subCategory ORDER BY qty DESC";
    return ["queryItem" => $queryCategories, "pdoParamItem" => $pdo_params];
}

function getChartQueryOpenAndClose()
{
    $pdo_params = [];
    $param = json_decode($_GET["param"] ?? null);
    $whereClause = "";
    $queryCategories = "SELECT COUNT(an.panelno) AS qty, ansc.subCategory 
FROM tnotes AS an
LEFT JOIN tsub_categories AS ansc ON an.subcategoryid=ansc.id
LEFT JOIN tmain_categories anc ON anc.id=ansc.mainCategoryId";

    if (!empty($param->startDate)) {
        $fStartDate = date("Y-m-d", strtotime($param->startDate));
        $whereClause = " where DATE_FORMAT(created, '%Y-%m-%d')>=:start_date";
        $pdo_params = array_merge($pdo_params, [":start_date" => $fStartDate]);
    }
    if (!empty($param->endDate)) {
        $fEndDate = date("Y-m-d", strtotime($param->endDate));
        $whereClause .= " AND DATE_FORMAT(created, '%Y-%m-%d')<=:end_date";
        $pdo_params = array_merge($pdo_params, [":end_date" => $fEndDate]);
    }

    if ($param->lv && $param->mv == false) {
        $whereClause .= " AND anc.mainCategoryType='LV'";
    } else if ($param->mv && $param->lv == false) {
        $whereClause .= " AND anc.mainCategoryType='MV'";
    }
    if (!empty($param->subCategories)) {
        $whereClause .= " AND an.subcategoryid IN" . "(:implodeParam)";
        $pdo_params = array_merge($pdo_params, [":implodeParam" => explode(',', $param->subCategories)]);
    }
    if (!empty($param->category) && $param->category != 'All') {
        $whereClause .= " AND an.category = :category_param";
        $pdo_params = array_merge($pdo_params, [":category_param" => $param->category]);
    }
    $queryCategories .= $whereClause . " and notestatus=1 GROUP BY ansc.subCategory ORDER BY qty DESC";
    return ["queryItem" => $queryCategories, "pdoParamItem" => $pdo_params];
}

function getChartQueryForMainCategories()
{
    $pdo_params = [];
    $param = json_decode($_GET["param"] ?? null);
    $whereClause = "";
    $queryCategories = "select tc.mainCategory as mainCategory, count(tn.panelno) as qty from tnotes tn
left join tsub_categories ts on tn.subcategoryid = ts.id
left join tmain_categories tc on ts.mainCategoryId =tc.id";

    if (!empty($param->startDate)) {
        $fStartDate = date("Y-m-d", strtotime($param->startDate));
        $whereClause = " where DATE_FORMAT(created, '%Y-%m-%d')>=:start_date";
        $pdo_params = array_merge($pdo_params, [":start_date" => $fStartDate]);
    }
    if (!empty($param->endDate)) {
        $fEndDate = date("Y-m-d", strtotime($param->endDate));
        $whereClause .= " AND DATE_FORMAT(created, '%Y-%m-%d')<=:end_date";
        $pdo_params = array_merge($pdo_params, [":end_date" => $fEndDate]);
    }

    if ($param->lv && $param->mv == false) {
        $whereClause .= " AND tc.mainCategoryType='LV'";
    } else if ($param->mv && $param->lv == false) {
        $whereClause .= " AND tc.mainCategoryType='MV'";
    }
    if (!empty($param->category) && $param->category != 'All') {
        $whereClause .= " AND tn.category = :category_param";
        $pdo_params = array_merge($pdo_params, [":category_param" => $param->category]);
    }

    $queryCategories .= $whereClause . " and notestatus=0 GROUP BY ts.mainCategoryId ORDER BY qty DESC";

    return ["queryItem" => $queryCategories, "pdoParamItem" => $pdo_params];
}

function getChartQueryForMainCategoriesOpenAndCloses(): array
{
    $pdo_params = [];
    $param = json_decode($_GET["param"] ?? null);
    $whereClause = "";
    $queryCategories = "select tc.mainCategory as mainCategory, count(tn.panelno) as qty 
from tnotes tn left join tsub_categories as ts on ts.id=tn.subcategoryid
        left join tmain_categories as tc
      on ts.mainCategoryId =tc.id";

    if (!empty($param->startDate)) {
        $fStartDate = date("Y-m-d", strtotime($param->startDate));
        $whereClause = " where DATE_FORMAT(created, '%Y-%m-%d')>=:start_date";
        $pdo_params = array_merge($pdo_params, [":start_date" => $fStartDate]);
    }
    if (!empty($param->endDate)) {
        $fEndDate = date("Y-m-d", strtotime($param->endDate));
        $whereClause .= " AND DATE_FORMAT(created, '%Y-%m-%d')<= :end_date";
        $pdo_params = array_merge($pdo_params, [":end_date" => $fEndDate]);
    }

    if ($param->lv && $param->mv == false) {
        $whereClause .= " AND tc.mainCategoryType='LV'";
    } else if ($param->mv && $param->lv == false) {
        $whereClause .= " AND tc.mainCategoryType='MV'";
    }
    if (!empty($param->category) && $param->category != 'All') {
        $whereClause .= " AND tn.category =:category_param";
        $pdo_params = array_merge($pdo_params, [":category_param" => $param->category]);
    }

    $queryCategories .= $whereClause . " and notestatus=1 GROUP BY ts.mainCategoryId ORDER BY qty DESC";

    return ["queryItem" => $queryCategories, "pdoParamItem" => $pdo_params];
}

function getParetoData()
{
    $param = json_decode($_GET["param"] ?? null);
    $subCategoryData = array();
    $queryCategories = getChartQuery();
    $result = DbManager::fetchPDOQueryData('assembly_items', $queryCategories["queryItem"], $queryCategories["pdoParamItem"])["data"];
    if ($result === false) {
        http_response_code(500);
        echo json_encode(["message" => "Veri yüklenemedi.", "code" => "500"], JSON_THROW_ON_ERROR);
        exit();
    }
    $qtyDataForSubCategoriesRaw = [];
    $qtyDataStr = [];
    foreach ($result as $row) {
        $subCategoryData[] = $row['subCategory'];
        $qtyDataStr[$row['subCategory']] = (int)$row['qty'];
        $qtyDataForSubCategoriesRaw[] = ["category" => $row['subCategory'], "qty" => (int)$qtyDataStr[$row['subCategory']]];
    }
    if ($param && $param->openAndClose == "openAndClose") {
        $queryOpenAndClose = getChartQueryOpenAndClose();
        $resultOpenAndClose = DbManager::fetchPDOQueryData('assembly_items', $queryOpenAndClose["queryItem"], $queryOpenAndClose["pdoParamItem"])["data"];
        foreach ($resultOpenAndClose as $key => $row) {
            if (!in_array($row['subCategory'], $subCategoryData)) {
                $subCategoryData[] = $row['subCategory'];
                $qtyDataStr[$row['subCategory']] = (int)$row['qty'];
                $qtyDataForSubCategoriesRaw[] = ["category" => $row['subCategory'], "qty" => (int)$qtyDataStr[$row['subCategory']]];
            } else {
                $column_names = array_column($qtyDataForSubCategoriesRaw, "category");
                $indexKey = array_search($row['subCategory'], $column_names);
                $qtyDataStr[$row['subCategory']] += (int)$row['qty'];
                $qtyDataForSubCategoriesRaw[$indexKey]["qty"] = $qtyDataStr[$row['subCategory']];
            }
        }
    }
    arsort($qtyDataStr);
    ksort($qtyDataForSubCategoriesRaw);
    echo json_encode(["qtyDataForSubCategoriesRaw" => $qtyDataForSubCategoriesRaw, "subCategories" => array_keys($qtyDataStr), "qtyInfo" => array_values($qtyDataStr)], JSON_PRETTY_PRINT);
}

function getParetoDataForMainCategories(): void
{
    $param = json_decode($_GET["param"] ?? null, false, 512, JSON_THROW_ON_ERROR);
    $mainCategoryData = array();
    $queryCategories = getChartQueryForMainCategories();
    $result = DbManager::fetchPDOQueryData('assembly_items', $queryCategories["queryItem"], $queryCategories["pdoParamItem"])["data"];
    if ($result === false) {
        http_response_code(500);
        echo json_encode(["message" => "Veri yüklenemedi.", "code" => "500"], JSON_THROW_ON_ERROR);
        exit();
    }
    $qtyDataForMainCategoriesRaw = [];
    $qtyDataStr = [];
    foreach ($result as $row) {
        $mainCategoryData[] = $row['mainCategory'];
        $qtyDataStr[$row['mainCategory']] = (int)$row['qty'];
        $qtyDataForMainCategoriesRaw[] = ["category" => $row['mainCategory'], "qty" => (int)$qtyDataStr[$row['mainCategory']]];
    }
    if ($param && $param->openAndClose == "openAndClose") {
        $queryOpenAndClose = getChartQueryForMainCategoriesOpenAndCloses();
        $resultOpenAndClose = DbManager::fetchPDOQueryData('assembly_items', $queryOpenAndClose["queryItem"], $queryOpenAndClose["pdoParamItem"])["data"];
        foreach ($resultOpenAndClose as $key => $row) {
            if (!in_array($row['mainCategory'], $mainCategoryData)) {
                $mainCategoryData[] = $row['mainCategory'];
                $qtyDataStr[$row['mainCategory']] = (int)$row['qty'];
                $qtyDataForMainCategoriesRaw[] = ["category" => $row['mainCategory'], "qty" => (int)$qtyDataStr[$row['mainCategory']]];
            } else {
                $column_names = array_column($qtyDataForMainCategoriesRaw, "category");
                $indexKey = array_search($row['mainCategory'], $column_names);
                $qtyDataStr[$row['mainCategory']] += (int)$row['qty'];
                $qtyDataForMainCategoriesRaw[$indexKey]["qty"] = $qtyDataStr[$row['mainCategory']];
            }

        }
    }
    ksort($qtyDataForMainCategoriesRaw);
    echo json_encode([
        "qtyDataForMainCategoriesRaw" => $qtyDataForMainCategoriesRaw,
        "mainCategories" => array_keys($qtyDataStr),
        "qtyInfo" => array_values($qtyDataStr)
    ]);
}

function getMainCategories()
{
    $query = "select id, mainCategory as text from tmain_categories order by id";
    $response = DbManager::fetchPDOQuery('assembly_items', $query);
    echo json_encode(["data" => $response["data"]], JSON_PRETTY_PRINT);
    exit();
}

function getSubCategories()
{
    $param = json_decode($_GET["mainCategoryId"] ?? null);
    $query = "select ts.id, ts.subCategory as text from tmain_categories tc
left join tsub_categories ts on tc.id = ts.mainCategoryId
where ts.mainCategoryId= ?";
    $response = DbManager::fetchPDOQueryData('assembly_items', $query, [$param])["data"];
    if ($response === false) {
        http_response_code(500);
        echo json_encode(["message" => "Veri yüklenemedi.", "code" => "500"], JSON_THROW_ON_ERROR);
        exit();
    }
    echo json_encode(["data" => $response], JSON_PRETTY_PRINT);
}

exit();