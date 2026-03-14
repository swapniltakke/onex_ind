<?php

class MToolManager{
    public static function searchProject($keyword){
        $query = "
            SELECT 
                FactoryNumber,
                ProjectName,
                Product,
                Qty
            FROM 
                dbo.OneX_ProjectDetails 
            WHERE 
                (FactoryNumber LIKE :p1 OR ProjectName LIKE :p1)
            ORDER BY
                FactoryNumber DESC
        ";

        return DbManager::fetchPDOQueryData('MTool_INKWA', $query, [":p1" => "%$keyword%"])["data"];
    }

    public static function searchMultipleProjects($projectNumbers){
        $query = "
            SELECT 
                FactoryNumber,
                ProjectName,
                Product,
                Qty
            FROM 
                dbo.OneX_ProjectDetails 
            WHERE 
                FactoryNumber IN (:projectNumbers)
        ";

        return DbManager::fetchPDOQueryData('MTool_INKWA', $query, [":projectNumbers" => $projectNumbers])["data"];
    }

    public static function getProjectPosData($projectNo, $includeAccessory = false){
        $includeAccessoryClause = ($includeAccessory) ? "AND TypicalCode IS NOT NULL" : "";
        $posDataQuery = "
            SELECT * FROM 
                dbo.OneX_SapPosData 
            WHERE 
                ProjectNo = :p1 
                $includeAccessoryClause
        ";
        $posDataQueryData = DbManager::fetchPDOQueryData('MTool_INKWA', $posDataQuery, [":p1" => $projectNo])["data"];
        $returnData = [];
        foreach ($posDataQueryData as $row){
            $panelNo = $row["PosNo"];
            $returnData[$panelNo] = $row;
        }
        return $returnData;
    }

    public static function getProjectDetailsByProjectNos(array $projectNumbers = [])
    {
        try {
            if (!empty($projectNumbers)) {
                $query = 'SELECT * FROM dbo.OneX_ProjectDetails WHERE FactoryNumber IN(:projectNumbers)';
                return DbManager::fetchPDOQuery('MTool_INKWA', $query, [':projectNumbers'=>$projectNumbers])['result'];
            }
            return [];
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("MtoolManager->getProjectDetailsByProjectNos: {$e->getMessage()}");
        }
    }

    public static function getProjectContacts(array $projectNumbers = []): array
    {
        $data = [];
        try {
            if (!empty($projectNumbers)) {
                $statement = "SELECT * FROM dbo.OneX_ProjectContacts WHERE FactoryNumber IN(:projectNumbers)";
                return DbManager::fetchPDOQuery('MTool_INKWA', $statement, [':projectNumbers'=>$projectNumbers])['result'];
            }
            return [];
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("MtoolManager->getProjectContacts: {$e->getMessage()}");
        }
    }
}