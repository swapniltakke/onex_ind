<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class SpecialDtoController extends BaseController
{
    public function getSpecialDtosOfNachbau(): void {
        $specialDtosInProject = [];
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];

        $specialDtosInDatabase = $this->getAllSpecialDtosFromDatabase();
        $regexpPattern = implode('|', $specialDtosInDatabase);

        $query = "SELECT DISTINCT kmat_name as dto_number, description, typical_no
              FROM nachbau_datas
              WHERE project_no = :pNo AND nachbau_no = :nNo
                AND kmat_name REGEXP :specialDtosRegexp
              ORDER BY dto_number";

        $nachbauSpecialDtos = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':specialDtosRegexp' => $regexpPattern])['data'];

        foreach ($nachbauSpecialDtos as $dto) {
            if (str_starts_with($dto['dto_number'], ':: KUKO'))
                $dto['dto_number'] = $this->formatKukoDtoNumber($dto['dto_number']);
            else
                $dto['dto_number'] = $this->formatDtoNumber($dto['dto_number']);

            $specialDtosInProject[] = array(
                'dto_number' => $dto['dto_number'],
                'description' => $this->formatDescription($dto['description'], 5),
                'typical_no' => $dto['typical_no']
            );
        }

        echo(json_encode($specialDtosInProject));
        exit();
    }

    public function getAllSpecialDtosFromDatabase() {
        $query = "SELECT dto_number FROM special_dtos";
        return array_column(DbManager::fetchPDOQueryData('dto_configurator', $query)['data'], 'dto_number');
    }

    public function getSubMaterialListsOfWdaAndSpecialDtoChanges(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Sub Material Lists Of WDA and Special Dto Changes With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Get Sub Material Lists Of WDA and Special Dto Changes With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_PROCESSING, implode(' | ', $_POST), "Get Special DTO Works");


        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $typicalNo = $_GET['typicalNo'];
        $kmat = $_GET['kmat'];

        // tipiğin ilk panosunu getir. zaten spare wda tipik bazlı calısılıyor, panonun önemi yok.
        $ortzKz = $this->getOrtzKzsOfProjectTypical($projectNo, $nachbauNo, $typicalNo)[0];

        // Bu sorgu, PA Withdrawable Unit istasyonunun altındaki malzeme listelerini, istekden gelen tipiğe göre döndürüyor.
        $query = "SELECT id, typical_no, ortz_kz, panel_no,
                    CASE 
                        WHEN kmat LIKE '00%' THEN SUBSTRING(kmat, 3)
                        ELSE kmat 
                    END AS material_number,
                    SUM(qty) AS quantity,
                    unit,
                    kmat_name AS material_description
                FROM nachbau_datas
                WHERE project_no = :pNo
                  AND nachbau_no = :nNo
                  AND parent_kmat LIKE :pKmat
                  AND typical_no = :typicalNo
                  AND ortz_kz = :ortzKz
                  AND kmat NOT LIKE '0030%'
                GROUP BY kmat, typical_no, ortz_kz, panel_no, material_number, unit, kmat_name";
        $data = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':pKmat' => $kmat, ':typicalNo' => $typicalNo, ':ortzKz' => $ortzKz])['data'] ?? [];

        if (empty($data))
            returnHttpResponse(404, 'PA Withdrawable Unit KMAT does not have lists under this typical.');

        //Bu sorgu, WDA listelerinde DTO kaynaklı değişiklik yapıldıysa ona göre listeleri düzenleyip son spare wda listesini hazırlar.
        $panelsOfWdaTypical = $this->getOrtzKzsOfProjectTypical($projectNo, $nachbauNo, $typicalNo);
        $query = "SELECT material_added_number, material_added_description, material_deleted_number, material_deleted_description, release_item, release_quantity, release_type, unit
                  FROM order_changes WHERE project_number = :pNo AND nachbau_number = :nNo AND parent_kmat = :wdaKmat 
                  AND active = 1 AND (release_item = :typicalNo OR release_item IN (:panelsOfWdaTypical))";
        $specialDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo,
            ':wdaKmat' => $kmat, ':typicalNo' => $typicalNo, ':panelsOfWdaTypical' => $panelsOfWdaTypical])['data'] ?? [];

        if (!empty($specialDtoWorks)) {
            $existingMaterialNumbers = array_column($data, 'material_number');

            foreach ($specialDtoWorks as $work) {
                // REMOVE logic: remove material_deleted_number if it exists in $data
                if (!empty($work['material_deleted_number'])) {
                    $data = array_filter($data, function ($item) use ($work) {
                        return $item['material_number'] !== $work['material_deleted_number'];
                    });

                    // Also update existingMaterialNumbers
                    $existingMaterialNumbers = array_column($data, 'material_number');
                }

                // ADD logic: add material_added_number if it’s not already in $data
                if (!empty($work['material_added_number']) && !in_array($work['material_added_number'], $existingMaterialNumbers, true)) {
                    $data[] = [
                        "typical_no" => $typicalNo,
                        "ortz_kz" => '',
                        "panel_no" => $work['release_item'],
                        "material_number" => $work['material_added_number'],
                        "quantity" => $work['release_quantity'],
                        "unit" => $work['unit'],
                        "material_description" => $work['material_added_description']
                    ];

                    $existingMaterialNumbers[] = $work['material_added_number'];
                }
            }
        }


        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Sub Material Lists Of WDA and Special Dto Changes Retrieved Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("RETURNED | Sub Material Lists Of WDA and Special Dto Changes Retrieved Successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_PROCESSING, implode(' | ', $_POST), "Get Special DTO Works");
        echo (json_encode(array_values($data)));
        exit();
    }

    public function getSpecialDtoChangesOfTypical(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $dtoNumber = $_GET['dtoNumber'];
        $typicalNo = $_GET['typicalNo'];
        $wdaParentKmat = ltrim($_GET['wdaParentKmat'], '0'); // 30135570

        $query = "SELECT id, material_number, material_description, typical_no, wda_parent_kmat, release_quantity, release_unit FROM project_works_special_dtos
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNumber AND typical_no = :typicalNo AND wda_parent_kmat = :wdaKmat AND operation = 'add' AND deleted IS NULL";

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => $dtoNumber, ':typicalNo' => $typicalNo, ':wdaKmat' => $wdaParentKmat])['data'] ?? [];

        echo(json_encode($data)); exit();
    }

    public function saveSpecialDtoChangesToAccessory() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Inserting Special DTO Works With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Inserting Special DTO Works With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_PROCESSING, implode(' | ', $_POST), "Add Special DTO Works");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $materials = $_POST['materials'];
        $accessoryTypicalNumber = $_POST['accessoryTypicalNo'];
        $accessoryParentKmat = $_POST['accessoryParentKmat'];
        $isRevisionNachbau = $_POST['isRevisionNachbau'];

        $selectedMaterialsInNachbau = [];
        foreach($materials as $material) {
            $query = "SELECT * FROM nachbau_datas WHERE id = :id";
            $data = DbManager::fetchPDOQueryData('planning', $query, [':id' => $material['id']])['data'][0];

            $selectedMaterialsInNachbau[$material['id']] = $data;
        }

        $specialDtoWorks = [];
        $insertQuery = "INSERT INTO project_works_special_dtos (project_number, nachbau_number, dto_number, dto_description, operation, position, typical_no, ortz_kz, panel_no,
                        material_number, material_description, is_accessory, accessory_typical_number, accessory_parent_kmat, wda_parent_kmat, release_quantity, release_unit, is_revision_change, send_to_review_by, publish_by) VALUES ";

        $rowIndex = 0;
        // Hem delete hem add olarak eklemek lazım. Cünkü order summary de, aksesuara eklenecek olarak gözükecek. Aynı zamanda tipiktende silinecek.
        foreach ($materials as $material) {
            $rowData = $selectedMaterialsInNachbau[$material['id']];
            foreach (['delete', 'add'] as $operation) {
                $isAdd = $operation === 'add';

                $params = [
                    ":project_number$rowIndex" => $projectNo,
                    ":nachbau_number$rowIndex" => $nachbauNo,
                    ":dto_number$rowIndex" => $material['dto_number'],
                    ":dto_description$rowIndex" => $material['dto_description'],
                    ":operation$rowIndex" => $operation,
                    ":position$rowIndex" => $rowData['position'],
                    ":typical_no$rowIndex" => $rowData['typical_no'],
                    ":ortz_kz$rowIndex" => $rowData['ortz_kz'],
                    ":panel_no$rowIndex" => $rowData['panel_no'],
                    ":material_number$rowIndex" => $material['material_number'],
                    ":material_description$rowIndex" => $material['material_description'],
                    ":is_accessory$rowIndex" => $isAdd ? 1 : 0,
                    ":accessory_typical_number$rowIndex" => $accessoryTypicalNumber,
                    ":accessory_parent_kmat$rowIndex" => $accessoryParentKmat,
                    ":wda_parent_kmat$rowIndex" => ltrim($material['wda_kmat'], '0'),
                    ":release_quantity$rowIndex" => $rowData['qty'],
                    ":release_unit$rowIndex" => $rowData['unit'],
                    ":is_revision_change$rowIndex" => $isRevisionNachbau,
                    ":send_to_review_by$rowIndex" => SharedManager::$fullname,
                    ":publish_by$rowIndex" => null
                ];

                $specialDtoWorks[] = $params;

                $insertQuery .= "(" . implode(', ', array_keys($params)) . "),";
                $rowIndex++;
            }
        }

        $insertQuery = rtrim($insertQuery, ',');
        DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $specialDtoWorks, [], false);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Special DTO Works Created With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Special DTO Works Created With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_CREATED, implode(' | ', $_POST), "Add Special DTO Works");
    }

    public function removeSpecialDtoChange(): void {
        $id = $_POST['id'];

        $query = "SELECT * FROM project_works_special_dtos WHERE id = :id";
        $addedMaterialResult = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id])['data'][0];

        if (empty($addedMaterialResult))
            returnHttpResponse(404, "Special dto work could not found in DB!");

        $query = "SELECT id FROM project_works_special_dtos WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNumber AND
                        is_accessory = 0 AND material_number = :materialNo AND typical_no = :typicalNo AND ortz_kz = :ortzKz AND panel_no = :panelNo";
        $params = [':pNo' => $addedMaterialResult['project_number'], ':nNo' => $addedMaterialResult['nachbau_number'], ':dtoNumber' => $addedMaterialResult['dto_number'],
                   ':materialNo' => $addedMaterialResult['material_number'], ':typicalNo' => $addedMaterialResult['typical_no'], ':ortzKz' => $addedMaterialResult['ortz_kz'], ':panelNo' => $addedMaterialResult['panel_no']];
        $deletedMaterialResult = DbManager::fetchPDOQuery('dto_configurator', $query, $params)['data'][0];

        $deletedIds = [$id, $deletedMaterialResult['id']];

        $query = "UPDATE project_works_special_dtos SET deleted_user = :delUser, deleted = :deletedAt WHERE id IN (:ids)";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $deletedIds]);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Special DTO Work Deleted With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Special DTO Work Deleted With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_CREATED, implode(' | ', $_POST), "Delete Special DTO Work");
    }

    public function removeAllSpecialDtoChanges() {
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $dtoNumber = $_POST['dtoNumber'];
        $typicalNo = $_POST['typicalNo'];

        $query = "SELECT id FROM project_works_special_dtos WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNumber AND typical_no = :typicalNo AND deleted IS NOT NULL";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => $dtoNumber, ':typicalNo' => $typicalNo])['data'];

        if (empty($result))
            returnHttpResponse(404, "No special dto works found in DB for this dto number!");

        $ids = array_column($result, 'id');

        $query = "UPDATE project_works_special_dtos SET deleted_user = :delUser, deleted = :deletedAt WHERE id IN (:ids)";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $ids]);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | All Special DTO Works Are Deleted With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | All Special DTO Works Are Deleted With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_CREATED, implode(' | ', $_POST), "Delete All Special DTO Works");
    }
}


$controller = new SpecialDtoController($_POST);

$response = match ($_GET['action']) {
    'getSpecialDtosOfNachbau' => $controller->getSpecialDtosOfNachbau(),
    'getSpecialDtoChangesOfTypical' => $controller->getSpecialDtoChangesOfTypical(),
    'getSubMaterialListsOfWdaAndSpecialDtoChanges' => $controller->getSubMaterialListsOfWdaAndSpecialDtoChanges(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};


$response = match ($_POST['action']) {
    'saveSpecialDtoChangesToAccessory' => $controller->saveSpecialDtoChangesToAccessory(),
    'removeSpecialDtoChange' => $controller->removeSpecialDtoChange(),
    'removeAllSpecialDtoChanges' => $controller->removeAllSpecialDtoChanges(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};