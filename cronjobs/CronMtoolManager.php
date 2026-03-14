<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/cronjobs/CronDbManager.php';

class CronMtoolManager{
    /*
     *
     */
    static function getProjectDetailsByProjectNos($projectNumbers = []){
        try{
            if(count($projectNumbers)>0){
                $statement = "SELECT * FROM dbo.OneX_ProjectDetails WHERE FactoryNumber IN(:projectNumbers)";
                return CronDbManager::fetchQueryData('MTool_INKWA', $statement, [":projectNumbers" => $projectNumbers])['data'];
            }
            return null;
        }catch(Exception $e){
            echo $e;
            exit;
        }
    }

    static function getAllProjects(){
        try{
            $statement = "SELECT DISTINCT ProjectNo FROM dbo.Projects";
            return CronDbManager::fetchQueryData('MTool_INKWA', $statement)['data'];
        }catch(Exception $e){
            echo $e;
            exit;
        }
    }

    static function getAllSapPosDataOfProject($projectNumbers = []){
        try{
            if(count($projectNumbers)>0){
                $statement = "
                    SELECT 
                        dbo.Projects.ProjectNo,
                        dbo.SapPosData.* 
                    FROM 
                        dbo.SapPosData 
                    LEFT JOIN 
                        dbo.Projects 
                    ON 
                        dbo.SapPosData.ProjectID = dbo.Projects.ProjectID
                    WHERE 
                        dbo.SapPosData.ProjectID IN(
                            SELECT ProjectID FROM dbo.Projects WHERE ProjectNo IN(:projectNumbers)
                        )
                ";

                $result = CronDbManager::fetchQueryData('MTool_INKWA', $statement, [":projectNumbers" => $projectNumbers])["data"];
                $data = [];
                foreach($result as $row){
                    $data[] = [
                        'ProjectNo' => $row['ProjectNo'],
                        'ProjectID' => $row['ProjectID'],
                        'PosNo' => str_pad($row['PosNo'],6,"0",STR_PAD_LEFT),
                        'LocationCode' => $row['LocationCode'],
                        'TypicalCode' => $row['TypicalCode'],
                        'PanelDesignation' => $row['PanelDesignation'],
                        'PanelType' => $row['PanelType']
                    ];
                }
                return $data;
            }
            return null;
        }catch(Exception $e){
            echo $e;
            exit;
        }
    }


    /*
     * This function brings the panels except accessory panels in all orders given as parameters.
     * Mtool::searchProject(['7024033579'])
    */
    static function getSapPosDataOfProjectWithoutAccessory($projectNumbers = []){
        try{
            if(count($projectNumbers)>0){
                $statement = "
                    SELECT 
                        dbo.Projects.ProjectNo,
                        dbo.SapPosData.* 
                    FROM dbo.SapPosData 
                    LEFT JOIN dbo.Projects ON dbo.SapPosData.ProjectID = dbo.Projects.ProjectID
                    WHERE 
                        dbo.SapPosData.ProjectID IN(
                            SELECT 
                                ProjectID 
                            FROM 
                                dbo.Projects 
                            WHERE 
                                ProjectNo  IN(:projectNumbers)
                        ) AND 
                        TypicalCode IS NOT NULL AND 
                        TypicalCode NOT LIKE '%KZ00%' AND 
                        TypicalCode NOT LIKE '%LZ00%' AND 
                        TypicalCode NOT LIKE '%JZ00%' AND 
                        TypicalCode NOT LIKE '%HZ00%' AND 
                        TypicalCode NOT LIKE '%MZ00%'  AND 
                        TypicalCode NOT LIKE '%AKS%'AND 
                        TypicalCode NOT LIKE '%K00%'AND 
                        TypicalCode NOT LIKE '%ACCESSORY%'AND 
                        TypicalCode NOT LIKE '%ACCESSORIES%'
                ";

                $result = CronDbManager::fetchQueryData('MTool_INKWA', $statement, [":projectNumbers" => $projectNumbers])["data"];
                $data = [];
                foreach($result as $row){
                    $data[] = [
                        'ProjectNo' => $row['ProjectNo'],
                        'ProjectID' => $row['ProjectID'],
                        'PosNo' => str_pad($row['PosNo'],6,"0",STR_PAD_LEFT),
                        'LocationCode' => $row['LocationCode'],
                        'TypicalCode' => $row['TypicalCode'],
                        'PanelDesignation' => $row['PanelDesignation'],
                        'PanelType' => $row['PanelType']
                    ];
                }
                return $data;
            }
            return null;
        }catch(Exception $e){
            echo $e;
            exit;
        }
    }


    /*
     * This function, brings the accessory panels in all orders given as parameters.
     * Mtool::getAccessoryPanels(['7024033579'])
    */
    static function getAccessoryPanels($projectNumbers = []){
        try{
            if(count($projectNumbers)>0){
                $statement = "
                    SELECT 
                        dbo.Projects.ProjectNo,
                        dbo.SapPosData.* 
                    FROM dbo.SapPosData 
                    LEFT JOIN 
                        dbo.Projects 
                    ON 
                        dbo.SapPosData.ProjectID = dbo.Projects.ProjectID
                    WHERE 
                        dbo.SapPosData.ProjectID IN(
                            SELECT 
                                ProjectID 
                            FROM 
                                dbo.Projects 
                            WHERE ProjectNo  IN(:projectNumbers)
                        ) AND
                        (TypicalCode IS NULL OR
                         TypicalCode LIKE '%KZ00%' OR
                         TypicalCode LIKE '%LZ00%' OR
                         TypicalCode LIKE '%JZ00%' OR
                         TypicalCode LIKE '%HZ00%' OR
                         TypicalCode LIKE '%MZ00%'  OR
                         TypicalCode LIKE '%AKS%'OR
                         TypicalCode LIKE '%K00%'OR
                         TypicalCode LIKE '%ACCESSORY%'OR
                         TypicalCode LIKE '%ACCESSORIES%')
                ";

                $result = CronDbManager::fetchQueryData('MTool_INKWA', $statement, [":projectNumbers" => $projectNumbers])["data"];
                $data = [];
                foreach($result as $row){
                    $data[] = [
                        'ProjectNo' => $row['ProjectNo'],
                        'ProjectID' => $row['ProjectID'],
                        'PosNo' => str_pad($row['PosNo'],6,"0",STR_PAD_LEFT),
                        'LocationCode' => $row['LocationCode'],
                        'TypicalCode' => $row['TypicalCode'],
                        'PanelDesignation' => $row['PanelDesignation'],
                        'PanelType' => $row['PanelType']
                    ];
                }
                return $data;
            }
            return null;
        }catch(Exception $e){
            echo $e;
            exit;
        }
    }
}