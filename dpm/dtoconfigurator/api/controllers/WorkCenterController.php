<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class WorkCenterController extends BaseController {
    public function getWorkCenters(): void {
        $query = "SELECT * FROM work_centers WHERE work_center IS NOT NULL ORDER BY work_center";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'];
        echo(json_encode($data));
    }

    public function getWorkCentersBySearch(): void {
        $keyword = $_GET['keyword'];

        $query = "SELECT * FROM work_centers 
                  WHERE (work_center LIKE :keyword OR work_content LIKE :keyword) AND work_center IS NOT NULL 
                  ORDER BY work_center";

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':keyword'=>"%$keyword%"])['data'] ?? [];
        echo(json_encode($data));
    }

    public function getSubWorkCentersOfWorkCenter(): void {
        $workCenterId = $_GET['workCenterId'];

        $query = "SELECT work_center_id, sub_kmat_name, sub_kmat FROM material_kmat_subkmats WHERE work_center_id = :wcId AND sub_kmat IS NOT NULL GROUP BY sub_kmat_name";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':wcId' => $workCenterId])['data'] ?? [];
        echo (json_encode($data));
    }

    public function getSubKmatNamesOfMaterial(): void {
        $projectNo = $_GET['projectNo'];
        $materialNumber = $_GET['materialNo'];
        $productId = $this->getProductIdOfProject($projectNo);

        $query = "SELECT parent_kmat, sub_kmat_name 
                  FROM material_kmat_subkmats 
                  WHERE material_number = :mNo 
                  AND product_id = :productId 
                  GROUP BY parent_kmat, sub_kmat_name";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mNo' => $materialNumber, ':productId' => $productId])['data'] ?? [];

        $data = array_filter($result, function($row) {
            return !empty($row['parent_kmat']) || !empty($row['sub_kmat_name']);
        });

        $data = array_values($data);

        echo(json_encode($data));
        exit();
    }

    public function getParentAndSubKmatsOfMaterialBySubKmatName(): void {
        $projectNo = $_GET['projectNo'];
        $materialNumber = $_GET['materialNo'];
        $subKmatName = $_GET['subKmatName'];
        $productId = $this->getProductIdOfProject($projectNo);

        $query = "SELECT parent_kmat, sub_kmat FROM material_kmat_subkmats
                  WHERE material_number = :mNo AND product_id = :productId AND sub_kmat_name = :subKmatName";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mNo' => $materialNumber, ':productId' => $productId, ':subKmatName' => $subKmatName])['data'] ?? [];

        $parentKmats = implode('|', array_column($result, 'parent_kmat'));
        $subKmats = implode('|', array_column($result, 'sub_kmat'));

        $data = ['parentKmats' => $parentKmats, 'subKmats' => $subKmats];

        echo(json_encode($data));exit();
    }

    public function getParentKmatsOfMaterial(): void {
        $projectNo = $_GET['projectNo'];
        $materialNumber = $_GET['materialNo'];
        $productId = $this->getProductIdOfProject($projectNo);

        $query = "SELECT parent_kmat FROM material_kmat_subkmats
                  WHERE material_number = :mNo AND product_id = :productId";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mNo' => $materialNumber, ':productId' => $productId])['data'] ?? [];
        $data = array_values(array_unique(array_column($result, 'parent_kmat')));
        echo json_encode($data);exit();
    }

    public function getDeviceChoosableKmats() {
        $rowData = $_GET['rowData'];
        $workCenterKmats = [];

        $query = "SELECT * FROM project_work_view WHERE id = :id";
        $projectWork = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $rowData['id']])['data'][0];

        $workCenterIds = [$projectWork['tk_work_center_id'], $projectWork['secondary_work_center_id']];
        $query = "SELECT DISTINCT parent_kmat, sub_kmat, sub_kmat_name, work_center_id, work_center, work_content FROM material_kmat_subkmats 
                    WHERE product_id = :productId AND work_center_id IN (:workCenterIds)";
        $workCenterKmatResults = DbManager::fetchPDOQueryData('dto_configurator', $query, [':productId' => $projectWork['product_id'], ':workCenterIds' => $workCenterIds])['data'];

        foreach($workCenterKmatResults as $kmat) {
            if (!empty($kmat['sub_kmat'])) {
                $workCenterKmats[] = array(
                    'kmat' => $kmat['sub_kmat'],
                    'work_center' => $kmat['work_center'],
                    'work_content' => $kmat['work_content'] . ' - ' . $kmat['sub_kmat_name'],
                    'work_center_id' => $kmat['work_center_id']
                );
            } else {
                $workCenterKmats[] = array(
                    'kmat' => $kmat['parent_kmat'],
                    'work_center' => $kmat['work_center'],
                    'work_content' => $kmat['work_content'],
                    'work_center_id' => $kmat['work_center_id']
                );
            }
        }

        // Parçanın eklendiği olası TK materyal kmatları arasından filtrele
        $materialKmats = explode('|', $rowData['tk_kmats']);
        $filteredWorkCenterKmats = array_filter($workCenterKmats, function($item) use ($materialKmats) {
            return in_array($item['kmat'], $materialKmats);
        });

        // Filtrelenmiş kmatları aktarımdan gelen kmatlarla çarpıştır
        $nachbauKmats = explode('|', $rowData['nachbau_kmats']);
        $filteredWorkCenterKmats = array_filter($filteredWorkCenterKmats, function($item) use ($nachbauKmats) {
            return in_array($item['kmat'], $nachbauKmats);
        });

        echo json_encode($filteredWorkCenterKmats);
        exit();
    }

}

$controller = new WorkCenterController($_POST);

$response = match ($_GET['action']) {
    'getWorkCenters' => $controller->getWorkCenters(),
    'getWorkCentersBySearch' => $controller->getWorkCentersBySearch(),
    'getSubWorkCentersOfWorkCenter' => $controller->getSubWorkCentersOfWorkCenter(),
    'getSubKmatNamesOfMaterial' => $controller->getSubKmatNamesOfMaterial(),
    'getParentAndSubKmatsOfMaterialBySubKmatName' => $controller->getParentAndSubKmatsOfMaterialBySubKmatName(),
    'getParentKmatsOfMaterial' => $controller->getParentKmatsOfMaterial(),
    'getDeviceChoosableKmats' => $controller->getDeviceChoosableKmats(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};
