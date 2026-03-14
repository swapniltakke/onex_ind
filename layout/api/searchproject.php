<?php
header('Content-Type:application/json;charset=UTF-8');
include_once $_SERVER["DOCUMENT_ROOT"] . "/checklogin.php";
SharedManager::checkAuthToModule(52);
include_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MtoolManager.php";
if (isset($_GET['project'])) {

    $projectNumberOrName = $_GET['project'];
    $projects = MtoolManager::searchProjectByKeyword($projectNumberOrName);

    $arr['items'] = [];

    foreach ($projects as $project) {
        $projectno = $project['FactoryNumber'];
        $statement = "
            SELECT * FROM (
                SELECT 
                    Id, 
                    projectNo, 
                    projectName, 
                    productionLine, 
                    productionWeek 
                FROM 
                    assembly_plan_mv 
                WHERE
                    assembly_plan_mv.projectNo = :projectno  AND
                    revisionNr = (
                        SELECT MAX(revisionNr) FROM assembly_plan_mv_index WHERE projectNo = :projectno
                    )
                ORDER BY 
                    revisionNr 
                DESC 
                    LIMIT 18446744073709551615
            ) AS t1
            GROUP BY 
                projectNo 
            UNION 
            SELECT * FROM (
                SELECT 
                    Id, 
                    projectNo, 
                    projectName, 
                    productionLine, 
                    productionWeek FROM lv_monthly_workload 
                WHERE
                    lv_monthly_workload.projectNo = :projectno AND
                    revisionNr = (
                        SELECT MAX(revisionNr) FROM lv_monthly_workload_index WHERE projectNo = :projectno
                    )
                ORDER BY 
                    revisionNr DESC 
                LIMIT 18446744073709551615
            ) AS t1
            GROUP BY 
                projectNo 
            ORDER BY 
                Id DESC 
            LIMIT 1
        ";
        $result = DbManager::fetchPDOQuery('planning', $statement, [":projectno" => $projectno]);
        if ($result["stmt"]->rowCount() > 0) {
            $data = $result["result"][0];
            $data['productionLine'] = $data['productionLine'] == 'Line-4' ? 'Line-3' : $data['productionLine'];
            $data['productionLine'] = !str_contains($data['productionLine'], 'Line-') ? '-fason' : $data['productionLine'];
            $queryString = 'index.php?line=' . explode('-', $data['productionLine'])[1] . '&week=' . str_replace('-', '', $data['productionWeek']);
            $data['projectNo'] ? $arr['items'][] = ['id' => $data['projectNo'], 'text' => $data['projectNo'] . " - " . $data['projectName'] . " - " . $data['productionLine'] . " - " . $data['productionWeek'], 'html_url' => $queryString] : '';
        }
    }
    SharedManager::saveLog('log_layout', "CWPlan - Layout|Project Search|" . $_GET['project']);

    echo json_encode($arr);

}

?>