<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
ini_set('memory_limit', '1024M');

$project = trim($_GET["project"]);
$panel = trim($_GET["panel"]);
$source_type = trim($_GET["source_type"]);
$shortText = trim($_GET["shortText"]);
$materialNumbers = trim($_GET["materialNumbers"]);
$materialNumbers2 = trim($_GET["materialNumbers2"]);
$mrp = trim($_GET["mrp"]);
$type = trim($_GET["type"]);

if(!is_numeric($project))
    returnHttpResponse(400, "Incorrect project number");

if(!is_numeric($panel) && $panel !== "all")
    returnHttpResponse(400, "Incorrect PANEL number");

$table2 = "";
if ($source_type == "1") {
    $table1 = "sap_spiridon_002";
} else if ($source_type == "2") {
    $table1 = "sap_spiridon_001";
    $table2 = "sap_spiridon_002";
} else {
    $table1 = "sap_spiridon_001";
}

$logText = 'Search for Project: ' . $project;
SharedManager::saveLog('log_material_search', $logText);

$posData = MToolManager::getProjectPosData($project);

$bomListDataQueryParameters1 = [
    ':salesOrder' => $project
];
$bomListDataQueryParameters2 = [
    ':salesOrder' => $project
];

if($panel !== "-1"){
    $panelNumberWhereClause1 = " AND t1.ItemNumber=:panel1";
    $bomListDataQueryParameters1[":panel1"] = $panel;
    if ($table2 != "") {
        $panelNumberWhereClause2 = " AND t2.ItemNumber=:panel2";
        $bomListDataQueryParameters2[":panel2"] = $panel;
    }
}
else{
    $panelNumberWhereClause1 = "";
    if ($table2 != "") {
        $panelNumberWhereClause2 = "";
    }
}

$shortTextWhereClause1 = "";
$shortTextWhereClause2 = "";
if($shortText){
    $shortTextWhereClause1 = " AND t1.MaterialDescription REGEXP :shortText1";
    $bomListDataQueryParameters1[":shortText1"] = $shortText;
    if ($table2 != "") {
        $shortTextWhereClause2 = " AND t2.MaterialDescription REGEXP :shortText2";
        $bomListDataQueryParameters2[":shortText2"] = $shortText;
    }
}

$materialNumbersWhereClause1 = "";
$materialNumbersWhereClause2 = "";
if($materialNumbers){
    $materialNumbersWhereClause1 = " AND t1.MaterialNumber REGEXP :materialNumbers1";
    $bomListDataQueryParameters1[":materialNumbers1"] = $materialNumbers;
    if ($table2 != "") {
        $materialNumbersWhereClause2 = " AND t2.MaterialNumber REGEXP :materialNumbers2";
        $bomListDataQueryParameters2[":materialNumbers2"] = $materialNumbers;
    }
}

$materialNumbers2WhereClause1 = "";
$materialNumbers2WhereClause2 = "";
if($materialNumbers2){
    $materialNumbers2WhereClause1 = " AND t1.MaterialNumber2 REGEXP :materialNumbers21";
    $bomListDataQueryParameters1[":materialNumbers21"] = $materialNumbers2;
    if ($table2 != "") {
        $materialNumbers2WhereClause2 = " AND t2.MaterialNumber2 REGEXP :materialNumbers22";
        $bomListDataQueryParameters2[":materialNumbers22"] = $materialNumbers2;
    }
}

$mrpWhereClause1 = "";
$mrpWhereClause2 = "";
if($mrp){
    $mrpWhereClause1 = " AND t1.MRP REGEXP :mrpNumbers1";
    $bomListDataQueryParameters1[":mrpNumbers1"] = $mrp;
    if ($table2 != "") {
        $mrpWhereClause2 = " AND t2.MRP REGEXP :mrpNumbers2";
        $bomListDataQueryParameters2[":mrpNumbers2"] = $mrp;
    }
}

$bomListDataQuery1 = "
    SELECT * FROM $table1 t1
    WHERE 
        SalesOrder = :salesOrder 
        $panelNumberWhereClause1
        $shortTextWhereClause1
        $materialNumbersWhereClause1
        $materialNumbers2WhereClause1
        $mrpWhereClause1
";

$bomListDataQueryData1 = DbManager::fetchPDOQueryData('rpa', $bomListDataQuery1, $bomListDataQueryParameters1)["data"];
$bomListDataQueryData2 = [];
if ($table2 != "") {
    $bomListDataQuery2 = "
        SELECT * FROM $table2 t2
        WHERE 
            SalesOrder = :salesOrder 
            $panelNumberWhereClause2
            $shortTextWhereClause2
            $materialNumbersWhereClause2
            $materialNumbers2WhereClause2
            $mrpWhereClause2
    ";

    $bomListDataQueryData2 = DbManager::fetchPDOQueryData('rpa', $bomListDataQuery2, $bomListDataQueryParameters2)["data"];
}
// Merge both results
$bomListDataQueryData = array_merge($bomListDataQueryData1, $bomListDataQueryData2);

$salesOrderNumbers = [];
foreach ($bomListDataQueryData as $row){
    $salesOrder = $row["SalesOrder"];
    $salesOrderNumbers[] = $salesOrder;
}
$salesOrderNumbers = array_values(array_unique($salesOrderNumbers));
if(count($salesOrderNumbers) === 0){
    echo json_encode(["data" => [
        "OrderNumber" => [],
        "SalesOrder" => [],
        "MaterialNumber" => [],
        "ReqQuantity" => [],
        "BUn" => [],
        "QuantityWithdrawn" => [],
        "StorageLocation" => [],
        "MaterialNumber2" => [],
        "ItemCategory" => [],
        "IndicatorBackflush" => [],
        "MRP" => [],
        "Typ" => [],
        "MaterialDescription" => [],
        "Typical" => [],
        "Panel" => [],
        "MaterialGrouping" => []
    ]]); exit;
}

$mToolPanelInfoQuery = "
    SELECT 
        ProjectNo, 
        PosNo, 
        LocationCode, 
        TypicalCode 
    FROM dbo.OneX_SapPosData 
    WHERE ProjectNo IN(:projectNos)
";
$posDataQueryData = DbManager::fetchPDOQueryData('MTool_INKWA', $mToolPanelInfoQuery, [
    ":projectNos" => $salesOrderNumbers
])["data"];

$mtoolPanelData = [];
foreach ($posDataQueryData as $row){
    $projectNo= $row["ProjectNo"];
    $posNo= $row["PosNo"];
    $locationCode= $row["LocationCode"];
    $typicalCode= $row["TypicalCode"];

    $mtoolPanelData[$projectNo][$posNo] = [
        "Typical" => $typicalCode,
        "Panel" => $locationCode
    ];
}

$materialNumber2Sums = [];
$processed = [];
if ($type == "advancecomponent") {
    foreach ($bomListDataQueryData as $row) {
        $materialNumber2 = $row["MaterialNumber2"];
        if (!isset($materialNumber2Sums[$materialNumber2])) {
            $materialNumber2Sums[$materialNumber2] = 0;
        }
        $materialNumber2Sums[$materialNumber2] += floatval($row["ReqQuantity"]);
    }
}
$output = [];
foreach ($bomListDataQueryData as $row){
    $panelNo = ltrim($row["ItemNumber"], '0');

    $orderNumber = $row["OrderNumber"];
    $salesOrder = $row["SalesOrder"];
    $materialNumber = $row["MaterialNumber"];
    $reqQuantity = $row["ReqQuantity"];
    $bUn = $row["BUn"];
    $quantityWithdrawn = $row["QuantityWithdrawn"];
    $storageLocation = $row["StorageLocation"];
    $materialNumber2 = $row["MaterialNumber2"];
    $itemCategory = $row["ItemCategory"];
    $indicatorBackflush = $row["IndicatorBackflush"];
    $MRP = $row["MRP"];
    $typ = $row["Typ"];
    $materialDescription = $row["MaterialDescription"];
    $KMATNo = $row["KMATNo"];
    $KMATName = $row["KMATName"];
    $materialGrouping = $row["MaterialGrouping"];

    if ($type == "advancecomponent") {
        if (!isset($processed[$row["MaterialNumber2"]])) {
            $output['data'][] = array(
                "OrderNumber" => "",
                "SalesOrder" => $salesOrder,
                "MaterialNumber" => "",
                "ReqQuantity" => $materialNumber2Sums[$row["MaterialNumber2"]], // Use the summed value
                "BUn" => "",
                "QuantityWithdrawn" => "",
                "StorageLocation" => "",
                "MaterialNumber2" => $materialNumber2,
                "ItemCategory" => "",
                "IndicatorBackflush" => "",
                "MRP" => "",
                "Typ" => "",
                "MaterialDescription" => $materialDescription,
                "Typical" => "",
                "Panel" => "",
                "KMATNo" => "",
                "KMATName" => "",
                "MaterialGrouping" => $materialGrouping
            );
            // Mark this MaterialNumber2 as processed
            $processed[$row["MaterialNumber2"]] = true;
        }
    } else {
        $output['data'][] = array(
            "OrderNumber" => $orderNumber,
            "SalesOrder" => $salesOrder,
            "MaterialNumber" => $materialNumber,
            "ReqQuantity" => $reqQuantity,
            "BUn" => $bUn,
            "QuantityWithdrawn" => $quantityWithdrawn,
            "StorageLocation" => $storageLocation,
            "MaterialNumber2" => $materialNumber2,
            "ItemCategory" => $itemCategory,
            "IndicatorBackflush" => $indicatorBackflush,
            "MRP" => $MRP,
            "Typ" => $typ,
            "MaterialDescription" => $materialDescription,
            "Typical" => $mtoolPanelData[$salesOrder][$panelNo]["Typical"],
            "Panel" => $mtoolPanelData[$salesOrder][$panelNo]["Panel"],
            "KMATNo" => $KMATNo,
            "KMATName" => $KMATName,
            "MaterialGrouping" => $materialGrouping
        );
    }
}

header('Content-type: application/json');
echo json_encode($output);