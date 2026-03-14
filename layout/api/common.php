<?php

function containsAlphabeticAndHyphen($str) {
    return preg_match('/^[a-zA-Z0-9-]+$/', $str);
}

function getLines(): array
{
    $query = "
        SELECT 
            productionLine
        FROM
            assembly_plan_mv
        WHERE 
            revisionNr = (SELECT MAX(revisionNr) FROM assembly_plan_mv_index ) AND 
            ProjectNo != '' AND 
            productionLine != ''
        GROUP BY 
            productionLine
       ORDER BY
	        productionLine
    ";
    $result = DbManager::fetchPDOQueryData('planning', $query)["data"];
    $linesDefinedInDB = array_map(function($obj) {
        return $obj["productionLine"];
    }, $result);

    return array_values(array_filter($linesDefinedInDB, "containsAlphabeticAndHyphen"));
}