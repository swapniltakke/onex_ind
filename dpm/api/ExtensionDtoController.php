<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class ExtensionDtoController extends BaseController
{
    public function getExtensionDtosOfNachbau(): void {
        $extensionDtos = [];
        $addedDtos = [];

        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $accTypical = $_GET['accTypical'];

        $query = "SELECT DISTINCT kmat_name as dto_number, description, typical_no
          FROM nachbau_datas
          WHERE project_no = :pNo AND nachbau_no = :nNo
          AND kmat_name LIKE '%::%'
          AND kmat_name REGEXP (SELECT d.rules FROM dto_configurator.rules d WHERE d.key='product_dto_names')
          AND description != ''
          ORDER BY dto_number";

        $nachbauExtensionDtos = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

        $query = "SELECT dto_number, description, typical_no FROM extension_dto_extra_typicals WHERE project_number = :pNo AND nachbau_number = :nNo";
        $extraTypicalsForExtensionDtos = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

        foreach ($nachbauExtensionDtos as &$row) {
            $row['is_extra_typical'] = false;
        }

        foreach ($extraTypicalsForExtensionDtos as &$row) {
            $row['is_extra_typical'] = true;
        }

        $mergedResult = array_merge($nachbauExtensionDtos, $extraTypicalsForExtensionDtos);
        $keywords = [
            'Additional Parts f',
            'Additional Parts for',
            'Additional Parts Only f 2 PhasesV: Extension',
            'Panel Adaptation 8BT2'
        ];

        foreach ($mergedResult as $dto) {
            foreach ($keywords as $keyword) {
                if (str_contains($dto['description'] ?? '', $keyword)) {

                    if (str_starts_with($dto['dto_number'], ':: KUKO'))
                        $dto['dto_number'] = $this->formatKukoDtoNumber($dto['dto_number']);
                    else
                        $dto['dto_number'] = $this->formatDtoNumber($dto['dto_number']);

                    $dto['has_extension_works'] = $this->isDtoHasExtensionWorks(
                        $projectNo, $nachbauNo, $dto['dto_number'], $dto['typical_no']
                    );

                    if (
                        !str_starts_with($dto['dto_number'], 'NOT_') &&
                        !str_starts_with($dto['dto_number'], 'NOTICE_') &&
                        !str_starts_with($dto['dto_number'], 'SPARE_') &&
                        !str_starts_with($dto['dto_number'], 'MINPRI_')
                    ) {
                        $extensionDtos[] = $dto;

                        if (!isset($addedDtos[$dto['dto_number']])) {
                            $accessoryDto = $dto;
                            $accessoryDto['typical_no'] = $accTypical;
                            $accessoryDto['has_extension_works'] = $this->isDtoHasExtensionWorks(
                                $projectNo, $nachbauNo, $dto['dto_number'], $accTypical
                            );

                            $extensionDtos[] = $accessoryDto;
                            $addedDtos[$dto['dto_number']] = true;
                        }
                    }

                    break;
                }
            }
        }

        echo(json_encode($extensionDtos));
        exit;
    }

    public function isDtoHasExtensionWorks($projectNo, $nachbauNo, $dtoNumber, $typicalNo): bool {
        $query = "SELECT id FROM project_works_extensions 
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNo AND dto_typical_no = :typicalNo AND deleted IS NULL LIMIT 1";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo'=>$nachbauNo, ':dtoNo' => $dtoNumber, ':typicalNo' => $typicalNo])['data'] ?? [];

        if (!empty($data))
            return true;

        return false;
    }


    public function getExtensionDtoTableData(): void
    {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Extension DTO Table Data Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Extension DTO Table Data Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Extension DTO");

        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $dtoNumber = $_GET['dtoNumber'];
        $dtoTypical = $_GET['typicalNo'];
        $panelNo = $_GET['panelNo'];
        $workCenter = $_GET['workCenter'];
        $workCenterId = $_GET['workCenterId'];
        $extensionKmatName = $_GET['kmatLabel'];

        $query = "SELECT product_type FROM projects WHERE project_number = :pNo AND nachbau_number = :nNo";
        $productId = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'][0]['product_type'] ?? '';

        $extensionKmatNachbauData = $this->getAllKmatsOfExtensionKmat($projectNo, $nachbauNo, $dtoTypical, $panelNo, $workCenterId, $extensionKmatName, $productId);
        if (empty($extensionKmatNachbauData))
            returnHttpResponse(400, 'No nachbau data found for this work center : ' . $workCenter);

        $extensionChangesData = $this->getChangesExtensionData($projectNo, $nachbauNo, $extensionKmatName, $dtoNumber, $dtoTypical, $panelNo);

        //Değişiklik yok ise aktarımdakileri direkt döndür
        if (empty($extensionChangesData)) {
            echo (json_encode($extensionKmatNachbauData));
            return;
        }

        //Aktarım datası ve değişikliklerin eklendiği tablo verisi
        $extensionTableData = $this->prepareExtensionTableData($extensionKmatNachbauData, $extensionChangesData);

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Extension DTO Table Data Returned Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("RETURNED | Extension DTO Table Data Returned Successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_VIEWED, implode(' | ', $_POST), "Extension DTO");
        echo (json_encode($extensionTableData)); exit;
    }

    public function getAllKmatsOfExtensionKmat($projectNo, $nachbauNo, $dtoTypical, $panelNo, $workCenterId, $extensionKmatName, $productId): array {

        $workCenterOfProducts = [
            '6' => [ // Product ID = 6 -> 8BT2
                'Ground Bar' => '31',
                'PRC' => '32',
                'Busbar' => '31',
                'Partition Plate' => '29|31',
                'Insulation Box' => '29',
            ],
            'default' => [ // Other NXAIRS
                'Ground Bar' => '12|24',
                'PRC' => '5',
                'Busbar' => '12',
                'GFK insulation tube' => '10',
                'Partition Plate' => '46',
                'Ledges' => '8|4',
                'Set of screws (Busbar)' => '14',
                'Insulation Box' => '14',
            ]
        ];

        // Mappingde bulunan ürün tipine göre work center id leri çek
        $selectedWorkCenters = $workCenterOfProducts[$productId] ?? $workCenterOfProducts['default'];
        $workCentersOfSelectedProduct = $selectedWorkCenters[$extensionKmatName] ?? null;

        if ($workCentersOfSelectedProduct !== null) {
            // clientdan gelen request de bulunan work center id leri, doğru ürünün id lerimi diye kontrolü yap. 8BT2 ise Ground Bar 31'dir. NXAIR ise 12'dir.
            $workCenterIdArray = explode('|', $workCenterId);
            $workCentersOfSelectedProductArray = explode('|', $workCentersOfSelectedProduct);

            if (empty(array_intersect($workCenterIdArray, $workCentersOfSelectedProductArray))) {
                returnHttpResponse(400, 'Invalid work center: ' . $workCenterId . ' is not allowed for ' . $extensionKmatName);
            }
        } else {
            returnHttpResponse(400, $extensionKmatName . ' KMAT could not found for this product.');
        }

        // Aktarımdaki istasyon KMATına ait veriler
        $query = "SELECT CONCAT('00', CAST(parent_kmat AS CHAR)) AS parent_kmat, 
                     CONCAT('00', CAST(sub_kmat AS CHAR)) AS sub_kmat
              FROM material_kmat_subkmats
              WHERE work_center_id IN (:wcIds) AND product_id = :pId
              GROUP BY CONCAT('00', CAST(parent_kmat AS CHAR)), CONCAT('00', CAST(sub_kmat AS CHAR));";

        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':wcIds' => $workCentersOfSelectedProductArray, ':pId' => $productId])['data'] ?? [];

        $parentKmats = array_filter(array_column($result, 'parent_kmat'), function($value) { return $value !== ''; });
        $subKmats = array_filter(array_column($result, 'sub_kmat'), function($value) { return $value !== ''; });
        $allKmats = array_unique(array_merge($parentKmats, $subKmats));

        $query = "SELECT Id as id, position, kmat as material_deleted_number, qty as quantity, unit,
                         kmat_name as material_description, parent_kmat, typical_no, ortz_kz, panel_no
                 FROM nachbau_datas
                 WHERE project_no = :pNo
                 AND nachbau_no = :nNo
                 AND typical_no = :typicalNo
                 AND ortz_kz = :ortzKz
                 AND (kmat IN (:pKmats) OR parent_kmat IN (:allKmats))
                 ORDER BY ortz_kz, typical_no, Id, position DESC";

        $parameters = [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':typicalNo' => $dtoTypical, ':ortzKz' => $panelNo, ':pKmats' => $parentKmats, ':allKmats' => $allKmats];
        return DbManager::fetchPDOQueryData('planning', $query, $parameters)['data'] ?? [];
    }

    public function getChangesExtensionData($projectNo, $nachbauNo, $extensionKmatName, $dtoNumber, $dtoTypical, $panelNo): array {
        // Aktarımdaki istasyon kmatına ait veriler
        $query = "SELECT * FROM project_works_extensions
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND extension_kmat_name = :extKmat
				    AND dto_number = :dtoNumber AND dto_typical_no = :dtoTypical AND ortz_kz = :ortzKz
				    AND deleted IS NULL";

        $parameters = [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':extKmat' => $extensionKmatName, ':dtoNumber' => $dtoNumber,
            ':dtoTypical' => $dtoTypical, ':ortzKz' => $panelNo];

        return DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'] ?? [];
    }

    public function saveExtensionDtoChange(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Save Extension DTO Change Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Save Extension DTO Change Request With Following Parameters  | ".implode(' | ', $_POST), PAGE_GENERAL, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Extension DTO");

        $extensionProjectWorks = [];
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $changedRows = $_POST['changedRows'];
        $productId = $this->getProductIdOfProject($projectNo);
        $typicalToPanelsMap = $this->getTypicalAndPanelsDictionary($projectNo, $nachbauNo);
        $isRevisionNachbau = $_POST['isRevisionNachbau'];

        $insertQuery = "INSERT INTO project_works_extensions(project_number, nachbau_number, dto_number, dto_description, dto_typical_no, product_id, material_added_starts_by, material_added_number, material_added_description, material_deleted_starts_by, material_deleted_number, material_deleted_description,
                   operation, is_accessory, quantity, unit, note, extension_kmat_name, work_center_id, work_center, work_content, position, parent_kmat, sub_kmat, typical_no, ortz_kz, panel_no, is_revision_change, send_to_review_by, approved_by) VALUES ";

        foreach ($changedRows as $index => $row) {
            if ($row['operation'] === 'add') {
                $panelNo = '';
                $addedMaterialStartsBy = $this->getSapMaterialPrefixByMaterialNo($row['materialAdded']);
                $addedMaterialDesc = $this->getMaterialDescriptionFromDtoConfigurator($row['materialAdded']);

                if (!empty($row['subKmat'])) {
                    $subKmats = explode('|', $row['subKmat']);
                    $subKmats = array_map(function($subKmat) { return '00' . $subKmat; }, $subKmats);

                    $query = "SELECT position, kmat as sub_kmat, kmat_name, parent_kmat FROM nachbau_datas
                              WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat IN (:subKmats)
                              GROUP BY kmat";
                    $result = DbManager::fetchPDOQuery('planning', $query,[':pNo' => $projectNo, ':nNo' => $nachbauNo, ':subKmats' => $subKmats])['data'][0] ?? [];

                    $parentKmat = ltrim($result['parent_kmat'], '0');
                    $subKmat = ltrim($result['sub_kmat'], '0');
                } else {
                    $parentKmats = explode('|', $row['parentKmat']);
                    $parentKmats = array_map(function($parentKmat) { return '00' . $parentKmat; }, $parentKmats);

                    $query = "SELECT position, kmat, kmat_name, parent_kmat FROM nachbau_datas
                              WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat IN (:parentKmats)
                              GROUP BY kmat";
                    $result = DbManager::fetchPDOQuery('planning', $query,[':pNo' => $projectNo, ':nNo' => $nachbauNo, ':parentKmats' => $parentKmats])['data'][0] ?? [];

                    $parentKmat = ltrim($result['kmat'], '0');
                    $subKmat = '';
                }

                foreach ($typicalToPanelsMap[$row['typicalNo']] as $panel) {
                    if ($panel['ortz_kz'] === $row['ortzKz']) {
                        $panelNo = $panel['panel_no'];
                        break;
                    }
                }


                $row['position'] = $this->increaseNachbauRowPosition($result['position']);
            } else {
                $query = "SELECT id, description, work_center_id FROM materials WHERE material_number = :materialNo";
                $materialData = DbManager::fetchPDOQuery('dto_configurator', $query, [':materialNo' => $row['materialDeleted']])['data'][0] ?? [];

                $deletedMaterialStartsBy = $this->getSapMaterialPrefixByMaterialNo($row['materialDeleted']);

                if ($row['operation'] === 'replace') {
                    $addedMaterialStartsBy = $this->getSapMaterialPrefixByMaterialNo($row['materialAdded']);
                    $addedMaterialDesc = $this->getMaterialDescriptionFromDtoConfigurator($row['materialAdded']);
                }

                if (empty($materialData))
                    returnHttpResponse(404, 'Deleted material ' . $row['materialDeleted'] . ' does not exist in database!');
                if ($materialData[0]['work_center_id'] === '0')
                    returnHttpResponse(404, 'Work center of deleted material number ' . $row['materialDeleted'] . ' is not defined. Please first update material\'s workcenter!');
                if ($row['operation'] === 'replace' && !$this->compareParentKmatsOfReplaceList($row['materialAdded'], $row['materialDeleted'], $productId))
                    returnHttpResponse(404, 'Work center of ' . $row['materialAdded'] . ' and work center of ' . $row['materialDeleted'] . ' are not equal!');

                //MALZEMENİN AKTARIMDAKİ BİR ÜST KMATI
                $parentKmatInNachbau = $this->getParentKmatsOfNachbauByMaterial($projectNo, $nachbauNo, $row['materialDeleted']);
                $kmatData = $this->getParentAndSubKmatByKmat(ltrim($parentKmatInNachbau, '0'));

                $parentKmat = $kmatData['parent_kmat'];
                $subKmat = $kmatData['sub_kmat'] ?? '';
            }

            $dtoDescription = $this->formatDescription($this->getNachbauDescriptionByDtoNumber($row['dtoNumber'], $projectNo, $nachbauNo,true), 4);

            $projectNumberKey = ":project_number$index";
            $nachbauNumberKey = ":nachbau_number$index";
            $dtoNumberKey = ":dto_number$index";
            $dtoDescriptionKey = ":dto_description$index";
            $dtoTypicalNo = ":dto_typical_no$index";
            $productIdKey = ":product_id$index";
            $addedMaterialStartsByKey = ":material_added_starts_by$index";
            $addedMaterialKey = ":material_added_number$index";
            $addedMaterialDescKey = ":material_added_description$index";
            $deletedMaterialStartsByKey = ":material_deleted_starts_by$index";
            $deletedMaterialKey = ":material_deleted_number$index";
            $deletedMaterialDescKey = ":material_deleted_description$index";
            $operationKey = ":operation$index";
            $isAccessoryKey = ":is_accessory$index";
            $positionKey = ":position$index";
            $quantityKey = ":quantity$index";
            $unitKey = ":unit$index";
            $noteKey = ":note$index";
            $extensionKmatNameKey = ":extension_kmat_name$index";
            $workCenterIdKey = ":work_center_id$index";
            $workCenterKey = ":work_center$index";
            $workContentKey = ":work_content$index";
            $parentKmatKey = ":parent_kmat$index";
            $subKmatKey = ":sub_kmat$index";
            $typicalNoKey = ":typical_no$index";
            $ortzKzKey = ":ortz_kz$index";
            $panelNoKey = ":panel_no$index";
            $isRevisionChangeKey = ":is_revision_change$index";
            $sendToReviewByKey = ":send_to_review_by$index";
            $approvedByKey = ":approved_by$index";

            $extensionProjectWorks[] = [
                $projectNumberKey => $projectNo,
                $nachbauNumberKey => $nachbauNo,
                $dtoNumberKey => $row['dtoNumber'],
                $dtoDescriptionKey => $dtoDescription,
                $dtoTypicalNo => $row['dtoTypicalNo'],
                $productIdKey => $productId,
                $addedMaterialStartsByKey => $addedMaterialStartsBy ?? '',
                $addedMaterialKey => ($row['materialAdded'] === 'CLEAR') ? '' : $row['materialAdded'],
                $addedMaterialDescKey => $addedMaterialDesc ?? '',
                $deletedMaterialStartsByKey =>  $deletedMaterialStartsBy ?? '',
                $deletedMaterialKey => $row['materialDeleted'],
                $deletedMaterialDescKey => $row['materialDescription'],
                $operationKey => $row['operation'],
                $isAccessoryKey => 0,
                $quantityKey => $row['qty'],
                $unitKey => $row['unit'],
                $noteKey => $row['note'],
                $extensionKmatNameKey => $row['extensionKmatName'],
                $workCenterIdKey => $row['workCenterId'],
                $workCenterKey => $row['workCenter'],
                $workContentKey => $row['workContent'],
                $positionKey => $row['position'],
                $parentKmatKey => $parentKmat,
                $subKmatKey => $subKmat,
                $typicalNoKey => $row['typicalNo'],
                $ortzKzKey => $row['ortzKz'],
                $panelNoKey => $row['operation'] === 'add' ? $panelNo : $row['panelNo'],
                $isRevisionChangeKey => $isRevisionNachbau,
                $sendToReviewByKey => SharedManager::$fullname,
                $approvedByKey => ''
            ];

            $insertQuery .= "($projectNumberKey,$nachbauNumberKey, $dtoNumberKey, $dtoDescriptionKey, $dtoTypicalNo, $productIdKey, $addedMaterialStartsByKey, $addedMaterialKey, $addedMaterialDescKey, $deletedMaterialStartsByKey, $deletedMaterialKey, $deletedMaterialDescKey,
                            $operationKey, $isAccessoryKey, $quantityKey, $unitKey, $noteKey, $extensionKmatNameKey, $workCenterIdKey, $workCenterKey, $workContentKey, $positionKey, $parentKmatKey, $subKmatKey, $typicalNoKey,
                            $ortzKzKey, $panelNoKey, $isRevisionChangeKey, $sendToReviewByKey, $approvedByKey),";
        }


        $insertQuery = rtrim($insertQuery, ',');
        DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $extensionProjectWorks, [], false);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Changes for Extension DTO Saved Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Changes for Extension DTO Saved Successfully | ".implode(' | ', $_POST), PAGE_GENERAL, DESIGN_DETAIL_EXTENSION_DTO, ACTION_CREATED, implode(' | ', $_POST), "Extension DTO");
    }

    public function prepareExtensionTableData($extensionKmatNachbauData, $extensionChangesData): array
    {
        $extensionTableData = $extensionKmatNachbauData;

        foreach ($extensionChangesData as $change) {
            foreach ($extensionTableData as $index => $row) {
                $changeParentKmat = !empty($change['sub_kmat']) ? $change['sub_kmat'] : $change['parent_kmat'];

                if ($change['operation'] === 'add') {
                    $isParentKmatMatch = preg_replace('/^00/', '', $row['material_deleted_number']) === $changeParentKmat;
                    if($isParentKmatMatch) {
                        $newRow = $this->prepareExtensionRowChangeData($row, $change);
                        array_splice($extensionTableData, $index + 1, 0, [$newRow]);
                    }
                } else {
                    $isTypicalMatch = $row['typical_no'] === $change['typical_no'];
                    $isPanelMatch = $row['ortz_kz'] === $change['ortz_kz'];
                    $isParentKmatMatch = preg_replace('/^00/', '', $row['parent_kmat']) === $changeParentKmat;
                    $isMaterialMatch = preg_replace('/^00/', '', $row['material_deleted_number']) === $change['material_deleted_number'];

                    if ($isTypicalMatch && $isPanelMatch && $isParentKmatMatch && $isMaterialMatch)
                        $extensionTableData[$index] = $this->prepareExtensionRowChangeData($row, $change);
                }
            }
        }

        return $extensionTableData;
    }

    public function prepareExtensionRowChangeData($currentRow, $change): array
    {
        if ($change['operation'] === 'add') {
            return [
                'extension_change_id' => $change['id'],
                'position' => $change['position'],
                'typical_no' => $currentRow['typical_no'],
                'ortz_kz' => $currentRow['ortz_kz'],
                'panel_no' => $currentRow['panel_no'],
                'operation' => $change['operation'],
                'dto_number' => $change['dto_number'],
                'material_added_number' => $change['material_added_number'],
                'material_deleted_number' => '',
                'material_description' => $change['material_added_description'],
                'quantity' => $change['quantity'],
                'unit' => $change['unit'],
                'note' => $change['note'],
                'extension_kmat_name' => $change['extension_kmat_name'],
                'parent_kmat' => $change['parent_kmat'],
                'sub_kmat' => $change['sub_kmat'],
                'send_to_review_by' => $change['send_to_review_by'],
                'created' => $change['created'],
            ];
        } else {
            return [
                'extension_change_id' => $change['id'],
                'position' => $currentRow['position'],
                'typical_no' => $currentRow['typical_no'],
                'ortz_kz' => $currentRow['ortz_kz'],
                'panel_no' => $currentRow['panel_no'],
                'operation' => $change['operation'],
                'dto_number' => $change['dto_number'],
                'material_added_number' => $change['material_added_number'],
                'material_deleted_number' => $change['material_deleted_number'],
                'material_description' => $change['material_deleted_description'],
                'quantity' => $change['quantity'],
                'unit' => $change['unit'],
                'extension_kmat_name' => $change['extension_kmat_name'],
                'parent_kmat' => $change['parent_kmat'],
                'sub_kmat' => $change['sub_kmat'],
                'send_to_review_by' => $change['send_to_review_by'],
                'created' => $change['created'],
            ];
        }
    }


    public function removeAllExtensionDtoChanges(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Remove All Extension DTO Changes Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Remove All Extension DTO Changes Request With Following Parameters  | ".implode(' | ', $_POST), PAGE_GENERAL, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Extension DTO");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $extensionKmatName = $_POST['extensionKmatName'];

        $query = "SELECT id FROM project_works_extensions WHERE project_number = :pNo AND nachbau_number = :nNo AND extension_kmat_name = :extensionKmatName AND deleted IS NULL";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query,[':pNo' => $projectNo, ':nNo' => $nachbauNo, ':extensionKmatName' => $extensionKmatName]) ?? [];

        if (empty($result))
            returnHttpResponse(404, 'There is no changes under this ' . $extensionKmatName . ' KMAT');

        $ids = array_column($result, 'id');
        $query = "UPDATE project_works_extensions SET deleted = :deletedAt, deleted_user = :delUser WHERE id IN (:ids)";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $ids]);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | All the Changes for Extension DTO Removed Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | All the Changes for Extension DTO Removed Successfully | ".implode(' | ', $_POST), PAGE_GENERAL, DESIGN_DETAIL_EXTENSION_DTO, ACTION_DELETED, implode(' | ', $_POST), "Extension DTO");
    }

    public function removeExtensionDtoChange(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Remove Extension DTO Change Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Remove Extension DTO Change Request With Following Parameters  | ".implode(' | ', $_POST), PAGE_GENERAL, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Extension DTO");

        $id = $_POST['extensionChangeId'];
        $query = "SELECT id FROM project_works_extensions WHERE id = :id";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $id])['data'] ?? [];

        if (empty($result))
            returnHttpResponse(404, 'No Extension DTO Change found to be deleted.');

        $query = "UPDATE project_works_extensions SET deleted = :deletedAt, deleted_user = :delUser WHERE id = :id";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':id' => $id]);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Changes for Extension DTO Removed Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Changes for Extension DTO Removed Successfully | ".implode(' | ', $_POST), PAGE_GENERAL, DESIGN_DETAIL_EXTENSION_DTO, ACTION_DELETED, implode(' | ', $_POST), "Extension DTO");
    }

    public function getPanelsOfDtoWithExtensionWork(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Extension DTO Overview Matrix Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Extension DTO Overview Matrix With Following Parameters  | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Extension DTO");

        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $dtoNumber = $_GET['dtoNumber'];
        $typicalNo = $_GET['typicalNo'];
        $isExtraTypical = $_GET['isExtraTypical'] === 'true';

        if(!$isExtraTypical) {
            // Fetch panels of extension dto
            $query = "SELECT ortz_kz FROM nachbau_datas
                  WHERE project_no=:pNo AND nachbau_no=:nNo AND kmat_name LIKE :dtoNo AND typical_no=:typicalNo AND ortz_kz != ''
                  GROUP BY ortz_kz";
            $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo,':nNo' => $nachbauNo,':dtoNo' => "%$dtoNumber%",':typicalNo' => $typicalNo])['data'] ?? [];
        } else {
            // If it is an extra added typical, then get all panels of typical.
            $query = "SELECT DISTINCT ortz_kz FROM nachbau_datas
                  WHERE project_no=:pNo AND nachbau_no=:nNo AND typical_no=:typicalNo AND ortz_kz != ''
                  GROUP BY ortz_kz";
            $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo,':nNo' => $nachbauNo, ':typicalNo' => $typicalNo])['data'] ?? [];
        }

        // Prepare response with panels
        $ortzKzAndPanelNos = array_map(function($row) use ($projectNo, $nachbauNo, $dtoNumber, $typicalNo) {
            $panelData = ['ortz_kz' => $row['ortz_kz'], 'has_work' => []];

            $kmatObjects = [
                'Ground Bar', 'PRC', 'Busbar', 'GFK insulation tube',
                'Partition Plate', 'Ledges', 'Set of screws (Busbar)', 'Insulation Box'
            ];

            foreach ($kmatObjects as $kmatObject) {
                $workQuery = "SELECT ortz_kz FROM project_works_extensions
                          WHERE project_number = :pNo AND nachbau_number = :nNo
                          AND dto_number = :dtoNo AND typical_no = :typicalNo
                          AND ortz_kz = :ortzKz AND extension_kmat_name = :kmatName AND is_accessory=0
                          AND deleted IS NULL";

                $parameters = [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNo' => $dtoNumber, ':typicalNo' => $typicalNo, ':ortzKz' => $row['ortz_kz'], ':kmatName' => $kmatObject];
                $hasWork = DbManager::fetchPDOQueryData('dto_configurator', $workQuery, $parameters)['data'] ?? [];

                $panelData['has_work'][$kmatObject] = !empty($hasWork);
            }

            return $panelData;
        }, $result);


        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Extension DTO Overview Matrix Retrieved Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("RETURNED | Extension DTO Overview Matrix Retrieved Successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_VIEWED, implode(' | ', $_POST), "Extension DTO");

        $data = ['panels' => $ortzKzAndPanelNos];
        echo(json_encode($data)); exit;
    }

    public function saveExtensionAccessoryChange(): void
    {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Save Accessory Work for Extension DTO Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Save Accessory Work for Extension DTO Request With Following Parameters  | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Extension DTO");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $dtoNumber = $_POST['dtoNumber'];
        $typicalNo = $_POST['typicalNo'];
        $accessoryParentKmat = $_POST['accessoryParentKmat'];
        $extensionDtoData = $_POST['extensionDtoData'];
        $isRevisionNachbau = $_POST['isRevisionNachbau'];
        $productId = $this->getProductIdOfProject($projectNo);

        $insertQuery = "INSERT INTO project_works_extensions(project_number, nachbau_number, dto_number, dto_description, dto_typical_no, product_id, material_added_starts_by, material_added_number, material_added_description, material_deleted_starts_by, material_deleted_number, material_deleted_description,
                   operation, is_accessory, quantity, unit, extension_kmat_name, work_center_id, work_center, work_content, position, parent_kmat, sub_kmat, typical_no, ortz_kz, panel_no, is_revision_change, send_to_review_by, approved_by) VALUES ";

        foreach($extensionDtoData as $index => $row) {
            $query = "SELECT work_center_id, description FROM materials WHERE material_number = :mNo";
            $material = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mNo' => $row['materialNumber']])['data'][0] ?? [];

            if ($row['affectedKmat']['label'] !== 'Earthing Switch Operating Tool') {
                if (!str_contains($row['affectedKmat']['work_center_id'], $material['work_center_id'])) {
                    returnHttpResponse(400, 'Material ' . $row['materialNumber'] . ' must have KMAT name called ' . $row['affectedKmat']['kmat_name']);
                }
            }

            $dtoDescription = $this->formatDescription($this->getNachbauDescriptionByDtoNumber($dtoNumber, $projectNo, $nachbauNo, true), 4);

            $projectNumberKey = ":project_number$index";
            $nachbauNumberKey = ":nachbau_number$index";
            $dtoNumberKey = ":dto_number$index";
            $dtoDescriptionKey = ":dto_description$index";
            $dtoTypicalNoKey = ":dto_typical_no$index";
            $productIdKey = ":product_id$index";
            $addedMaterialStartsByKey = ":material_added_starts_by$index";
            $addedMaterialKey = ":material_added_number$index";
            $addedMaterialDescKey = ":material_added_description$index";
            $deletedMaterialStartsByKey = ":material_deleted_starts_by$index";
            $deletedMaterialKey = ":material_deleted_number$index";
            $deletedMaterialDescKey = ":material_deleted_description$index";
            $operationKey = ":operation$index";
            $isAccessoryKey = ":is_accessory$index";
            $positionKey = ":position$index";
            $quantityKey = ":quantity$index";
            $unitKey = ":unit$index";
            $extensionKmatNameKey = ":extension_kmat_name$index";
            $workCenterIdKey = ":work_center_id$index";
            $workCenterKey = ":work_center$index";
            $workContentKey = ":work_content$index";
            $parentKmatKey = ":parent_kmat$index";
            $subKmatKey = ":sub_kmat$index";
            $typicalNoKey = ":typical_no$index";
            $ortzKzKey = ":ortz_kz$index";
            $panelNoKey = ":panel_no$index";
            $isRevisionChangeKey = ":is_revision_change$index";
            $sendToReviewByKey = ":send_to_review_by$index";
            $approvedByKey = ":approved_by$index";

            $extensionProjectWorks[] = [
                $projectNumberKey => $projectNo,
                $nachbauNumberKey => $nachbauNo,
                $dtoNumberKey => $dtoNumber,
                $dtoDescriptionKey => $dtoDescription,
                $dtoTypicalNoKey => $typicalNo,
                $productIdKey => $productId,
                $addedMaterialStartsByKey => $this->getSapMaterialPrefixByMaterialNo($row['materialNumber']),
                $addedMaterialKey => $row['materialNumber'],
                $addedMaterialDescKey => $material['description'],
                $deletedMaterialStartsByKey => '',
                $deletedMaterialKey => '',
                $deletedMaterialDescKey => '',
                $operationKey => 'add',
                $isAccessoryKey => 1,
                $quantityKey => '1.000',
                $unitKey => 'ST',
                $extensionKmatNameKey => $row['kmatName'],
                $workCenterIdKey => $material['work_center_id'],
                $workCenterKey => $row['affectedKmat']['work_center'],
                $workContentKey => $row['affectedKmat']['kmat_name'],
                $positionKey => '.1',
                $parentKmatKey => $accessoryParentKmat,
                $subKmatKey => '',
                $typicalNoKey => $typicalNo,
                $ortzKzKey => $row['panel'],
                $panelNoKey => $this->getAccessoryKmatObjectOfProject($projectNo)['panel_no'],
                $isRevisionChangeKey => $isRevisionNachbau,
                $sendToReviewByKey => SharedManager::$fullname,
                $approvedByKey => ''
            ];

            $insertQuery .= "($projectNumberKey,$nachbauNumberKey, $dtoNumberKey, $dtoDescriptionKey, $dtoTypicalNoKey, $productIdKey, $addedMaterialStartsByKey, $addedMaterialKey, $addedMaterialDescKey, $deletedMaterialStartsByKey, $deletedMaterialKey, $deletedMaterialDescKey,
                            $operationKey, $isAccessoryKey, $quantityKey, $unitKey, $extensionKmatNameKey, $workCenterIdKey, $workCenterKey, $workContentKey, $positionKey, $parentKmatKey, $subKmatKey, $typicalNoKey, $ortzKzKey, $panelNoKey, $isRevisionChangeKey, $sendToReviewByKey, $approvedByKey),";
        }

        $insertQuery = rtrim($insertQuery, ',');
        DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $extensionProjectWorks, [], false);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Accessory Work Changes for Extension DTO Saved Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Accessory Work Changes for Extension DTO Saved Successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_CREATED, implode(' | ', $_POST), "Extension DTO");

    }

    public function getExtensionDtoAccessoryWorks(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Retrieve Accessory Works for Extension DTO Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Retrieve Accessory Works for Extension DTO Request With Following Parameters  | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Extension DTO");

        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $dtoNumber = $_GET['dtoNumber'];
        $typicalNo = $_GET['typicalNo'];

        $kmatObjects = [
            'Ground Bar', 'PRC', 'Busbar', 'GFK insulation tube',
            'Partition Plate', 'Ledges', 'Set of screws (Busbar)', 'Insulation Box', 'Earthing Switch Operating Tool'
        ];

        $panelData = [];

        foreach ($kmatObjects as $kmatObject) {
            $workQuery = "SELECT id, ortz_kz, material_added_number, material_added_description, note FROM project_works_extensions
                      WHERE project_number = :pNo AND nachbau_number = :nNo
                      AND dto_number = :dtoNo AND typical_no = :typicalNo
                      AND extension_kmat_name = :kmatName AND is_accessory = 1
                      AND deleted IS NULL
                      ORDER BY id ASC";

            $parameters = [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNo' => $dtoNumber, ':typicalNo' => $typicalNo,':kmatName' => $kmatObject];
            $works = DbManager::fetchPDOQueryData('dto_configurator', $workQuery, $parameters)['data'] ?? [];

            // If work exists, iterate through each panel and add it to panelData
            foreach ($works as $work) {
                $panelNumber = $work['ortz_kz'];

                // Append materials under the same KMAT key as an array of objects
                $panelData[$panelNumber][$kmatObject][] = [
                    'extension_work_id' => $work['id'],
                    'material_no' => $work['material_added_number'],
                    'material_description' => $work['material_added_description'],
                    'note' => $work['note']
                ];
            }
        }

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Accessory Works for Extension DTO Request Retrieved Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("RETURNED | Accessory Works for Extension DTO Request Retrieved Successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_MODIFIED, implode(' | ', $_POST), "Extension DTO");

        echo (json_encode($panelData));
        exit();
    }

    public function removeExtensionAccessoryChange(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Remove Accessory Works for Extension DTO Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Remove Accessory Works for Extension DTO Request With Following Parameters  | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Extension DTO");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $dtoNumber = $_POST['dtoNumber'];
        $typicalNo = $_POST['typicalNo'];
        $panelName = $_POST['panelName'];

        //Extension DTO çalışmalarını temizle .
        $query = "UPDATE project_works_extensions SET deleted = :deletedAt, deleted_user = :delUser
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNo AND typical_no = :typicalNo AND ortz_kz = :panelName";

        $parameters = [':deletedAt' => date('Y-m-d H:i:s'), ':delUser' => SharedManager::$fullname, ':pNo' => $projectNo, ':nNo' => $nachbauNo,
            ':dtoNo' => $dtoNumber, ':typicalNo' => $typicalNo, ':panelName' => $panelName];

        DbManager::fetchPDOQuery('dto_configurator', $query, $parameters);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Accessory Work Changes for Extension DTO Deleted Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Accessory Work Changes for Extension DTO Deleted Successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_DELETED, implode(' | ', $_POST), "Extension DTO");
    }


    public function compareParentKmatsOfReplaceList($materialAddedNo, $materialDeletedNo, $productId): bool
    {
        $query = "SELECT parent_kmat, sub_kmat FROM material_kmat_subkmats WHERE material_number = :mNoAdded AND product_id = :productId";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mNoAdded' => $materialAddedNo, ':productId' => $productId])['data'] ?? [];
        $materialAddedParentKmats = array_filter(array_column($result, 'parent_kmat'));
        $materialAddedSubKmats = array_filter(array_column($result, 'sub_kmat'));
        $materialAddedKmats = array_unique(array_merge($materialAddedParentKmats, $materialAddedSubKmats));

        $query = "SELECT parent_kmat, sub_kmat FROM material_kmat_subkmats WHERE material_number = :mNoDeleted AND product_id = :productId";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mNoDeleted' => $materialDeletedNo, ':productId' => $productId])['data'] ?? [];
        $materialDeletedParentKmats = array_filter(array_column($result, 'parent_kmat'));
        $materialDeletedSubKmats = array_filter(array_column($result, 'sub_kmat'));
        $materialDeletedKmats = array_unique(array_merge($materialDeletedParentKmats, $materialDeletedSubKmats));

        $commonKmats = array_intersect($materialAddedKmats, $materialDeletedKmats);

        if (empty($commonKmats))
            return false;

        return true;
    }

    public function removeSelectedMaterialExtensionChange() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Remove Selected Material From Extension Accessory With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Remove Selected Material From Extension Accessory With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Remove Extension Accessory Material");

        $extensionWorkId = $_POST['extensionWorkId'];

        $query = "UPDATE project_works_extensions SET deleted_user = :delUser, deleted = :deletedAt WHERE id = :id AND is_accessory = 1";
        $params = [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':id' => $extensionWorkId];
            DbManager::fetchPDOQueryData('dto_configurator', $query, $params)['data'] ?? [];

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Selected Extension Accessory Material Deleted Successfully | " . implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Selected Extension Accessory Material Deleted Successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_DELETED, implode(' | ', $_POST), "Remove Extension Accessory Material");
    }

    public function updateExtensionPanelNameChange() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Update Extension Accessory Panel Name Change Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Update Extension Accessory Panel Name Change Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Update Extension Accessory Panel");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $dtoNumber = $_POST['dtoNumber'];
        $inputNewPanel = $_POST['inputNewPanel'];
        $existingPanelName = $_POST['existingPanelName'];

        $query = "SELECT id FROM project_works_extensions 
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNumber
                  AND ortz_kz = :panelName AND is_accessory = 1 AND deleted IS NULL";

        $params = [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => $dtoNumber, ':panelName' => $existingPanelName];
        $existingPanelResults = DbManager::fetchPDOQueryData('dto_configurator', $query, $params)['data'] ?? [];

        if(empty($existingPanelResults))
            returnHttpResponse(400, 'Panel name ' . $existingPanelName . ' could not found in database.');

        $existingPanelResultsIds = array_column($existingPanelResults, 'id');

        $query = "UPDATE project_works_extensions SET ortz_kz = :newPanelName WHERE id IN (:ids)";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':newPanelName' => $inputNewPanel, ':ids' => $existingPanelResultsIds])['data'];

        SharedManager::saveLog('log_dtoconfigurator',"UPDATED | Extension Accessory Panel Name Updated Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("UPDATED | Extension Accessory Panel Name Updated Successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_MODIFIED, implode(' | ', $_POST), "Update Extension Accessory Panel");
    }

    public function handleNoteToExtensionAccessoryMaterial() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Add Extension Accessory Note Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Add Extension Accessory Note Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Add Extension Accessory Note");

        $extensionWorkId = $_POST['extensionWorkId'];
        $extensionNote = $_POST['extensionNote'];

        $query = "UPDATE project_works_extensions SET note = :note WHERE id = :id";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':note' => $extensionNote, ':id' => $extensionWorkId])['data'];

        SharedManager::saveLog('log_dtoconfigurator',"UPDATED | Extension Accessory Note Added to Material Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("UPDATED | Extension Accessory Note Added to Material Successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_MODIFIED, implode(' | ', $_POST), "Add Extension Accessory Note");
    }

    public function deleteNoteFromExtensionAccessoryMaterial() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Delete Extension Accessory Note Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Delete Extension Accessory Note Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_PROCESSING, implode(' | ', $_POST), "Delete Extension Accessory Note");

        $extensionWorkId = $_POST['extensionWorkId'];

        $query = "UPDATE project_works_extensions SET note = NULL WHERE id = :id";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $extensionWorkId])['data'];

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Extension Accessory Note Deleted from Material Successfully | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Extension Accessory Note Deleted from Material Successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_EXTENSION_DTO, ACTION_DELETED, implode(' | ', $_POST), "Deleted Extension Accessory Note");
    }

    public function getExtraTypicalsOfExtensionProject() {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $currentExtensionTypicals = $_GET['currentExtensionTypicals'];

        $query = "SELECT typical_no FROM nachbau_datas 
                    WHERE project_no=:pNo AND nachbau_no=:nNo
                    GROUP BY typical_no ORDER BY typical_no";

        $projectTypicals = array_column(DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'], 'typical_no');

        $extraTypicals = array_diff($projectTypicals, $currentExtensionTypicals);

        $data = array_values($extraTypicals);
        echo json_encode($data);
        exit();
    }

    public function addExtraTypicalToExtensionProject() {

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $selectedTypical = $_POST['selectedTypical'];
        $extensionDtoNumbers = $_POST['extensionDtoNumbers'];

        foreach($extensionDtoNumbers as $dtoNumber) {
            $query = "SELECT description FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat_name LIKE :dtoNumber AND description != '' LIMIT 1";
            $description = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => '%'.$dtoNumber.'%'])['data'][0]['description'] ?? '';

            $query = "INSERT INTO extension_dto_extra_typicals(project_number, nachbau_number, dto_number, description, typical_no, created_by)";
            $params[] = [$projectNo, $nachbauNo, $dtoNumber, $description, $selectedTypical, SharedManager::$fullname];
            DbManager::fetchInsert('dto_configurator', $query, $params);
        }

    }
}


$controller = new ExtensionDtoController($_POST);

$response = match ($_GET['action']) {
    'getExtensionDtosOfNachbau' => $controller->getExtensionDtosOfNachbau(),
    'getPanelsOfDtoWithExtensionWork' => $controller->getPanelsOfDtoWithExtensionWork(),
    'getExtensionDtoAccessoryWorks' => $controller->getExtensionDtoAccessoryWorks(),
    'getExtensionDtoTableData' => $controller->getExtensionDtoTableData(),
    'getExtraTypicalsOfExtensionProject' => $controller->getExtraTypicalsOfExtensionProject(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    'saveExtensionAccessoryChange' => $controller->saveExtensionAccessoryChange(),
    'removeExtensionAccessoryChange' => $controller->removeExtensionAccessoryChange(),
    'saveExtensionDtoChange' => $controller->saveExtensionDtoChange(),
    'removeExtensionDtoChange' => $controller->removeExtensionDtoChange(),
    'removeAllExtensionDtoChanges' => $controller->removeAllExtensionDtoChanges(),
    'removeSelectedMaterialExtensionChange' => $controller->removeSelectedMaterialExtensionChange(),
    'updateExtensionPanelNameChange' => $controller->updateExtensionPanelNameChange(),
    'handleNoteToExtensionAccessoryMaterial' => $controller->handleNoteToExtensionAccessoryMaterial(),
    'deleteNoteFromExtensionAccessoryMaterial' => $controller->deleteNoteFromExtensionAccessoryMaterial(),
    'addExtraTypicalToExtensionProject' => $controller->addExtraTypicalToExtensionProject(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};
