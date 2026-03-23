<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class InterchangeDtoController extends BaseController
{
    public function getInterchangeDtosOfNachbau(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Interchange Dtos Of Nachbau Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Get Interchange Dtos Of Nachbau Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Get Interchange DTO");

        $interchangeDtos = [];
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $accTypical = $_GET['accTypical'];

        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT DISTINCT kmat_name as dto_number, description, typical_no
                  FROM nachbau_datas 
                  WHERE project_no = :pNo AND nachbau_no = :nNo AND typical_no != :accTypical AND description != ''
                  AND kmat_name LIKE '%::%'                  
                  AND kmat_name REGEXP :pattern
                  ORDER BY dto_number";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo'=>$nachbauNo, ':accTypical' => $accTypical, ':pattern' => $nachbauDtoPattern])['data'] ?? [];

        foreach($result as $dto) {
            $dtoDescription = strtolower($this->formatDescription($dto['description'], 5));

            if(str_contains($dtoDescription, 'interchang')) {
                // Interchange de . sonrası önemli. Mesela NX_002304.001 ve NX_002304.002 olarak bölebiliyorlar aynı Interchange Dto sunu. Bu ikisi ayrı çalışılmalı. O yüzden formatDtoNumber fonksiyonu kullanmadım cünkü o nokta sonrasınıda siliyor.
                $dto['dto_number'] = preg_replace('/^::\s*(KUKO_CON_CST_)?/', '', $dto['dto_number']);
                $interchangeDtos[] = $dto;
            }
        }

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Interchange Dtos Of Nachbau Returned | ".implode(' | ', $_POST));
        Journals::saveJournal("RETURNED | Interchange Dtos Of Nachbau Returned | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_VIEWED, implode(' | ', $_POST), "Get Interchange DTO");

        echo (json_encode($interchangeDtos));
        exit();
    }

    public function getMaterialListsOfInterchangeDto(): void {

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Material Lists of Interchange DTO Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Get Material Lists of Interchange DTO Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Get Interchange DTO Materials");

        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $dtoNumber = $_GET['dtoNumber'];
        $typicalsForDTO = $_GET['typicalsForDTO'];
        $productId = $this->getProductIdOfProject($projectNo);

        // 8BT2 -> 6 ID Product
        $workCenterId = ($productId === '6') ? 33 : 3;

        $query = "SELECT parent_kmat FROM material_kmat_subkmats WHERE work_center_id = :wcId GROUP BY parent_kmat";
        $parentKmats = array_column(DbManager::fetchPDOQueryData('dto_configurator', $query, [':wcId' => $workCenterId])['data'], 'parent_kmat') ?? [];

        $query = "SELECT sub_kmat FROM material_kmat_subkmats WHERE work_center_id = :wcId AND sub_kmat IS NOT NULL GROUP BY sub_kmat";
        $subKmats = array_column(DbManager::fetchPDOQueryData('dto_configurator', $query, [':wcId' => $workCenterId])['data'], 'sub_kmat') ?? [];

        $allKmats = array_unique(array_merge($parentKmats, $subKmats));
        $allKmatsWithZeros = array_map(fn($kmat) => '00' . $kmat, $allKmats);

        // Fetch existing material lists
        $query = "SELECT Id, position, typical_no, ortz_kz, panel_no, feld_name, kmat, qty, unit, kmat_name, parent_kmat, description 
              FROM nachbau_datas
              WHERE project_no = :pNo 
              AND nachbau_no = :nNo 
              AND typical_no IN (:typicals)
              AND (kmat IN (:allKmats) OR parent_kmat IN (:allKmats))
              ORDER BY Id ASC";

        $nachbauData = DbManager::fetchPDOQueryData('planning', $query, [
            ':pNo' => $projectNo, ':nNo' => $nachbauNo, ':typicals' => $typicalsForDTO, ':allKmats' => $allKmatsWithZeros
        ])['data'] ?? [];

        // Fetch saved changes from project_works_interchange
        $query = "SELECT * FROM project_works_interchange
              WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNumber AND typical_no IN (:typicals) AND deleted IS NULL";

        $interchangeChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [
            ':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => $dtoNumber, ':typicals' => $typicalsForDTO
        ])['data'] ?? [];

        foreach ($interchangeChanges as $change) {
            $insertIndex = null; // Track where to insert the row

            foreach ($nachbauData as $index => &$data) {
                $isParentKmat = str_starts_with($data['kmat'], '003013') || str_starts_with($data['kmat'], '003003');

                if ($data['position'] === $change['position'] && $data['typical_no'] === $change['typical_no'] && !$isParentKmat) {

                    // ✅ If operation is 'add', find the last row for the same ORTZ_KZ
                    if ($change['operation'] === 'add' && $change['ortz_kz'] === $data['ortz_kz']) {
                        $insertIndex = $index + 1; // Insert after the last found index
                    }

                    // ✅ If operation is replace or delete, update existing rows
                    if ($change['operation'] === 'replace' || $change['operation'] === 'delete') {
                        if (ltrim($data['kmat'], '0') === $change['material_deleted_number']) {
                            $data['interchangeDtoChangeId'] = $change['id'];
                            $data['added_number'] = $change['material_added_number'] ?? 'SİL';
                            $data['deleted_number'] = $change['material_deleted_number'];
                            $data['operation'] = $change['operation'];
                        }
                    }
                }
            }

            // ✅ If we found an insert position, insert the new row there
            if ($change['operation'] === 'add' && $insertIndex !== null) {
                array_splice($nachbauData, $insertIndex, 0, [[
                    'Id' => null,
                    'position' => $change['position'],
                    'typical_no' => $change['typical_no'],
                    'ortz_kz' => $change['ortz_kz'],
                    'panel_no' => $change['panel_no'],
                    'feld_name' => '',
                    'kmat' => $change['material_added_number'],
                    'qty' => $change['quantity'],
                    'unit' => $change['unit'],
                    'kmat_name' => $change['material_added_description'],
                    'parent_kmat' => $change['parent_kmat'],
                    'description' => '',
                    'interchangeDtoChangeId' => $change['id'],
                    'added_number' => $change['material_added_number'],
                    'deleted_number' => '',
                    'operation' => 'add'
                ]]);
            }
        }

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Material Lists of Interchange DTO Request Successful | ".implode(' | ', $_POST));
        Journals::saveJournal("RETURNED | Material Lists of Interchange DTO Request Successful | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_VIEWED, implode(' | ', $_POST), "Get Interchange DTO Materials");
        echo json_encode($nachbauData);
        exit();
    }


    public function saveInterchangeDtoChanges() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Save Interchange DTO Changes Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Save Interchange DTO Changes Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Save Interchange DTO Changes");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $dtoNumber = $_POST['dtoNumber'];
        $dtoDescription = $_POST['dtoDescription'];
        $changes = $_POST['changes'];
        $isRevisionNachbau = $_POST['isRevisionNachbau'];

        $productId = $this->getProductIdOfProject($projectNo);

        if ($productId === '6') {
            // 8BT2 -> 6 ID Product
            $workCenterId = 33;
            $workCenter = 'M5438';
            $workContent = 'PA Truck';
        }
        else {
            $workCenterId = 3;
            $workCenter = 'M5350';
            $workContent = 'PA Withdrawable Unit';
        }

        $savedChanges = [];
        foreach ($changes as $change) {
            $params = [];
            if ($change['operation'] === 'replace') {
                // Checking if added material in correct work center before replacing.
                $query = "SELECT work_center_id FROM materials WHERE material_number = :mAdded";
                $materialAddedWc = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mAdded' => $change['addedNumber']])['data'][0]['work_center_id'] ?? '';

                if (intval($materialAddedWc) !== $workCenterId)
                    returnHttpResponse(400, 'Added Material ' . $change['addedNumber'] . ' work center must be ' . $workContent);
            }

            $query = "INSERT INTO project_works_interchange(project_number, nachbau_number, dto_number, dto_description, product_id, material_added_starts_by, material_added_number, material_added_description, material_deleted_starts_by, material_deleted_number, material_deleted_description,
                   operation, quantity, unit, work_center_id, work_center, work_content, position, parent_kmat, typical_no, ortz_kz, panel_no, is_revision_change, send_to_review_by, approved_by)";


            $params[] = [
                $projectNo,
                $nachbauNo,
                $dtoNumber,
                $dtoDescription,
                $productId,
                $this->getSapMaterialPrefixByMaterialNo($change['addedNumber']),
                ($change['addedNumber'] === 'CLEAR') ? '' : $change['addedNumber'],
                $this->getMaterialDescriptionFromDtoConfigurator($change['addedNumber']),
                $this->getSapMaterialPrefixByMaterialNo($change['deletedNumber']),
                preg_replace('/^00/', '', $change['deletedNumber']),
                $change['rowData'][4],
                $change['operation'],
                $change['rowData'][5] . '.000',
                $change['rowData'][6],
                $workCenterId,
                $workCenter,
                $workContent,
                $change['rowData'][1],
                ltrim($change['rowData'][9], '0'),
                $change['rowData'][8],
                explode("/", $change['rowData'][7], 2)[0],
                explode("/", $change['rowData'][7], 2)[1],
                $isRevisionNachbau,
                SharedManager::$fullname,
                NULL
            ];

            $responseInsert = DbManager::fetchInsert('dto_configurator', $query, $params);
            $lastInsertedId = $responseInsert["pdoConnection"]->lastInsertId();
            $savedChanges[] = [
                'interchangeDtoChangeId' => $lastInsertedId,
                'rowIndex' => $change['rowIndex'],
                'table' => $change['table']
            ];
        }

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Save Interchange DTO Changes Request Successful | ".implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Save Interchange DTO Changes Request Successful | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_CREATED, implode(' | ', $_POST), "Save Interchange DTO Changes");

        echo json_encode(['savedChanges' => $savedChanges]);
        exit();
    }

    public function removeInterchangeDtoChange(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Remove Interchange DTO Change Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Remove Interchange DTO Change Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Remove Interchange DTO Changes");

        $interchangeDtoChangeId = $_POST['interchangeDtoChangeId'];

        $query = "UPDATE project_works_interchange SET deleted_user = :delUser, deleted = :deletedAt WHERE id = :id";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':id' => $interchangeDtoChangeId]);

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Interchange DTO Change Removed | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Interchange DTO Change Removed | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_DELETED, implode(' | ', $_POST), "Remove Interchange DTO Changes");
    }

    public function addInterchangeDtoMaterialToTypical() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Add Interchange DTO Material to Typical With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Add Interchange DTO Material to Typical With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Add Interchange DTO Material");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $dtoNumber = $_POST['dtoNumber'];
        $dtoDescription = $_POST['dtoDescription'];
        $typicalNo = $_POST['typicalNo'];
        $materialAddedNo = $_POST['materialNo'];
        $quantity = $_POST['quantity'];
        $unit = $_POST['unit'];
        $isRevisionNachbau = $_POST['isRevisionNachbau'];

        $productId = $this->getProductIdOfProject($projectNo);

        if ($productId === '6') {
            // 8BT2 -> 6 ID Product
            $workCenterId = 33;
            $workCenter = 'M5438';
            $workContent = 'PA Truck';
        }
        else {
            $workCenterId = 3;
            $workCenter = 'M5350';
            $workContent = 'PA Withdrawable Unit';
        }

        $parentKmat = $this->getParentKmatOfNachbauByWorkCenter($projectNo, $nachbauNo, $typicalNo, $workContent);
        if (empty($parentKmat))
            returnHttpResponse(400, 'Parent kmat of work center ' . $workContent . ' could not be found.');

        $query = "SELECT * FROM materials WHERE material_number = :mAddedNo";
        $material = DbManager::fetchPDOQueryData('dto_configurator', $query,[':mAddedNo' => $materialAddedNo])['data'][0] ?? [];

        if (intval($material['work_center_id']) !== $workCenterId)
            returnHttpResponse(400, 'Added Material ' . $materialAddedNo . ' work center must be ' . $workContent);

        $firstOrtzKzOfTypical = $this->getOrtzKzsOfProjectTypicalByKmat($projectNo, $nachbauNo, $typicalNo, $workContent)[0];

        $query = "INSERT INTO project_works_interchange(project_number, nachbau_number, dto_number, dto_description, product_id, material_added_starts_by, material_added_number, material_added_description, material_deleted_starts_by, material_deleted_number, material_deleted_description,
               operation, quantity, unit, work_center_id, work_center, work_content, position, parent_kmat, typical_no, ortz_kz, panel_no, is_revision_change, send_to_review_by, approved_by)";

        $params[] = [
            $projectNo,
            $nachbauNo,
            $dtoNumber,
            $dtoDescription,
            $productId,
            $this->getSapMaterialPrefixByMaterialNo($materialAddedNo),
            $materialAddedNo,
            $material['description'],
            '',
            '',
            '',
            'add',
            $quantity . '.000',
            $unit,
            $workCenterId,
            $workCenter,
            $workContent,
            '..2',
            ltrim($parentKmat, '0'),
            $typicalNo,
            $firstOrtzKzOfTypical['ortz_kz'],
            $firstOrtzKzOfTypical['panel_no'],
            $isRevisionNachbau,
            SharedManager::$fullname,
            NULL
        ];

        $responseInsert = DbManager::fetchInsert('dto_configurator', $query, $params);
        $lastInsertedId = $responseInsert["pdoConnection"]->lastInsertId();
        $savedChanges[] = [
            'interchangeDtoChangeId' => $lastInsertedId,
            'table' => 'datatable-' . $typicalNo
        ];

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Add Interchange DTO Material to Typical Successful | ".implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Add Interchange DTO Material to Typical Successful | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_CREATED, implode(' | ', $_POST), "Add Interchange DTO Material");

        echo json_encode(['savedChanges' => $savedChanges]);
        exit();
    }

    public function getParentKmatOfNachbauByWorkCenter($projectNo, $nachbauNo, $typicalNo, $workContent){
        $query = "SELECT kmat FROM nachbau_datas 
                WHERE project_no = :pNo AND nachbau_no = :nNo AND typical_no = :typicalNo AND kmat_name = :workContent";

        return DbManager::fetchPDOQuery('planning', $query,[':pNo' => $projectNo, ':nNo' => $nachbauNo, ':typicalNo' => $typicalNo, ':workContent' => $workContent])['data'][0]['kmat'] ?? '';
    }

    public function getOrtzKzsOfProjectTypicalByKmat($projectNo, $nachbauNo, $typicalNo, $workContent) {

        $query = "SELECT ortz_kz, panel_no FROM nachbau_datas 
                WHERE project_no = :pNo AND nachbau_no = :nNo AND typical_no = :typicalNo AND kmat_name = :workContent GROUP BY panel_no";

        return DbManager::fetchPDOQuery('planning', $query,[':pNo' => $projectNo, ':nNo' => $nachbauNo, ':typicalNo' => $typicalNo, ':workContent' => $workContent])['data'] ?? [];
    }
}


$controller = new InterchangeDtoController($_POST);

$response = match ($_GET['action']) {
    'getInterchangeDtosOfNachbau' => $controller->getInterchangeDtosOfNachbau(),
    'getMaterialListsOfInterchangeDto' => $controller->getMaterialListsOfInterchangeDto(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    default => ['status' => 400, 'message' => 'Invalid action'],
    'saveInterchangeDtoChanges' => $controller->saveInterchangeDtoChanges(),
    'removeInterchangeDtoChange' => $controller->removeInterchangeDtoChange(),
    'addInterchangeDtoMaterialToTypical' => $controller->addInterchangeDtoMaterialToTypical()
};
