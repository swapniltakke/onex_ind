<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/shared/shared.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/dpm/dtoconfigurator/api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

$controller = new NCController($_POST);

$response = match ($_GET['action']) {
    'getNCsByTkFormId' => $controller->getNCsByTkFormId(),
    'getNCsByProjectNo' => $controller->getNCsByProjectNo(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

class NCController {

    public function getNCsByTkFormId(): void
    {
        $tkformId = $_GET['id'];

        $query = "SELECT dto_number FROM tkforms WHERE id = :id";
        $dtoNumber = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $tkformId])['data'][0]['dto_number'];

        $query = "SELECT Descriptor, PanelNo, NcNo, Status, NcDetails, NcDate FROM Stopped
                  LEFT JOIN Orders ON Orders.OrderID = Stopped.OrderID
                  LEFT JOIN Origin ON Origin.OriginID = Stopped.OriginID
                  WHERE DtoNo LIKE :DtoNo AND Origin.OriginID IN(32,94,96)";
        $data = DbManager::fetchPDOQueryData('SI_EA_QR_Tracking', $query, [':DtoNo' => '%'.$dtoNumber.'%'])['data'];
        echo json_encode($data);
    }

    public function getNCsByProjectNo(): void
    {
        $projectNo = $_GET['projectNo'];

        $query = "SELECT DtoNo, Descriptor, PanelNo, NcNo, Status, NcDetails, NcDate, OriginCode FROM Stopped
                  LEFT JOIN Orders ON Orders.OrderID = Stopped.OrderID
                  LEFT JOIN Origin ON Origin.OriginID = Stopped.OriginID
                  WHERE Orders.Descriptor LIKE :pNo AND Origin.OriginID IN(32,94,96)";
        $data = DbManager::fetchPDOQueryData('SI_EA_QR_Tracking', $query, [':pNo' => '%'.$projectNo.'%'])['data'];
        echo json_encode($data);
    }
}