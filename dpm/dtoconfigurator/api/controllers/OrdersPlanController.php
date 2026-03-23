<?php
include_once '../../api/controllers/BaseController.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/shared/api/MtoolManager.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class OrdersPlanController extends BaseController {

    public function getOrdersPlanData()
    {
        $query = "SELECT TOP 1000 FactoryNumber, ProjectName, SubProduct, Qty
                  FROM dbo.OneX_ProjectDetails 
                  WHERE FactoryNumber LIKE '70240%' AND (Product LIKE 'NXAIR%') AND SubProduct NOT LIKE '%Comp%'
                  ORDER BY FactoryNumber DESC";

        $projectDetails = DbManager::fetchPDOQuery('MTool_INKWA', $query)['result'];
        $projects = array_column($projectDetails, 'FactoryNumber');

        $projectContacts = MtoolManager::getProjectContacts($projects);
        $projectContacts = array_combine(array_column($projectContacts, 'FactoryNumber'), $projectContacts);

        $data = [];
        foreach($projectDetails as $project) {
            $data[] = [
                "FactoryNumber" => $project['FactoryNumber'],
                "ProjectName" =>  $project['ProjectName'],
                "SubProduct" => $project['SubProduct'],
                "Qty" => $project['Qty'],
                "ME" => $projectContacts[$project['FactoryNumber']]['MechanicalEngineer'],
                "MEGID" => $projectContacts[$project['FactoryNumber']]['MEGID'],
                "OM" => $projectContacts[$project['FactoryNumber']]['OrderManager'],
                "OMGID" => $projectContacts[$project['FactoryNumber']]['OMGID']
            ];
        }

        ob_start("ob_gzhandler");

        header("Content-Encoding: gzip");
        header("Content-Type: application/json");

        echo(json_encode($data));
        exit();
    }


}


$controller = new OrdersPlanController($_POST);

$response = match ($_GET['action']) {
    'getOrdersPlanData' => $controller->getOrdersPlanData(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

//$response = match ($_POST['action']) {
//    default => ['status' => 400, 'message' => 'Invalid action'],
//};