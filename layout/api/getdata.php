<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/layout/api/common.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";

header('Content-type: application/json');
$week = $_GET['week'];
$line = ucfirst($_GET['line']);

if (!(is_numeric($week) && (int)$week == $week && strlen($week) === 6))
    returnHttpResponse(400, "Invalid week($week) parameter");

$productionLines = getLines();
if(!in_array($line, $productionLines))
    returnHttpResponse(400, "Invalid line($line) parameter");

$workDays = getCWDays($week);
$year = substr($week, 0, 4);
$weekNumber = substr($week, 4, 6);

$daysOfWeek = array(
    1 => "Monday",
    2 => "Tuesday",
    3 => "Wednesday",
    4 => "Thursday",
    5 => "Friday",
    6 => "Saturday",
    7 => "Sunday"
);

/*$assemblyPlanExceptSelectedWeekQuery = "
    SELECT 
        projectNo,
        projectName,
        poz as lot,
        productionLine, 
        productionWeek, 
        productionday, 
        productId, 
        SUM(quantityonday) AS quantityonday 
    FROM
        assembly_plan_mv
    WHERE 
        revisionNr = (SELECT MAX(revisionNr) FROM assembly_plan_mv_index ) AND 
        productionWeek != :productionWeek AND
        productionDay NOT IN(:productionDays) AND
        productionLine = :productionLine AND
        ProjectNo!='' 
    GROUP BY
        projectNo,
        poz,
        productionday
    ORDER BY
        productionWeek,
        productionday
";

$assemblyPlanExceptSelectedWeekQueryData = DbManager::fetchPDOQueryData('planning', $assemblyPlanExceptSelectedWeekQuery, [
    ":productionWeek" => "$year-$weekNumber",
    ":productionDays" => $workDays,
    ":productionLine" => $line
])["data"];

$assemblyPlanExceptSelectedWeek = [];
foreach ($assemblyPlanExceptSelectedWeekQueryData as $row){
    $projectNo = $row["projectNo"];
    $productionWeek = $row["productionWeek"];
    $productionDay = $row["productionday"];
    if(!$assemblyPlanExceptSelectedWeek[$projectNo])
        $assemblyPlanExceptSelectedWeek[$projectNo][$productionWeek] = $productionDay;
    else
        $assemblyPlanExceptSelectedWeek[$projectNo][$productionWeek] += $productionDay;
}*/

$assemblyPlanQuery = "
    SELECT 
        projectNo,
        projectName,
        poz as lot,
        productionLine, 
        productionWeek, 
        productionday, 
        productId, 
        SUM(quantityonday) AS quantityonday 
    FROM
        assembly_plan_mv
    WHERE 
        revisionNr = (SELECT MAX(revisionNr) FROM assembly_plan_mv_index ) AND 
        productionDay IN(:productionDays) AND
        productionLine = :productionLine AND
        ProjectNo != '' 
    GROUP BY
        projectNo,
        poz,
        productionday
";

$assemblyPlanData = DbManager::fetchPDOQueryData('planning', $assemblyPlanQuery, [
    ":productionDays" => $workDays,
    ":productionLine" => $line
])["data"];

$output = [
    "rework" => [],
    "plan" => []
];
$serverDateDMY = date("d-m-Y");
foreach ($workDays as $workDay){
    $workDayDMY = implode('-', array_reverse(explode('-', $workDay)));
    $weekDayOfWorkDay = $daysOfWeek[date("N", strtotime($workDay))];
    $output["plan"][$workDayDMY] = [
        "serverDateDMY" => $serverDateDMY,
        "weekDay" => $weekDayOfWorkDay,
        "dailyQuantity" => 0,
        "productionPlan" => []
    ];
}

$projectNoAndProductionDayConcatArray = [];
foreach ($assemblyPlanData as $row){
    $projectNo = $row["projectNo"];
    $productionLine = $row["productionLine"];
    $quantityOnDay = $row["quantityonday"];
    $projectName = $row["projectName"];
    $productionWeek = $row["productionWeek"];
    $productionDay = $row["productionday"];
    $productionDayDMY = implode('-', array_reverse(explode('-', $productionDay)));
    $productionWeekDay = $daysOfWeek[date("N", strtotime($productionDay))];
    $type = $row["productId"];

    /*if($assemblyPlanExceptSelectedWeek[$projectNo]){
        echo "<pre>"; var_dump($assemblyPlanExceptSelectedWeek[$projectNo]); echo "</pre>"; exit;
    }*/
    $projectNoAndProductionDayConcatArray[] = $projectNo."_".$productionDay;

    $dataObj = [
        "projectNo" => $projectNo,
        "lot" => "",
        "productionLine" => $productionLine,
        "projectName" => $projectName,
        "quantity" => (int) $quantityOnDay,
        "productionWeek" => $productionWeek,
        "productionDay" => $productionDayDMY,
        "panelType" => $type,
        "panelNumbers" => [],
        "responsibles" => [
            "om" => [],
            "ee" => [],
            "me" => []
        ]
    ];

    if(in_array($productionDay,$workDays)){
        $output["plan"][$productionDayDMY]["dailyQuantity"] += (int)$quantityOnDay;
        $output["plan"][$productionDayDMY]["productionPlan"][] = $dataObj;
    }

}

$panelNumberPerDayQuery = "
    SELECT 
        projectNo,
        quantityonday,
        productionday,
        poz
    FROM 
        assembly_plan_mv 
    WHERE 
        CONCAT(projectNo, '_', productionday) IN(:projectNoAndProductionDayConcat) AND
        revisionNr = (SELECT MAX(revisionNr) FROM assembly_plan_mv_index) AND
	    productionLine = :productionLine
";
$panelNumberPerDayQueryData = DbManager::fetchPDOQueryData('planning', $panelNumberPerDayQuery, [
    ":projectNoAndProductionDayConcat" => $projectNoAndProductionDayConcatArray,
    ":productionLine" => $line
])["data"];
$projectNoIndexes = [];
$projectNoPanelOnDay = [];
foreach ($panelNumberPerDayQueryData as $row){
    $projectNo = $row["projectNo"];
    $quantityOnDay = (int) $row["quantityonday"];
    $productionDay = $row["productionday"];
    $productionDayDMY = implode('-', array_reverse(explode('-', $productionDay)));
    $poz = $row["poz"];
    $panelNumbers = explode(',', $poz);

    $startIndex = 0;
    if($projectNoIndexes[$projectNo])
        $startIndex = $projectNoIndexes[$projectNo];

    for($i=$startIndex; $i<$startIndex+$quantityOnDay; $i++){
        $projectNoPanelOnDay[$productionDayDMY][$projectNo][] = $panelNumbers[$i];
        $projectNoIndexes[$projectNo]++;
    }
}
foreach (array_keys($projectNoPanelOnDay) as $productionDayDMY){
    foreach ($output["plan"][$productionDayDMY]["productionPlan"] as &$productionPlanRow){
        $projectNo = $productionPlanRow["projectNo"];
        $productionPlanRow["panelNumbers"] = $projectNoPanelOnDay[$productionDayDMY][$projectNo];
    }
}

echo json_encode($output); exit;