<?php

function getProjects(){
    $salesOrdersFromPlanQuery = "
        SELECT 
            SUBSTR(projectNo, 1, 10) AS projectNo
        FROM assembly_plan_mv 
        WHERE 
            revisionNr = (SELECT MAX(revisionNr) FROM assembly_plan_mv_index) AND
            productionday > DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL -4 MONTH) AND
            productionday < DATE_ADD(DATE_FORMAT(NOW(),'%Y-%m-%d'), INTERVAL 4 MONTH) AND
            projectNo LIKE '300%'
        GROUP BY ProjectNo
    ";

    $salesOrdersFromPlanQueryResult = CronDbManager::fetchQueryData('planning', $salesOrdersFromPlanQuery)["data"];
    $salesOrders = [];
    foreach ($salesOrdersFromPlanQueryResult as $row){
        $projectNo = $row["projectNo"];
        $salesOrders[] = $projectNo;
    }

    return $salesOrders;
}

function getTempDataFileSavePath(){
    return "./rpa_temp";
}

function saveTempDataFile($transaction, $tempData, $fileIndex){
    $savePath =  getTempDataFileSavePath();
    $tempDataFileName = "$transaction"."_$fileIndex.json";
    $filePath = "$savePath/$tempDataFileName";
    $isSaved = file_put_contents($filePath, json_encode($tempData));
    if(!$isSaved)
        returnHttpResponse(500, "Temp data file could not be saved");
}

function deleteTempFiles($transaction){
    $path = getTempDataFileSavePath();
    $folderContent = scandir($path);
    if($folderContent === false)
        returnHttpResponse(500, "Path: '$path' does not exists ");

    $previousTempFiles = array_filter($folderContent, function($file) use($transaction){
        return str_starts_with($file, $transaction) && !in_array($file, ['.','..']);
    });

    foreach ($previousTempFiles as $previousTempFile){
        $previousTempFilePath = "$path/$previousTempFile";
        unlink($previousTempFilePath);
    }
}

