<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('memory_limit', '4096M');

class NachbauTransferController extends BaseController
{
    public function checkNachbauTransferDtoDifferences(): void
    {
        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized Designer for Transfer Nachbaus | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized Designer for Transfer Nachbaus | ".implode(' | ', $_POST),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Nachbau Transfer");
            returnHttpResponse(400, "Unauthorized user to transfer nachbaus.");
        }

        $projectNo = $_GET['projectNo'];
        $selectedNachbau = $_GET['selectedNachbau'];
        $transferToNachbau = $_GET['transferToNachbau'];

        // ✅ Validate input parameters
        if (empty($projectNo) || empty($selectedNachbau) || empty($transferToNachbau)) {
            returnHttpResponse(400, "Missing required parameters.");
        }

        // Check nachbau creation times
        $query = "SELECT Created FROM log_nachbau WHERE FactoryNumber = :projectNo AND FileName = :fileName ORDER BY ID ASC LIMIT 1";
        $selectedNachbauData = DbManager::fetchPDOQueryData('logs', $query, [':projectNo' => $projectNo, ':fileName' => $selectedNachbau])['data'][0] ?? [];
        $transferToNachbauData = DbManager::fetchPDOQueryData('logs', $query, [':projectNo' => $projectNo, ':fileName' => $transferToNachbau])['data'][0] ?? [];

        if (empty($selectedNachbauData) || empty($transferToNachbauData)) {
            returnHttpResponse(400, "One or both nachbau records not found in logs.");
        }

        $selectedNachbauTime = $selectedNachbauData['Created'];
        $transferToNachbauTime = $transferToNachbauData['Created'];

        if (strtotime($transferToNachbauTime) < strtotime($selectedNachbauTime)) {
            returnHttpResponse(400, "Target nachbau cannot be created before current nachbau.");
        }

        $isTransferToNachbauPublished = $this->checkIfProjectHasReleased($projectNo, $transferToNachbau);
        if($isTransferToNachbauPublished) {
            returnHttpResponse(400, "Target nachbau has already been published before!");
        }

        // ✅ FIX: Fetch rules pattern once from dto_configurator
        $rulesQuery = "SELECT rules FROM rules WHERE key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        if (empty($nachbauDtoPattern)) {
            // Fallback pattern if rules not found
            $nachbauDtoPattern = '^:: [A-Z]';
        }

        // ✅ SEÇİLİ AKTARIMDA BULUNAN ANCAK TRANSFERTO AKTARIMDA BULUNMAYAN DTO LİSTESİ
        $query = "SELECT DISTINCT kmat_name FROM nachbau_datas 
                WHERE project_no = :pNo 
                AND kmat_name REGEXP :pattern
                AND nachbau_no = :selectedNachbau 
                AND (kmat_name LIKE '%::%' OR kmat_name LIKE '%,:%')
                EXCEPT
                SELECT DISTINCT kmat_name FROM nachbau_datas 
                WHERE project_no = :pNo 
                AND kmat_name REGEXP :pattern
                AND nachbau_no = :transferToNachbau 
                AND (kmat_name LIKE '%::%' OR kmat_name LIKE '%,:%')";

        $result = DbManager::fetchPDOQueryData('planning', $query, [
            ':pNo' => $projectNo,
            ':pattern' => $nachbauDtoPattern,
            ':selectedNachbau' => $selectedNachbau,
            ':transferToNachbau' => $transferToNachbau
        ])['data'] ?? [];

        $resultDtos = array_column($result, 'kmat_name') ?? [];
        $selectedNachbauExtraDtos = $this->populateNachbauDifferencesArray($resultDtos);

        // ✅ TRANSFERTO AKTARIMDA BULUNAN ANCAK SEÇİLİ AKTARIMDA BULUNMAYAN DTO LİSTESİ
        $query = "SELECT DISTINCT kmat_name FROM nachbau_datas 
                WHERE project_no = :pNo 
                AND kmat_name REGEXP :pattern
                AND nachbau_no = :transferToNachbau 
                AND (kmat_name LIKE '%::%' OR kmat_name LIKE '%,:%')
                EXCEPT
                SELECT DISTINCT kmat_name FROM nachbau_datas 
                WHERE project_no = :pNo 
                AND kmat_name REGEXP :pattern
                AND nachbau_no = :selectedNachbau 
                AND (kmat_name LIKE '%::%' OR kmat_name LIKE '%,:%')";

        $result = DbManager::fetchPDOQueryData('planning', $query, [
            ':pNo' => $projectNo,
            ':pattern' => $nachbauDtoPattern,
            ':transferToNachbau' => $transferToNachbau,
            ':selectedNachbau' => $selectedNachbau
        ])['data'] ?? [];

        $resultDtos = array_column($result, 'kmat_name') ?? [];
        $transferToNachbauExtraDtos = $this->populateNachbauDifferencesArray($resultDtos);

        $data = [
            'selectedNachbauExtraDtos' => $selectedNachbauExtraDtos,
            'transferToNachbauExtraDtos' => $transferToNachbauExtraDtos
        ];

        echo json_encode($data);
        exit();
    }

    public function populateNachbauDifferencesArray($resultDtos): array
    {
        $dtoDifferences = [];

        foreach ($resultDtos as $dto_number) {
            $founded = false;
            $dto_number_replaced = str_replace(":: KUKO_CON_CST_", "", $dto_number);

            foreach ($resultDtos as $dto_number2) {
                if (str_contains($dto_number2, $dto_number_replaced) && $dto_number !== $dto_number2) {
                    $dtoDifferences[] = $dto_number2;
                    $founded = true;
                    break;
                }
            }

            if (!$founded && !in_array($dto_number_replaced, $dtoDifferences))
                $dtoDifferences[] = $dto_number;
        }

        return $dtoDifferences;
    }

    public function transferNachbauToAnother(): void
    {
        SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | Nachbau Transfer from " . $_POST['selectedNachbau'] . " to transferring into " . $_POST['transferToNachbau'] . " Request | " . implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Nachbau Transfer from " . $_POST['selectedNachbau'] . " to transferring into " . $_POST['transferToNachbau'] . " Request | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_OPERATIONS, ACTION_PROCESSING, implode(' | ', $_POST), "Nachbau Transfer");

        $projectNo = $_POST['projectNo'];
        $selectedNachbau = $_POST['selectedNachbau'];
        $transferToNachbau = $_POST['transferToNachbau'];
        $selectedNachbauExtraDtos = $_POST['selectedNachbauExtraDtos'];
        $transferToNachbauExtraDtos = $_POST['transferToNachbauExtraDtos'];
        $accessoryTypicalNumber = $_POST['accessoryTypicalNumber'];
        $accessoryParentKmat = $_POST['accessoryParentKmat'];

        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized Designer for Transfer Nachbaus | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized Designer for Transfer Nachbaus | ".implode(' | ', $_POST),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Nachbau Transfer");
            returnHttpResponse(400, "Unauthorized user to transfer nachbaus.");
            exit();
        }


        $selectedNachbauExtraDtosFormatted = [];
        if(!empty($selectedNachbauExtraDtos)) {
            $selectedNachbauExtraDtosFormatted = array_map(function ($dto) {
                return $this->formatDtoNumber($dto);
            }, $selectedNachbauExtraDtos);
        }

        $typicalToPanelsMap = $this->getTypicalAndPanelsDictionary($projectNo, $transferToNachbau);

        // OPTIMIZATION: Convert to array_flip for O(1) lookups
        $selectedNachbauExtraDtosFlipped = array_flip($selectedNachbauExtraDtosFormatted);

        // OPTIMIZATION: Fetch all typicals once, grouped by DTO
        $allTypicalsByDto = $this->getAllTypicalsGroupedByDto($projectNo, $transferToNachbau, $accessoryTypicalNumber);

        // Transfer Project Works
        $skippedProjectWorks = $this->transferProjectWorksBetweenNachbaus($projectNo, $selectedNachbau, $transferToNachbau, $selectedNachbauExtraDtos, $transferToNachbauExtraDtos, $accessoryTypicalNumber, $accessoryParentKmat);

        // Just released items in TransferToNachbau
        $query = "SELECT * FROM project_work_view WHERE project_number = :pNo AND nachbau_number = :nNo AND release_status  = 'to be released'";
        $lastProjectWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $transferToNachbau])['data'];

        // Transfer Bom Change
        $this->transferReleaseItemsToBomChange($lastProjectWorks, $accessoryParentKmat);

        // Transfer Spare DTO Changes if exists (yeni aktarımdaki ekstra dtoların önemi yok default oluşturma yok spare çalışmalarında)
        $skippedSpares = $this->transferSpareDtoWorks($projectNo, $selectedNachbau, $transferToNachbau, $allTypicalsByDto, $selectedNachbauExtraDtosFlipped);

        // Transfer Interchange Dto Changes if exists
        $skippedInterchanges = $this->transferInterchangeDtoChanges($projectNo, $selectedNachbau, $transferToNachbau, $allTypicalsByDto, $selectedNachbauExtraDtosFlipped, $typicalToPanelsMap);

        // Transfer Extension DTO Changes if exists (burada tipikler önemli, yeni aktarımda)
        $skippedExtensions = $this->transferExtensionDtoChanges($projectNo, $selectedNachbau, $transferToNachbau, $allTypicalsByDto, $selectedNachbauExtraDtosFlipped);

        // Transfer Minus Price Dto Changes
        $skippedMinusPrices = $this->transferMinusPriceDtoChanges($projectNo, $selectedNachbau, $transferToNachbau, $allTypicalsByDto, $selectedNachbauExtraDtosFlipped);

        // Transfer Special Dto Changes
        $skippedSpecials = $this->transferSpecialDtoChanges($projectNo, $selectedNachbau, $transferToNachbau, $allTypicalsByDto, $selectedNachbauExtraDtosFlipped, $typicalToPanelsMap);

        // Transfer Kuko Notes
        $this->transferKukoNotes($projectNo, $selectedNachbau, $transferToNachbau, $selectedNachbauExtraDtosFlipped);

        // Skipped kayıtları veritabanına kaydet
        $this->insertSkippedWorks($projectNo, $selectedNachbau, $transferToNachbau, 1, $skippedProjectWorks);
        $this->insertSkippedWorks($projectNo, $selectedNachbau, $transferToNachbau, 2, $skippedSpares);
        $this->insertSkippedWorks($projectNo, $selectedNachbau, $transferToNachbau, 3, $skippedExtensions);
        $this->insertSkippedWorks($projectNo, $selectedNachbau, $transferToNachbau, 4, $skippedMinusPrices);
        $this->insertSkippedWorks($projectNo, $selectedNachbau, $transferToNachbau, 6, $skippedInterchanges);
        $this->insertSkippedWorks($projectNo, $selectedNachbau, $transferToNachbau, 7, $skippedSpecials);

        // Insert project nachbau into dto configurator database
        $this->insertProjectNachbauIntoDtoConfigurator($projectNo, $transferToNachbau);

        $query = "UPDATE projects SET working_user=:whom, project_status = 2, last_updated_by=:whom, last_updated_date=:uptDate, transferred_from_nachbau=:transferredFromNachbau WHERE project_number=:pNo AND nachbau_number=:nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':whom' => SharedManager::$fullname, ':uptDate' => (new DateTime())->format('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $transferToNachbau, ':transferredFromNachbau' => $selectedNachbau]);

        $responseData = [
            'skipped' => [
                'projectWorks' => $skippedProjectWorks,
                'spare' => $skippedSpares,
                'interchange' => $skippedInterchanges,
                'extension' => $skippedExtensions,
                'minusPrice' => $skippedMinusPrices,
                'special' => $skippedSpecials
            ]
        ];

        SharedManager::saveLog('log_dtoconfigurator', "CREATED | Nachbau Transfer from " . $_POST['selectedNachbau'] . " to transferring into " . $_POST['transferToNachbau'] . " Request | " . implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Nachbau Transfer from " . $_POST['selectedNachbau'] . " to transferring into " . $_POST['transferToNachbau'] . " Request | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_OPERATIONS, ACTION_CREATED, implode(' | ', $_POST), "Nachbau Transfer");

        echo(json_encode($responseData));
        exit();
    }

    public function transferProjectWorksBetweenNachbaus($projectNo, $selectedNachbau, $transferToNachbau, $selectedNachbauExtraDtos, $transferToNachbauExtraDtos, $accessoryTypicalNumber, $accessoryParentKmat): array {
        $skippedWorks = [];

        // Selected aktarımdaki project workleri getir.
        $query = "SELECT * FROM project_work_view WHERE project_number = :projectNo AND nachbau_number = :selectedNachbau";
        $selectedNachbauProjectWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':projectNo' => $projectNo, ':selectedNachbau' => $selectedNachbau])['data'] ?? [];

        if (empty($selectedNachbauProjectWorks)) {
            $isProjectWorkedOnlySpare = $this->checkIfProjectHasSpareDtoWorks($projectNo, $selectedNachbau);

            if (!$isProjectWorkedOnlySpare)
                returnHttpResponse(404, 'There is no works in selected nachbau ('. $selectedNachbau .'). It could never worked before!');

            return [];
        }

        // TransferTo aktarımındaki mevcut çalışmaları temizle
        $this->removeAllProjectWorksInTransferToNachbau($projectNo, $transferToNachbau);

        // Delete/Replace operasyonları için malzeme kontrolü
        $materialsToCheck = [];
        foreach ($selectedNachbauProjectWorks as $row) {
            if (($row['operation'] === 'delete' || $row['operation'] === 'replace') && !empty($row['material_deleted_number'])) {
                $materialsToCheck[] = $row['material_deleted_number'];
            }
        }

        // Tüm malzemelerin nachbau'daki varlığını tek sorguda kontrol et
        $nachbauMaterialData = [];
        if (!empty($materialsToCheck)) {
            $nachbauMaterialData = $this->checkMaterialsExistInNachbau($projectNo, $transferToNachbau, $materialsToCheck);
        }

        $nachbauDataQuery = "SELECT typical_no, ortz_kz, panel_no, parent_kmat, kmat 
                             FROM nachbau_datas 
                             WHERE project_no = :pNo AND nachbau_no = :nNo";
        $nachbauData = DbManager::fetchPDOQueryData('planning', $nachbauDataQuery, [':pNo' => $projectNo, ':nNo' => $transferToNachbau])['data'];

        $transferToNachbauTypicals = array_filter(
            array_unique(array_column($nachbauData, 'typical_no')),
            fn($value) => $value !== '' && $value !== null
        );

        $transferToNachbauPanels = array_filter(
            array_unique(array_column($nachbauData, 'ortz_kz')),
            fn($value) => $value !== '' && $value !== null
        );

        $projectWorks = [];
        $index = 0;
        $productId = $selectedNachbauProjectWorks[0]['product_id'];
        $typicalToPanelsMap = $this->getTypicalAndPanelsDictionary($projectNo, $selectedNachbau);

        // ============================================
        // STEP 1: Mevcut project workleri aktar (seçili nachbau'daki ekstra DTO'ları hariç tut)
        // ============================================
        foreach ($selectedNachbauProjectWorks as $row) {
            $rowAddedToSkipped = false;
            $skippedErrorMessageId = null;

            // Delete/Replace ise malzeme yeni aktarımda var mı kontrolü
            $releaseItems = array_filter(explode('|', $row['release_items'] ?? ''));
            $accessoryReleaseItems = array_filter(explode('|', $row['accessory_release_items'] ?? ''));
            $allItemsToCheck = array_merge($releaseItems, $accessoryReleaseItems);

            // DTO ekstra ise (transfer-to nachbau'da yoksa) atla
            $isDtoExtra = false;
            if (!empty($selectedNachbauExtraDtos)) {
                foreach ($selectedNachbauExtraDtos as $dto) {
                    if (str_contains($dto, $row['dto_number'])) {
                        $isDtoExtra = true;
                        break;
                    }
                }
            }

            if ($isDtoExtra) {
                if ($row['error_message_id'] !== '8' && $row['error_message_id'] !== '9') {
                    // Check type and add to skippedWorks accordingly
                    if ($row['type'] === 'Typical' || $row['type'] === 'Accessories') {
                        // For Typical type, iterate through each typical and its panels
                        foreach ($allItemsToCheck as $typicalNo) {
                            if (isset($typicalToPanelsMap[$typicalNo])) {
                                foreach ($typicalToPanelsMap[$typicalNo] as $panelData) {
                                    $skippedWorks[] = [
                                        'project_number' => $row['project_number'],
                                        'selected_nachbau' => $selectedNachbau,
                                        'transfer_to_nachbau' => $transferToNachbau,
                                        'released_dto_type_id' => 1,
                                        'skip_reason' => 'DTO not found in target nachbau',
                                        'dto_number' => $row['dto_number'],
                                        'dto_description' => $row['dto_description'],
                                        'operation' => $row['operation'],
                                        'typical_no' => $typicalNo,
                                        'ortz_kz' => $panelData['ortz_kz'],
                                        'panel_no' => $panelData['panel_no'],
                                        'material_added_starts_by' => $row['material_added_starts_by'],
                                        'material_added_number' => $row['material_added_number'],
                                        'material_added_description' => $row['material_added_description'],
                                        'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                        'material_deleted_number' => $row['material_deleted_number'],
                                        'material_deleted_description' => $row['material_deleted_description'],
                                        'work_center' => $row['work_center'],
                                        'work_content' => $row['work_content'],
                                        'parent_kmat' => $row['common_kmats'],
                                        'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                        'unit' => $row['unit'],
                                        'error_message_id' => 2
                                    ];
                                    $skippedErrorMessageId = 2;
                                    $rowAddedToSkipped = true;
                                }
                            }
                        }
                    }
                    else if ($row['type'] === 'Panel') {
                        // For Panel type, iterate through all typicals and filter by ortz_kz
                        foreach ($allItemsToCheck as $ortzKz) {
                            foreach ($typicalToPanelsMap as $typical => $panels) {
                                foreach ($panels as $panelData) {
                                    if ($panelData['ortz_kz'] === $ortzKz) {
                                        $skippedWorks[] = [
                                            'project_number' => $row['project_number'],
                                            'selected_nachbau' => $selectedNachbau,
                                            'transfer_to_nachbau' => $transferToNachbau,
                                            'released_dto_type_id' => 1,
                                            'skip_reason' => 'DTO not found in target nachbau',
                                            'dto_number' => $row['dto_number'],
                                            'dto_description' => $row['dto_description'],
                                            'operation' => $row['operation'],
                                            'typical_no' => $typical,
                                            'ortz_kz' => $ortzKz,
                                            'panel_no' => $panelData['panel_no'],
                                            'material_added_starts_by' => $row['material_added_starts_by'],
                                            'material_added_number' => $row['material_added_number'],
                                            'material_added_description' => $row['material_added_description'],
                                            'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                            'material_deleted_number' => $row['material_deleted_number'],
                                            'material_deleted_description' => $row['material_deleted_description'],
                                            'work_center' => $row['work_center'],
                                            'work_content' => $row['work_content'],
                                            'parent_kmat' => $row['common_kmats'],
                                            'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                            'unit' => $row['unit'],
                                            'error_message_id' => 2
                                        ];
                                        $skippedErrorMessageId = 2;
                                        $rowAddedToSkipped = true;
                                    }
                                }
                            }
                        }
                    }
                    continue;
                } else if ($row['error_message_id'] === '9') {
                    continue;
                }
            }

            if (!empty($allItemsToCheck))
            {
                if ($row['operation'] !== 'add') {
                    if ($row['type'] === 'Typical' || $row['type'] === 'Accessories') {
                        foreach ($allItemsToCheck as $typicalNo) {
                            // 1. Check if typical exists in target nachbau
                            if (!in_array($typicalNo, $transferToNachbauTypicals)) {
                                // Typical not found - add all panels to skippedWorks
                                if (isset($typicalToPanelsMap[$typicalNo])) {
                                    foreach ($typicalToPanelsMap[$typicalNo] as $panelData) {
                                        $skippedWorks[] = [
                                            'project_number' => $row['project_number'],
                                            'selected_nachbau' => $selectedNachbau,
                                            'transfer_to_nachbau' => $transferToNachbau,
                                            'released_dto_type_id' => 1,
                                            'skip_reason' => 'Typical ' . $typicalNo . ' not exists in target nachbau.',
                                            'dto_number' => $row['dto_number'],
                                            'dto_description' => $row['dto_description'],
                                            'operation' => $row['operation'],
                                            'typical_no' => $typicalNo,
                                            'ortz_kz' => $panelData['ortz_kz'],
                                            'panel_no' => $panelData['panel_no'],
                                            'material_added_starts_by' => $row['material_added_starts_by'],
                                            'material_added_number' => $row['material_added_number'],
                                            'material_added_description' => $row['material_added_description'],
                                            'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                            'material_deleted_number' => $row['material_deleted_number'],
                                            'material_deleted_description' => $row['material_deleted_description'],
                                            'work_center' => $row['work_center'],
                                            'work_content' => $row['work_content'],
                                            'parent_kmat' => $row['common_kmats'],
                                            'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                            'unit' => $row['unit'],
                                            'error_message_id' => 11
                                        ];
                                        $skippedErrorMessageId = 11;
                                        $rowAddedToSkipped = true;

                                    }
                                }
                                continue;
                            }

                            // 3. Check if material exists in this typical (for delete/replace operations)
                            $materialFoundInThisTypical = false;

                            foreach ($nachbauMaterialData as $key => $exists) {
                                // Key format: material_typical_ortz
                                if (str_starts_with($key, $row['material_deleted_number'] . '_' . $typicalNo . '_')) {
                                    $materialFoundInThisTypical = true;
                                    break;
                                }
                            }

                            if (!$materialFoundInThisTypical) {
                                // Material not found in typical - add all panels to skippedWorks
                                if (isset($typicalToPanelsMap[$typicalNo])) {
                                    foreach ($typicalToPanelsMap[$typicalNo] as $panelData) {
                                        $parentKmats = [$row['common_kmats']];

                                        if ($row['operation'] !== 'add' &&
                                            $row['material_deleted_starts_by'] !== ':: CTH:' &&
                                            $row['material_deleted_starts_by'] !== ':: VTH:') {

                                            // Check if same parent kmat exists multiple times
                                            $parentKmats = [];
                                            foreach ($nachbauData as $nachbauRow) {
                                                if ($nachbauRow['typical_no'] == $typicalNo &&
                                                    $nachbauRow['ortz_kz'] == $panelData['ortz_kz'] &&
                                                    $nachbauRow['panel_no'] == $panelData['panel_no'] &&
                                                    str_contains($nachbauRow['kmat'], $row['material_deleted_number'])) {

                                                    $cleanParentKmat = preg_replace('/^00/', '', $nachbauRow['parent_kmat']);
                                                    $parentKmats[] = $cleanParentKmat;
                                                }
                                            }

                                            if (empty($parentKmats)) {
                                                $parentKmats = [$row['common_kmats']];
                                            }
                                        }

                                        foreach ($parentKmats as $parentKmat) {
                                            $skippedWorks[] = [
                                                'project_number' => $row['project_number'],
                                                'selected_nachbau' => $selectedNachbau,
                                                'transfer_to_nachbau' => $transferToNachbau,
                                                'released_dto_type_id' => 1,
                                                'skip_reason' => 'Material not found in typical ' . $typicalNo . ' in target nachbau.',
                                                'dto_number' => $row['dto_number'],
                                                'dto_description' => $row['dto_description'],
                                                'operation' => $row['operation'],
                                                'typical_no' => $typicalNo,
                                                'ortz_kz' => $panelData['ortz_kz'],
                                                'panel_no' => $panelData['panel_no'],
                                                'material_added_starts_by' => $row['material_added_starts_by'],
                                                'material_added_number' => $row['material_added_number'],
                                                'material_added_description' => $row['material_added_description'],
                                                'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                                'material_deleted_number' => $row['material_deleted_number'],
                                                'material_deleted_description' => $row['material_deleted_description'],
                                                'work_center' => $row['work_center'],
                                                'work_content' => $row['work_content'],
                                                'parent_kmat' => $parentKmat,
                                                'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                                'unit' => $row['unit'],
                                                'error_message_id' => 5
                                            ];
                                            $skippedErrorMessageId = 5;
                                            $rowAddedToSkipped = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else if ($row['type'] === 'Panel') {
                        foreach ($allItemsToCheck as $ortzKz) {
                            // 1. Check if panel (ortz_kz) exists in target nachbau
                            if (!in_array($ortzKz, $transferToNachbauPanels)) {
                                // Panel not found in target nachbau
                                // Find all panels with this ortz_kz in typicalToPanelsMap
                                foreach ($typicalToPanelsMap as $typical => $panels) {
                                    foreach ($panels as $panelData) {
                                        if ($panelData['ortz_kz'] === $ortzKz) {
                                            // Add each panel to skippedWorks
                                            $skippedWorks[] = [
                                                'project_number' => $row['project_number'],
                                                'selected_nachbau' => $selectedNachbau,
                                                'transfer_to_nachbau' => $transferToNachbau,
                                                'released_dto_type_id' => 1,
                                                'skip_reason' => 'Panel ' . $ortzKz . ' not exists in target nachbau.',
                                                'dto_number' => $row['dto_number'],
                                                'dto_description' => $row['dto_description'],
                                                'operation' => $row['operation'],
                                                'typical_no' => $typical,
                                                'ortz_kz' => $ortzKz,
                                                'panel_no' => $panelData['panel_no'],
                                                'material_added_starts_by' => $row['material_added_starts_by'],
                                                'material_added_number' => $row['material_added_number'],
                                                'material_added_description' => $row['material_added_description'],
                                                'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                                'material_deleted_number' => $row['material_deleted_number'],
                                                'material_deleted_description' => $row['material_deleted_description'],
                                                'work_center' => $row['work_center'],
                                                'work_content' => $row['work_content'],
                                                'parent_kmat' => $row['common_kmats'],
                                                'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                                'unit' => $row['unit'],
                                                'error_message_id' => 12
                                            ];
                                            $skippedErrorMessageId = 12;
                                            $rowAddedToSkipped = true;
                                        }
                                    }
                                }
                                continue; // Skip to next ortzKz
                            }

                            // 2. Panel exists, check if material exists in this panel
                            $materialFoundInThisPanel = false;

                            foreach ($nachbauMaterialData as $key => $exists) {
                                // Key format: material_typical_ortz
                                if (str_ends_with($key, '_' . $ortzKz) && str_starts_with($key, $row['material_deleted_number'] . '_')) {
                                    $materialFoundInThisPanel = true;
                                    break;
                                }
                            }

                            if (!$materialFoundInThisPanel) {
                                // Material not found in panel - add all panels with this ortz_kz to skippedWorks
                                foreach ($typicalToPanelsMap as $typical => $panels) {
                                    foreach ($panels as $panelData) {
                                        if ($panelData['ortz_kz'] === $ortzKz) {
                                            $parentKmats = [$row['common_kmats']];

                                            if ($row['material_deleted_starts_by'] !== ':: CTH:' && $row['material_deleted_starts_by'] !== ':: VTH:') {
                                                $parentKmats = [];
                                                foreach ($nachbauData as $nachbauRow) {
                                                    if ($nachbauRow['typical_no'] == $typical &&
                                                        $nachbauRow['ortz_kz'] == $ortzKz &&
                                                        $nachbauRow['panel_no'] == $panelData['panel_no'] &&
                                                        str_contains($nachbauRow['kmat'], $row['material_deleted_number']) &&
                                                        str_contains($nachbauRow['parent_kmat'], $row['common_kmats'])) {

                                                        $cleanParentKmat = preg_replace('/^00/', '', $nachbauRow['parent_kmat']);
                                                        $parentKmats[] = $cleanParentKmat;
                                                    }
                                                }

                                                if (empty($parentKmats)) {
                                                    $parentKmats = [$row['common_kmats']];
                                                }
                                            }

                                            foreach ($parentKmats as $parentKmat) {
                                                $skippedWorks[] = [
                                                    'project_number' => $row['project_number'],
                                                    'selected_nachbau' => $selectedNachbau,
                                                    'transfer_to_nachbau' => $transferToNachbau,
                                                    'released_dto_type_id' => 1,
                                                    'skip_reason' => 'Material not found in panel ' . $ortzKz . ' in target nachbau.',
                                                    'dto_number' => $row['dto_number'],
                                                    'dto_description' => $row['dto_description'],
                                                    'operation' => $row['operation'],
                                                    'typical_no' => $typical,
                                                    'ortz_kz' => $ortzKz,
                                                    'panel_no' => $panelData['panel_no'],
                                                    'material_added_starts_by' => $row['material_added_starts_by'],
                                                    'material_added_number' => $row['material_added_number'],
                                                    'material_added_description' => $row['material_added_description'],
                                                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                                    'material_deleted_number' => $row['material_deleted_number'],
                                                    'material_deleted_description' => $row['material_deleted_description'],
                                                    'work_center' => $row['work_center'],
                                                    'work_content' => $row['work_content'],
                                                    'parent_kmat' => $parentKmat,
                                                    'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                                    'unit' => $row['unit'],
                                                    'error_message_id' => 5
                                                ];
                                                $skippedErrorMessageId = 5;
                                                $rowAddedToSkipped = true;
                                            }
                                        }
                                    }
                                }
                                continue;
                            }
                        }
                    }
                }
                else {
                    // In else condition which is operation = 'add', i need to check if the material's parent kmat($row['common_kmats']) exists in nachbau data.
                    // If it exists not gonna insert the row into skippedWorks. If it is not exists, please insert the row into skippedWorks.
                    // But before inserting into skippedWorks, need to reach typical and ortz_kz values. So make if $row['type'] is 'Typical', insert to skippedRows as much as panel_no of typical from $typicalToPanelsMap.

                    if ($row['type'] === 'Typical' || $row['type'] === 'Accessories') {
                        // For each typical in release_items/accessory_release_items
                        foreach ($allItemsToCheck as $typicalNo) {
                            // 1. Check if typical exists in target nachbau
                            if (!in_array($typicalNo, $transferToNachbauTypicals)) {
                                // Typical not found - add all panels to skippedWorks
                                if (isset($typicalToPanelsMap[$typicalNo])) {
                                    foreach ($typicalToPanelsMap[$typicalNo] as $panelData) {
                                        $skippedWorks[] = [
                                            'project_number' => $row['project_number'],
                                            'selected_nachbau' => $selectedNachbau,
                                            'transfer_to_nachbau' => $transferToNachbau,
                                            'released_dto_type_id' => 1,
                                            'skip_reason' => 'Typical ' . $typicalNo . ' not exists in target nachbau.',
                                            'dto_number' => $row['dto_number'],
                                            'dto_description' => $row['dto_description'],
                                            'operation' => $row['operation'],
                                            'typical_no' => $typicalNo,
                                            'ortz_kz' => $panelData['ortz_kz'],
                                            'panel_no' => $panelData['panel_no'],
                                            'material_added_starts_by' => $row['material_added_starts_by'],
                                            'material_added_number' => $row['material_added_number'],
                                            'material_added_description' => $row['material_added_description'],
                                            'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                            'material_deleted_number' => $row['material_deleted_number'],
                                            'material_deleted_description' => $row['material_deleted_description'],
                                            'work_center' => $row['work_center'],
                                            'work_content' => $row['work_content'],
                                            'parent_kmat' => $row['common_kmats'],
                                            'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                            'unit' => $row['unit'],
                                            'error_message_id'=> 11
                                        ];
                                        $skippedErrorMessageId = 11;
                                        $rowAddedToSkipped = true;

                                    }
                                }
                                continue;
                            }

                            // 2. Typical and panels exist, now check if parent kmat exists
                            $parentKmatFoundInTypical = false;

                            foreach ($nachbauData as $nachbauRow) {
                                if ($nachbauRow['typical_no'] === $typicalNo) {
                                    $cleanParentKmat = preg_replace('/^00/', '', $nachbauRow['parent_kmat']);
                                    if ($cleanParentKmat === $row['common_kmats']) {
                                        $parentKmatFoundInTypical = true;
                                        break;
                                    }
                                }
                            }

                            // If parent kmat not found, add to skippedWorks for each panel in this typical
                            if (!$parentKmatFoundInTypical) {
                                if (isset($typicalToPanelsMap[$typicalNo])) {
                                    foreach ($typicalToPanelsMap[$typicalNo] as $panelData) {
                                        $skippedWorks[] = [
                                            'project_number' => $row['project_number'],
                                            'selected_nachbau' => $selectedNachbau,
                                            'transfer_to_nachbau' => $transferToNachbau,
                                            'released_dto_type_id' => 1,
                                            'skip_reason' => 'Parent kmat (' . $row['common_kmats'] . ') not found in typical ' . $typicalNo . ' in target nachbau.',
                                            'dto_number' => $row['dto_number'],
                                            'dto_description' => $row['dto_description'],
                                            'operation' => $row['operation'],
                                            'typical_no' => $typicalNo,
                                            'ortz_kz' => $panelData['ortz_kz'],
                                            'panel_no' => $panelData['panel_no'],
                                            'material_added_starts_by' => $row['material_added_starts_by'],
                                            'material_added_number' => $row['material_added_number'],
                                            'material_added_description' => $row['material_added_description'],
                                            'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                            'material_deleted_number' => $row['material_deleted_number'],
                                            'material_deleted_description' => $row['material_deleted_description'],
                                            'work_center' => $row['work_center'],
                                            'work_content' => $row['work_content'],
                                            'parent_kmat' => $row['common_kmats'],
                                            'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                            'unit' => $row['unit'],
                                            'error_message_id'=> 3
                                        ];
                                        $skippedErrorMessageId = 3;
                                        $rowAddedToSkipped = true;

                                    }
                                }
                            }
                        }
                    }
                    else if ($row['type'] === 'Panel') {
                        foreach ($allItemsToCheck as $ortzKz) {
                            // 1. Check if panel (ortz_kz) exists in target nachbau
                            if (!in_array($ortzKz, $transferToNachbauPanels)) {
                                // Panel not found in target nachbau
                                // Find all panels with this ortz_kz in typicalToPanelsMap
                                foreach ($typicalToPanelsMap as $typical => $panels) {
                                    foreach ($panels as $panelData) {
                                        if ($panelData['ortz_kz'] === $ortzKz) {
                                            // Add each panel to skippedWorks
                                            $skippedWorks[] = [
                                                'project_number' => $row['project_number'],
                                                'selected_nachbau' => $selectedNachbau,
                                                'transfer_to_nachbau' => $transferToNachbau,
                                                'released_dto_type_id' => 1,
                                                'skip_reason' => 'Panel ' . $ortzKz . ' not exists in target nachbau.',
                                                'dto_number' => $row['dto_number'],
                                                'dto_description' => $row['dto_description'],
                                                'operation' => $row['operation'],
                                                'typical_no' => $typical,
                                                'ortz_kz' => $ortzKz,
                                                'panel_no' => $panelData['panel_no'],
                                                'material_added_starts_by' => $row['material_added_starts_by'],
                                                'material_added_number' => $row['material_added_number'],
                                                'material_added_description' => $row['material_added_description'],
                                                'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                                'material_deleted_number' => $row['material_deleted_number'],
                                                'material_deleted_description' => $row['material_deleted_description'],
                                                'work_center' => $row['work_center'],
                                                'work_content' => $row['work_content'],
                                                'parent_kmat' => $row['common_kmats'],
                                                'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                                'unit' => $row['unit'],
                                                'error_message_id' => 12
                                            ];

                                            $skippedErrorMessageId = 12;
                                            $rowAddedToSkipped = true;
                                        }
                                    }
                                }

                                continue; // Skip to next ortzKz
                            }

                            // 2. Panel exists, now check if parent kmat exists
                            $parentKmatFoundInPanel = false;

                            foreach ($nachbauData as $nachbauRow) {
                                if ($nachbauRow['ortz_kz'] === $ortzKz) {
                                    $cleanParentKmat = preg_replace('/^00/', '', $nachbauRow['parent_kmat']);
                                    if ($cleanParentKmat === $row['common_kmats']) {
                                        $parentKmatFoundInPanel = true;
                                        break;
                                    }
                                }
                            }

                            // If parent kmat not found, add to skippedWorks for each panel with this ortz_kz
                            if (!$parentKmatFoundInPanel) {
                                foreach ($typicalToPanelsMap as $typical => $panels) {
                                    foreach ($panels as $panelData) {
                                        if ($panelData['ortz_kz'] === $ortzKz) {
                                            $skippedWorks[] = [
                                                'project_number' => $row['project_number'],
                                                'selected_nachbau' => $selectedNachbau,
                                                'transfer_to_nachbau' => $transferToNachbau,
                                                'released_dto_type_id' => 1,
                                                'skip_reason' => 'Parent kmat not exists in target nachbau.',
                                                'dto_number' => $row['dto_number'],
                                                'dto_description' => $row['dto_description'],
                                                'operation' => $row['operation'],
                                                'typical_no' => $typical,
                                                'ortz_kz' => $ortzKz,
                                                'panel_no' => $panelData['panel_no'],
                                                'material_added_starts_by' => $row['material_added_starts_by'],
                                                'material_added_number' => $row['material_added_number'],
                                                'material_added_description' => $row['material_added_description'],
                                                'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                                'material_deleted_number' => $row['material_deleted_number'],
                                                'material_deleted_description' => $row['material_deleted_description'],
                                                'work_center' => $row['work_center'],
                                                'work_content' => $row['work_content'],
                                                'parent_kmat' => $row['common_kmats'],
                                                'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                                'unit' => $row['unit'],
                                                'error_message_id' => 3
                                            ];
                                            $skippedErrorMessageId = 3;
                                            $rowAddedToSkipped = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!$rowAddedToSkipped) {
                // ========== SKIPPED DEĞİLSE: NORMAL AKIŞ ==========

                // Hedef nachbau'daki malzeme durumunu kontrol et
                $nachbauResultItems = $this->checkMaterialStatusInNachbau($projectNo, $transferToNachbau, $row, $accessoryTypicalNumber, $accessoryParentKmat);

                if (intval($row['error_message_id']) === 0 && intval($nachbauResultItems['error_message_id']) !== 0) {
                    if ($row['type'] === 'Typical' || $row['type'] === 'Accessories') {
                        // For Typical type, iterate through each typical and its panels
                        foreach ($allItemsToCheck as $typicalNo) {
                            if (isset($typicalToPanelsMap[$typicalNo])) {
                                foreach ($typicalToPanelsMap[$typicalNo] as $panelData) {
                                    $skippedWorks[] = [
                                        'project_number' => $row['project_number'],
                                        'selected_nachbau' => $selectedNachbau,
                                        'transfer_to_nachbau' => $transferToNachbau,
                                        'released_dto_type_id' => 1,
                                        'skip_reason' => 'Material not exists in ' . $row['work_content'] . ' (' . $row['work_center'] . ')',
                                        'dto_number' => $row['dto_number'],
                                        'dto_description' => $row['dto_description'],
                                        'operation' => $row['operation'],
                                        'typical_no' => $typicalNo,
                                        'ortz_kz' => $panelData['ortz_kz'],
                                        'panel_no' => $panelData['panel_no'],
                                        'material_added_starts_by' => $row['material_added_starts_by'],
                                        'material_added_number' => $row['material_added_number'],
                                        'material_added_description' => $row['material_added_description'],
                                        'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                        'material_deleted_number' => $row['material_deleted_number'],
                                        'material_deleted_description' => $row['material_deleted_description'],
                                        'work_center' => $row['work_center'],
                                        'work_content' => $row['work_content'],
                                        'parent_kmat' => $row['common_kmats'],
                                        'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                        'unit' => $row['unit']
                                    ];
                                    $skippedErrorMessageId = $nachbauResultItems['error_message_id'];
                                    $rowAddedToSkipped = true;
                                }
                            }
                        }
                    }
                    else if ($row['type'] === 'Panel') {
                        // For Panel type, iterate through all typicals and filter by ortz_kz
                        foreach ($allItemsToCheck as $ortzKz) {
                            foreach ($typicalToPanelsMap as $typical => $panels) {
                                foreach ($panels as $panelData) {
                                    if ($panelData['ortz_kz'] === $ortzKz) {
                                        $skippedWorks[] = [
                                            'project_number' => $row['project_number'],
                                            'selected_nachbau' => $selectedNachbau,
                                            'transfer_to_nachbau' => $transferToNachbau,
                                            'released_dto_type_id' => 1,
                                            'skip_reason' => 'Material not exists in ' . $row['work_content'] . ' (' . $row['work_center'] . ')',
                                            'dto_number' => $row['dto_number'],
                                            'dto_description' => $row['dto_description'],
                                            'operation' => $row['operation'],
                                            'typical_no' => $typical,
                                            'ortz_kz' => $ortzKz,
                                            'panel_no' => $panelData['panel_no'],
                                            'material_added_starts_by' => $row['material_added_starts_by'],
                                            'material_added_number' => $row['material_added_number'],
                                            'material_added_description' => $row['material_added_description'],
                                            'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                                            'material_deleted_number' => $row['material_deleted_number'],
                                            'material_deleted_description' => $row['material_deleted_description'],
                                            'work_center' => $row['work_center'],
                                            'work_content' => $row['work_content'],
                                            'parent_kmat' => $row['common_kmats'],
                                            'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                            'unit' => $row['unit']
                                        ];
                                        $skippedErrorMessageId = $nachbauResultItems['error_message_id'];
                                        $rowAddedToSkipped = true;
                                    }
                                }
                            }
                        }
                    }
                }

                // Release itemları tipe göre ayarla (Typical/Accessories/Panel)
                $selectedNachbauReleaseItems = explode('|', $row['release_items']) ?? [];
                $selectedNachbauAccessoryReleaseItems = explode('|', $row['accessory_release_items']) ?? [];

                if ($row['type'] === 'Typical' || $row['type'] === 'Accessories') {
                    // Tipiklere göre filtrele: sadece transfer-to nachbau'da bulunan itemları tut
                    $transferToNachbauAllDtoTypicals = explode('|', $nachbauResultItems['nachbau_typicals']);
                    $transferToNachbauAllDtoTypicals[] = $accessoryTypicalNumber;

                    $selectedNachbauReleaseItems = array_intersect($selectedNachbauReleaseItems, $transferToNachbauAllDtoTypicals);
                    $selectedNachbauAccessoryReleaseItems = array_intersect($selectedNachbauAccessoryReleaseItems, $transferToNachbauAllDtoTypicals);

                } else if ($row['type'] === 'Panel') {
                    // Panolara göre filtrele: sadece transfer-to nachbau'da bulunan panoları tut
                    $transferToNachbauAlDtoPanels = explode('|', $nachbauResultItems['nachbau_panels']);
                    $transferToNachbauAlDtoPanels[] = $accessoryTypicalNumber;

                    $selectedNachbauReleaseItems = array_intersect($selectedNachbauReleaseItems, $transferToNachbauAlDtoPanels);
                    $selectedNachbauAccessoryReleaseItems = array_intersect($selectedNachbauAccessoryReleaseItems, $transferToNachbauAlDtoPanels);
                }

            } else {
                // ========== SKIPPED İSE: MİNİMAL İŞLEM ==========

                // checkMaterialStatusInNachbau çağrısını yap (sonraki kodlarda kullanılacak)
                $nachbauResultItems = $this->checkMaterialStatusInNachbau($projectNo, $transferToNachbau, $row, $accessoryTypicalNumber, $accessoryParentKmat);

                // Release items'ı boş bırak (NULL olarak eklenecek)
                $selectedNachbauReleaseItems = [];
                $selectedNachbauAccessoryReleaseItems = [];
            }

            // Parametre keylerini oluştur
            $keys = $this->buildProjectWorkParameterKeys($index);

            // TK Form henüz oluşturulmamışsa (hata 8 veya 9), NULL değerler kullan
            if ($row['error_message_id'] === '8' || $row['error_message_id'] === '9') {
                $projectWorks[] = [
                    $keys['project_number'] => $row['project_number'],
                    $keys['nachbau_number'] => $transferToNachbau,
                    $keys['nachbau_dto_number'] => $row['nachbau_dto_number'],
                    $keys['dto_number'] => $row['dto_number'],
                    $keys['is_dto_deleted'] => $row['is_dto_deleted'],
                    $keys['product_id'] => $row['product_id'],
                    $keys['tkform_id'] => NULL,
                    $keys['tkform_materials_id'] => NULL,
                    $keys['work_center_id'] => NULL,
                    $keys['release_status'] => NULL,
                    $keys['release_items'] => NULL,
                    $keys['accessory_release_items'] => NULL,
                    $keys['accessory_release_type'] => NULL,
                    $keys['last_updated_by'] => SharedManager::$fullname,
                    $keys['error_message_id'] => $row['error_message_id'],
                    $keys['tk_kmats'] => NULL,
                    $keys['common_kmats'] => NULL,
                    $keys['nachbau_kmats'] => NULL,
                    $keys['nachbau_panels'] => NULL,
                    $keys['nachbau_typicals'] => NULL,
                    $keys['added_on_work_center_id'] => NULL
                ];
            } else if ($rowAddedToSkipped) {
                // ========== YENİ: SKIPPED OLAN SATIRLAR ==========
                $projectWorks[] = [
                    $keys['project_number'] => $row['project_number'],
                    $keys['nachbau_number'] => $transferToNachbau,
                    $keys['nachbau_dto_number'] => $row['nachbau_dto_number'],
                    $keys['dto_number'] => $row['dto_number'],
                    $keys['is_dto_deleted'] => $row['is_dto_deleted'],
                    $keys['product_id'] => $row['product_id'] ?? $productId,
                    $keys['tkform_id'] => $row['tkform_id'] ?? NULL,
                    $keys['tkform_materials_id'] => $row['tkform_materials_id'] ?? NULL,
                    $keys['work_center_id'] => $row['tk_work_center_id'] ?? NULL,
                    $keys['release_status'] => 'initial',
                    $keys['release_items'] => NULL,  // ← Skipped için NULL
                    $keys['accessory_release_items'] => NULL,  // ← Skipped için NULL
                    $keys['accessory_release_type'] => $row['accessory_release_type'],
                    $keys['last_updated_by'] => SharedManager::$fullname,
                    $keys['error_message_id'] => $skippedErrorMessageId,  // ← Skipped error ID kullan
                    $keys['tk_kmats'] => $row['tk_kmats'],
                    $keys['common_kmats'] => $nachbauResultItems['common_kmats'],
                    $keys['nachbau_kmats'] => $nachbauResultItems['nachbau_kmats'],
                    $keys['nachbau_panels'] => $nachbauResultItems['nachbau_panels'],
                    $keys['nachbau_typicals'] => $nachbauResultItems['nachbau_typicals'],
                    $keys['added_on_work_center_id'] => (intval($row['added_on_work_center_id']) === 0 ? NULL : $row['added_on_work_center_id']) ?? NULL
                ];
            } else {
                // Normal durum - tüm verilerle
                $releaseItems = implode('|', $selectedNachbauReleaseItems);
                $accessoryReleaseItems = implode('|', $selectedNachbauAccessoryReleaseItems);
                $hasReleaseItems = !empty($selectedNachbauReleaseItems) || !empty($selectedNachbauAccessoryReleaseItems);
                $hasNoError = intval($nachbauResultItems['error_message_id']) === 0;

                $projectWorks[] = [
                    $keys['project_number'] => $row['project_number'],
                    $keys['nachbau_number'] => $transferToNachbau,
                    $keys['nachbau_dto_number'] => $row['nachbau_dto_number'],
                    $keys['dto_number'] => $row['dto_number'],
                    $keys['is_dto_deleted'] => $row['is_dto_deleted'],
                    $keys['product_id'] => $row['product_id'] ?? NULL,
                    $keys['tkform_id'] => $row['tkform_id'] ?? NULL,
                    $keys['tkform_materials_id'] => $row['tkform_materials_id'] ?? NULL,
                    $keys['work_center_id'] => $row['tk_work_center_id'] ?? NULL,
                    $keys['release_status'] => $hasNoError && $hasReleaseItems ? 'to be released' : 'initial',
                    $keys['release_items'] => $hasNoError ? $releaseItems : '',
                    $keys['accessory_release_items'] => $hasNoError ? $accessoryReleaseItems : '',
                    $keys['accessory_release_type'] => $row['accessory_release_type'],
                    $keys['last_updated_by'] => SharedManager::$fullname,
                    $keys['error_message_id'] => $nachbauResultItems['error_message_id'],
                    $keys['tk_kmats'] => $row['tk_kmats'],
                    $keys['common_kmats'] => $nachbauResultItems['common_kmats'],
                    $keys['nachbau_kmats'] => $nachbauResultItems['nachbau_kmats'],
                    $keys['nachbau_panels'] => $nachbauResultItems['nachbau_panels'],
                    $keys['nachbau_typicals'] => $nachbauResultItems['nachbau_typicals'],
                    $keys['added_on_work_center_id'] => (intval($row['added_on_work_center_id']) === 0 ? NULL : $row['added_on_work_center_id']) ?? NULL
                ];
            }

            $index++;
        }

        // ============================================
        // BÖLÜM 2: Transfer-to nachbau'daki ekstra DTO'ları ekle
        // ============================================
        if (!empty($transferToNachbauExtraDtos)) {
            // DTO numaralarını formatla
            $dtoNumbers = [];
            $nachbauDtoMap = [];
            foreach ($transferToNachbauExtraDtos as $dto) {
                if (str_contains($dto, 'KUKO_CON_CST')) {
                    $nachbauDtoMap[$this->formatKukoDtoNumber($dto)] = $dto;
                    $dtoNumbers[] = $this->formatKukoDtoNumber($dto);
                } else {
                    $nachbauDtoMap[$this->formatDtoNumber($dto)] = $dto;
                    $dtoNumbers[] = $this->formatDtoNumber($dto);
                }
            }

            foreach($dtoNumbers as $dtoNumber) {
                $nachbauDtoNumber = $nachbauDtoMap[$dtoNumber];
                $query = "INSERT INTO project_works(project_number, nachbau_number, nachbau_dto_number, dto_number, product_id, last_updated_by, error_message_id)";
                $pars[] = [$projectNo, $transferToNachbau, $nachbauDtoNumber, $dtoNumber, $productId, SharedManager::$fullname, 9];
            }
            DbManager::fetchInsert('dto_configurator', $query, $pars);


            // Ekstra DTO'lar için TK formları getir
            $placeholders = array_fill(0, count($dtoNumbers), '?');
            $placeholdersString = implode(',', $placeholders);
            $query = "SELECT id FROM tkforms WHERE dto_number IN ($placeholdersString) AND deleted IS NULL";
            $tkforms = DbManager::fetchPDOQueryData('dto_configurator', $query, $dtoNumbers)['data'] ?? [];

            if (!empty($tkforms)) {
                $tkformsIds = array_column($tkforms, 'id');

                // TK form malzemelerini getir
                $query = "SELECT id, tkform_id, dto_number, material_added_number, material_added_work_center_id, tk_kmats,
                      material_deleted_number, material_deleted_work_center_id, tk_work_center_id, operation, effective
                      FROM tkform_materials_view
                      WHERE tkform_id IN (:tkformsIds)";
                $tkform_materials = DbManager::fetchPDOQueryData('dto_configurator', $query, [':tkformsIds' => $tkformsIds])['data'] ?? [];

                if (!empty($tkform_materials)) {
                    foreach ($tkform_materials as $row) {
                        $nachbauDtoNumber = $this->getNachbauDtoNumberForProjectWork($projectNo, $transferToNachbau, $row['dto_number']);
                        $nachbauResultItems = $this->checkMaterialStatusInNachbau($projectNo, $transferToNachbau, $row, $accessoryTypicalNumber, $accessoryParentKmat);

                        // Parametre keylerini oluştur
                        $keys = $this->buildProjectWorkParameterKeys($index);

                        $projectWorks[] = [
                            $keys['project_number'] => $projectNo,
                            $keys['nachbau_number'] => $transferToNachbau,
                            $keys['nachbau_dto_number'] => $nachbauDtoNumber,
                            $keys['dto_number'] => $row['dto_number'],
                            $keys['is_dto_deleted'] => 0,
                            $keys['product_id'] => $productId,
                            $keys['tkform_id'] => intval($row['tkform_id']),
                            $keys['tkform_materials_id'] => intval($row['id']),
                            $keys['work_center_id'] => intval($row['tk_work_center_id']),
                            $keys['release_status'] => 'initial',
                            $keys['release_items'] => null,
                            $keys['accessory_release_items'] => null,
                            $keys['accessory_release_type'] => 0,
                            $keys['last_updated_by'] => SharedManager::$fullname,
                            $keys['error_message_id'] => intval($nachbauResultItems['error_message_id']),
                            $keys['tk_kmats'] => $row['tk_kmats'],
                            $keys['common_kmats'] => $nachbauResultItems['common_kmats'],
                            $keys['nachbau_kmats'] => $nachbauResultItems['nachbau_kmats'],
                            $keys['nachbau_panels'] => $nachbauResultItems['nachbau_panels'],
                            $keys['nachbau_typicals'] => $nachbauResultItems['nachbau_typicals'],
                            $keys['added_on_work_center_id'] => null
                        ];

                        $index++;
                    }
                }
            }
        }

        // ============================================
        // Tüm project workleri veritabanına kaydet
        // ============================================
        if (!empty($projectWorks)) {
            $insert_query = "INSERT INTO project_works(project_number, nachbau_number, nachbau_dto_number, dto_number, is_dto_deleted, product_id, tkform_id, tkform_materials_id, work_center_id, release_status,
                    release_items, accessory_release_items, accessory_release_type, last_updated_by, error_message_id, tk_kmats, common_kmats, nachbau_kmats, nachbau_panels, nachbau_typicals, added_on_work_center_id) VALUES ";

            foreach ($projectWorks as $work) {
                $keys = array_keys($work);
                $insert_query .= '(' . implode(', ', $keys) . '),';
            }

            // Son virgülü kaldır
            $insert_query = rtrim($insert_query, ',');

            // Tüm project workleri tek bir array'e düzleştir
            $allParameters = [];
            foreach ($projectWorks as $work) {
                $allParameters = $allParameters + $work;
            }

            DbManager::fetchPDOQuery('dto_configurator', $insert_query, $allParameters);
        }

        SharedManager::saveLog('log_dtoconfigurator', "CREATED | Project Works are transferred. | " . implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Project Works are transferred. | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_OPERATIONS, ACTION_CREATED, implode(' | ', $_POST), "Nachbau Transfer");

        return $skippedWorks;
    }

    private function buildProjectWorkParameterKeys($index): array {
        return [
            'project_number' => ":project_number$index",
            'nachbau_number' => ":nachbau_number$index",
            'nachbau_dto_number' => ":nachbau_dto_number$index",
            'dto_number' => ":dto_number$index",
            'is_dto_deleted' => ":is_dto_deleted$index",
            'product_id' => ":product_id$index",
            'tkform_id' => ":tkform_id$index",
            'tkform_materials_id' => ":tkform_materials_id$index",
            'work_center_id' => ":work_center_id$index",
            'release_status' => ":release_status$index",
            'release_items' => ":release_items$index",
            'accessory_release_items' => ":accessory_release_items$index",
            'accessory_release_type' => ":accessory_release_type$index",
            'last_updated_by' => ":last_updated_by$index",
            'error_message_id' => ":error_message_id$index",
            'tk_kmats' => ":tk_kmats$index",
            'common_kmats' => ":common_kmats$index",
            'nachbau_kmats' => ":nachbau_kmats$index",
            'nachbau_panels' => ":nachbau_panels$index",
            'nachbau_typicals' => ":nachbau_typicals$index",
            'added_on_work_center_id' => ":added_on_work_center_id$index"
        ];
    }

    public function transferReleaseItemsToBomChange($lastProjectWorks, $accessoryParentKmat): void
    {
        $bomChangeArr = [];

        if (empty($lastProjectWorks))
            return;

        $insertToBomQuery = "INSERT INTO bom_change(project_work_id, release_item, release_quantity, parent_kmat, release_type, is_accessory, accessory_release_type, send_to_review_by, is_revision_change, publish_by, released_status, active) VALUES ";
        $index = 0;

        foreach ($lastProjectWorks as $row) {
            $lastPwReleaseItems = explode('|', $row['release_items']);
            $lastPwAccessoryReleaseItems = explode('|', $row['accessory_release_items']);

            if (!empty($lastPwReleaseItems))
            {
                foreach($lastPwReleaseItems as $releaseItem) {
                    if (!empty($releaseItem)) {
                        $projectWorkIdKey = ":project_work_id$index";
                        $releaseItemKey = ":release_item$index";
                        $releaseQuantityKey = ":release_quantity$index";
                        $parentKmatKey = ":parent_kmat$index";
                        $releaseTypeKey = ":release_type$index";
                        $isAccessoryKey = ":is_accessory$index";
                        $accessoryReleaseTypeKey = ":accessory_release_type$index";
                        $sendToReviewKey = ":send_to_review_by$index";
                        $isRevisionChangeKey = ":is_revision_change$index";
                        $publishByKey = ":publish_by$index";
                        $releasedStatusKey = ":released_status$index";
                        $activeKey = ":active$index";

                        $bomChangeArr[] = [
                            $projectWorkIdKey => $row['id'],
                            $releaseItemKey => $releaseItem,
                            $releaseQuantityKey => $row['quantity'],
                            $parentKmatKey => $row['common_kmats'],
                            $releaseTypeKey => $row['type'],
                            $isAccessoryKey => 0,
                            $accessoryReleaseTypeKey => 0,
                            $sendToReviewKey => SharedManager::$fullname,
                            $isRevisionChangeKey => $row['is_revision_change'],
                            $publishByKey => null,
                            $releasedStatusKey => 0,
                            $activeKey => 1
                        ];

                        $insertToBomQuery .= "($projectWorkIdKey, $releaseItemKey, $releaseQuantityKey, $parentKmatKey, $releaseTypeKey, $isAccessoryKey, $accessoryReleaseTypeKey, $sendToReviewKey, $isRevisionChangeKey, $publishByKey, $releasedStatusKey, $activeKey),";
                        $index++;
                    }
                }
            }

            if (!empty($lastPwAccessoryReleaseItems))
            {
                foreach($lastPwAccessoryReleaseItems as $accReleaseItem) {
                    if (!empty($accReleaseItem)) {
                        if (intval($row['accessory_release_type']) === 2)
                            $parentKmat = $accessoryParentKmat; // Aksesuar altına eklenmesi için eklenecek üst kmat aksesuar olmalı
                        else
                            $parentKmat = $row['common_kmats']; // Tipiğinin altında aksesuar olarak gönderilecekler için tipiğin kmatı olmalı

                        $projectWorkIdKey = ":project_work_id$index";
                        $releaseItemKey = ":release_item$index";
                        $releaseQuantityKey = ":release_quantity$index";
                        $parentKmatKey = ":parent_kmat$index";
                        $releaseTypeKey = ":release_type$index";
                        $isAccessoryKey = ":is_accessory$index";
                        $accessoryReleaseTypeKey = ":accessory_release_type$index";
                        $sendToReviewKey = ":send_to_review_by$index";
                        $publishByKey = ":publish_by$index";
                        $releasedStatusKey = ":released_status$index";
                        $activeKey = ":active$index";

                        $bomChangeArr[] = [
                            $projectWorkIdKey => $row['id'],
                            $releaseItemKey => $accReleaseItem,
                            $releaseQuantityKey => $row['quantity'],
                            $parentKmatKey => $parentKmat,
                            $releaseTypeKey => 'Accessories',
                            $isAccessoryKey => 1,
                            $accessoryReleaseTypeKey => $row['accessory_release_type'],
                            $sendToReviewKey => SharedManager::$fullname,
                            $publishByKey => null,
                            $releasedStatusKey => 0,
                            $activeKey => 1
                        ];

                        $insertToBomQuery .= "($projectWorkIdKey, $releaseItemKey, $releaseQuantityKey, $parentKmatKey, $releaseTypeKey, $isAccessoryKey, $accessoryReleaseTypeKey, $sendToReviewKey, $publishByKey, $releasedStatusKey, $activeKey),";
                        $index++;
                    }
                }
            }
        }

        if ($index > 0) {
            $insertToBomQuery = rtrim($insertToBomQuery, ',');
            DbManager::fetchPDOQuery('dto_configurator', $insertToBomQuery, $bomChangeArr, [], false);

            SharedManager::saveLog('log_dtoconfigurator', "CREATED | BOM Change Works are transferred. | " . implode(' | ', $_POST));
            Journals::saveJournal("CREATED | BOM Change Works are transferred. | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_OPERATIONS, ACTION_CREATED, implode(' | ', $_POST), "Nachbau Transfer");
        }
    }

    public function transferSpareDtoWorks($projectNo, $selectedNachbau, $transferToNachbau, $allTypicalsByDto, $selectedNachbauExtraDtosFlipped): array  {
        $skippedWorks = [];
        $spareWorksArray = [];

        // Seçili aktarımdaki spare çalışmalarını getir.
        $query = "SELECT * FROM project_works_spare WHERE project_number = :pNo AND nachbau_number = :selectedNachbau AND deleted IS NULL";
        $selectedNachbauSpareWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':selectedNachbau' => $selectedNachbau])['data'] ?? [];

        if (empty($selectedNachbauSpareWorks))
            return [];

        $index = 0;
        $insertToSpareWorksQuery = "INSERT INTO project_works_spare(project_number, nachbau_number, dto_number, dto_description, spare_dto_type, 
                accessory_typical_number, accessory_panel_no, accessory_parent_kmat, spare_typical_number, spare_parent_kmat, 
                material_added_starts_by, material_added_number, material_added_description, release_quantity, release_unit, is_revision_change, send_to_review_by, publish_by) VALUES ";

        foreach($selectedNachbauSpareWorks as $row) {
            $transferToNachbauDtoTypicals = $allTypicalsByDto[$row['dto_number']] ?? [];

            if (isset($selectedNachbauExtraDtosFlipped[$row['dto_number']])) {
                $skippedWorks[] = [
                    'project_number' => $row['project_number'],
                    'selected_nachbau' => $selectedNachbau,
                    'transfer_to_nachbau' => $transferToNachbau,
                    'released_dto_type_id' => 2,
                    'skip_reason' => 'DTO not found in target nachbau',
                    'dto_number' => $row['dto_number'],
                    'dto_description' => $row['dto_description'],
                    'operation' => 'add',
                    'typical_no' => $row['accessory_typical_number'],
                    'ortz_kz' => '',
                    'panel_no' => $row['accessory_panel_no'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_added_description' => $row['material_added_description'],
                    'material_deleted_number' => '',
                    'material_deleted_description' => '',
                    'work_center' => '',
                    'work_content' => '',
                    'parent_kmat' => $row['accessory_parent_kmat'],
                    'quantity' => preg_replace('/\.000$/', '', $row['release_quantity']),
                    'unit' => $row['release_unit']
                ];

                continue;
            }

            if (!in_array($row['spare_typical_number'], $transferToNachbauDtoTypicals)) {
                $skippedWorks[] = [
                    'project_number' => $row['project_number'],
                    'selected_nachbau' => $selectedNachbau,
                    'transfer_to_nachbau' => $transferToNachbau,
                    'released_dto_type_id' => 2,
                    'skip_reason' => 'DTO typical ' . $row['spare_typical_number'] . ' not found in target nachbau for this DTO',
                    'dto_number' => $row['dto_number'],
                    'dto_description' => $row['dto_description'],
                    'operation' => 'add',
                    'typical_no' => $row['accessory_typical_number'],
                    'ortz_kz' => '',
                    'panel_no' => $row['accessory_panel_no'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_added_description' => $row['material_added_description'],
                    'material_deleted_number' => '',
                    'material_deleted_description' => '',
                    'work_center' => '',
                    'work_content' => '',
                    'parent_kmat' => $row['accessory_parent_kmat'],
                    'quantity' => preg_replace('/\.000$/', '', $row['release_quantity']),
                    'unit' => $row['release_unit']
                ];

                continue;
            }

            $projectNoKey = ":project_number$index";
            $nachbauNoKey = ":nachbau_number$index";
            $dtoNumberKey = ":dto_number$index";
            $dtoDescriptionKey = ":dto_description$index";
            $spareDtoTypeKey = ":spare_dto_type$index";
            $accessoryTypicalNumberKey = ":accessory_typical_number$index";
            $accessoryPanelNoKey = ":accessory_panel_no$index";
            $accessoryParentKmatKey = ":accessory_parent_kmat$index";
            $spareTypicalNumberKey = ":spare_typical_number$index";
            $spareParentKmatKey = ":spare_parent_kmat$index";
            $materialAddedStartsByKey = ":material_added_starts_by$index";
            $materialNoKey = ":material_added_number$index";
            $materialDescriptionKey = ":material_added_description$index";
            $releaseQuantityKey = ":release_quantity$index";
            $releaseUnitKey = ":release_unit$index";
            $isRevisionChangeKey = ":is_revision_change$index";
            $sendToReviewKey = ":send_to_review_by$index";
            $publishByKey = ":publish_by$index";

            $spareWorksArray[] = [
                $projectNoKey => $row['project_number'],
                $nachbauNoKey => $transferToNachbau,
                $dtoNumberKey => $row['dto_number'],
                $dtoDescriptionKey => html_entity_decode($row['dto_description']),
                $spareDtoTypeKey => $row['spare_dto_type'],
                $accessoryTypicalNumberKey => $row['accessory_typical_number'],
                $accessoryPanelNoKey => $row['accessory_panel_no'],
                $accessoryParentKmatKey =>  $row['accessory_parent_kmat'],
                $spareTypicalNumberKey => $row['spare_typical_number'],
                $spareParentKmatKey => $row['spare_parent_kmat'],
                $materialAddedStartsByKey => $row['material_added_starts_by'],
                $materialNoKey => $row['material_added_number'],
                $materialDescriptionKey => $row['material_added_description'],
                $releaseQuantityKey => $row['release_quantity'],
                $releaseUnitKey => $row['release_unit'],
                $isRevisionChangeKey => $row['is_revision_change'],
                $sendToReviewKey => SharedManager::$fullname,
                $publishByKey => null,
            ];

            $insertToSpareWorksQuery .= "($projectNoKey, $nachbauNoKey, $dtoNumberKey, $dtoDescriptionKey, $spareDtoTypeKey, $accessoryTypicalNumberKey, $accessoryPanelNoKey, $accessoryParentKmatKey, 
                                        $spareTypicalNumberKey, $spareParentKmatKey, $materialAddedStartsByKey, $materialNoKey, $materialDescriptionKey, $releaseQuantityKey, $releaseUnitKey, $isRevisionChangeKey, $sendToReviewKey, $publishByKey),";
            $index++;
        }

        if ($index > 0) {
            $insertToSpareWorksQuery = rtrim($insertToSpareWorksQuery, ',');
            DbManager::fetchPDOQuery('dto_configurator', $insertToSpareWorksQuery, $spareWorksArray, [], false);

            SharedManager::saveLog('log_dtoconfigurator', "CREATED | Spare DTO Works are transferred. | " . implode(' | ', $_POST));
            Journals::saveJournal("CREATED | Spare DTO Works are transferred. | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_OPERATIONS, ACTION_CREATED, implode(' | ', $_POST), "Nachbau Transfer");
        }

        return $skippedWorks;
    }

    public function transferExtensionDtoChanges($projectNo, $selectedNachbau, $transferToNachbau, $allTypicalsByDto, $selectedNachbauExtraDtosFlipped): array
    {
        $extensionDtoWorksArray = [];
        $skippedWorks = [];

        // Seçili aktarımdaki extension dto çalışmalarını getir.
        $query = "SELECT * FROM project_works_extensions WHERE project_number = :pNo AND nachbau_number = :selectedNachbau AND deleted IS NULL";
        $selectedNachbauExtensionDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':selectedNachbau' => $selectedNachbau])['data'] ?? [];

        if (empty($selectedNachbauExtensionDtoWorks))
            return [];

        // OPTIMIZATION: Delete/Replace operasyonları için malzeme kontrolü
        $materialsToCheck = [];
        foreach ($selectedNachbauExtensionDtoWorks as $row) {
            if (($row['operation'] === 'delete' || $row['operation'] === 'replace') && !empty($row['material_deleted_number'])) {
                $materialsToCheck[] = $row['material_deleted_number'];
            }
        }

        // Tüm malzemelerin nachbau'daki varlığını tek sorguda kontrol et
        $nachbauMaterialData = [];
        if (!empty($materialsToCheck)) {
            $nachbauMaterialData = $this->checkMaterialsExistInNachbau($projectNo, $transferToNachbau, $materialsToCheck);
        }

        $index = 0;
        $insertToExtensionDtoWorksQuery = "INSERT INTO project_works_extensions(project_number, nachbau_number, dto_number, 
                    dto_description, dto_typical_no, product_id, material_added_starts_by, material_added_number, material_added_description, 
                    material_deleted_starts_by, material_deleted_number, material_deleted_description, operation, is_accessory, status, quantity, 
                    unit, note, extension_kmat_name, work_center_id, work_center, work_content, position, parent_kmat, sub_kmat, typical_no, ortz_kz, 
                    panel_no, is_revision_change, send_to_review_by, approved_by) VALUES ";

        foreach ($selectedNachbauExtensionDtoWorks as $row) {
            $transferToNachbauDtoTypicals = $allTypicalsByDto[$row['dto_number']] ?? [];

            // DTO veya DTO'nun geçtiği bir tipik yeni aktarımda yoksa atla
            // EXTENSION OZEL NOT: EXTENSION DTOSU ÇALIŞIRKEN DTONUN TİPİĞİ AKSESUARDA GİRİLMEYEBİLİR ANCAK AKSESUARA EKLENEBİLİYOR. BUNA DİKKAT EDİLMELİ.
            if (isset($selectedNachbauExtraDtosFlipped[$row['dto_number']])) {
                $skippedWorks[] = [
                    'project_number' => $row['project_number'],
                    'selected_nachbau' => $selectedNachbau,
                    'transfer_to_nachbau' => $transferToNachbau,
                    'released_dto_type_id' => 3,
                    'skip_reason' => 'DTO not found in target nachbau',
                    'dto_number' => $row['dto_number'],
                    'dto_description' => $row['dto_description'],
                    'operation' => $row['operation'],
                    'typical_no' => $row['typical_no'],
                    'ortz_kz' => $row['ortz_kz'],
                    'panel_no' => $row['panel_no'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_added_description' => $row['material_added_description'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number'],
                    'material_deleted_description' => $row['material_deleted_description'],
                    'work_center' => $row['work_center'],
                    'work_content' => $row['extension_kmat_name'],
                    'parent_kmat' => $row['parent_kmat'],
                    'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                    'unit' => $row['unit'],
                    'is_revision_change' => $row['is_revision_change']
                ];

                continue;
            }

            if (!in_array($row['typical_no'], $transferToNachbauDtoTypicals) && intval($row['is_accessory']) === 0) {
                $skippedWorks[] = [
                    'project_number' => $row['project_number'],
                    'selected_nachbau' => $selectedNachbau,
                    'transfer_to_nachbau' => $transferToNachbau,
                    'released_dto_type_id' => 3,
                    'skip_reason' =>  $row['typical_no'] . ' typical is not found in target nachbau',
                    'dto_number' => $row['dto_number'],
                    'dto_description' => $row['dto_description'],
                    'operation' => $row['operation'],
                    'typical_no' => $row['typical_no'],
                    'ortz_kz' => $row['ortz_kz'],
                    'panel_no' => $row['panel_no'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_added_description' => $row['material_added_description'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number'],
                    'material_deleted_description' => $row['material_deleted_description'],
                    'work_center' => $row['work_center'],
                    'work_content' => $row['extension_kmat_name'],
                    'parent_kmat' => $row['parent_kmat'],
                    'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                    'unit' => $row['unit'],
                    'is_revision_change' => $row['is_revision_change']
                ];

                continue;
            }

            // Delete/Replace ise malzeme kontrolü
            if ($row['operation'] === 'delete' || $row['operation'] === 'replace') {
                $materialKey = $row['material_deleted_number'] . '_' . $row['typical_no'] . '_' . $row['ortz_kz'];

                if (!isset($nachbauMaterialData[$materialKey])) {
                    $skippedWorks[] = [
                        'project_number' => $row['project_number'],
                        'selected_nachbau' => $selectedNachbau,
                        'transfer_to_nachbau' => $transferToNachbau,
                        'released_dto_type_id' => 3,
                        'skip_reason' => 'Material not found in target nachbau in this typical/panel',
                        'dto_number' => $row['dto_number'],
                        'dto_description' => $row['dto_description'],
                        'operation' => $row['operation'],
                        'typical_no' => $row['typical_no'],
                        'ortz_kz' => $row['ortz_kz'],
                        'panel_no' => $row['panel_no'],
                        'material_added_starts_by' => $row['material_added_starts_by'],
                        'material_added_number' => $row['material_added_number'],
                        'material_added_description' => $row['material_added_description'],
                        'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                        'material_deleted_number' => $row['material_deleted_number'],
                        'material_deleted_description' => $row['material_deleted_description'],
                        'work_center' => $row['work_center'],
                        'work_content' => $row['extension_kmat_name'],
                        'parent_kmat' => $row['parent_kmat'],
                        'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                        'unit' => $row['unit'],
                        'is_revision_change' => $row['is_revision_change']
                    ];

                    continue;
                }
            }

            $projectNumberKey = ":project_number$index";
            $nachbauNumberKey = ":nachbau_number$index";
            $dtoNumberKey = ":dto_number$index";
            $dtoDescriptionKey = ":dto_description$index";
            $dtoTypicalNoKey = ":dto_typical_no$index";
            $productIdKey = ":product_id$index";
            $materialAddedStartsByKey = ":material_added_starts_by$index";
            $materialAddedNoKey = ":material_added_number$index";
            $materialAddedDescriptionKey = ":material_added_description$index";
            $materialDeletedStartsByKey = ":material_deleted_starts_by$index";
            $materialDeletedNoKey = ":material_deleted_number$index";
            $materialDeletedDescriptionKey = ":material_deleted_description$index";
            $operationKey = ":operation$index";
            $isAccessoryKey = ":is_accessory$index";
            $statusKey = ":status$index";
            $quantityKey = ":quantity$index";
            $unitKey = ":unit$index";
            $noteKey = ":note$index";
            $extensionKmatNameKey = ":extension_kmat_name$index";
            $workCenterIdKey = ":work_center_id$index";
            $workCenterKey = ":work_center$index";
            $workContentKey = ":work_content$index";
            $positionKey = ":position$index";
            $parentKmatKey = ":parent_kmat$index";
            $subKmatKey = ":sub_kmat$index";
            $typicalNoKey = ":typical_no$index";
            $ortzKzKey = ":ortz_kz$index";
            $panelNoKey = ":panel_no$index";
            $isRevisionChangeKey = ":is_revision_change$index";
            $sendToReviewByKey = ":send_to_review_by$index";
            $approvedByKey = ":approved_by$index";

            $extensionDtoWorksArray[] = [
                $projectNumberKey => $row['project_number'],
                $nachbauNumberKey => $transferToNachbau,
                $dtoNumberKey => $row['dto_number'],
                $dtoDescriptionKey => html_entity_decode($row['dto_description']),
                $dtoTypicalNoKey => $row['dto_typical_no'],
                $productIdKey => $row['product_id'],
                $materialAddedStartsByKey => $row['material_added_starts_by'],
                $materialAddedNoKey => $row['material_added_number'],
                $materialAddedDescriptionKey => $row['material_added_description'],
                $materialDeletedStartsByKey => $row['material_deleted_starts_by'],
                $materialDeletedNoKey => $row['material_deleted_number'],
                $materialDeletedDescriptionKey => $row['material_deleted_description'],
                $operationKey => $row['operation'],
                $isAccessoryKey => $row['is_accessory'],
                $statusKey => $row['status'],
                $quantityKey => $row['quantity'],
                $unitKey => $row['unit'],
                $noteKey => $row['note'],
                $extensionKmatNameKey => $row['extension_kmat_name'],
                $workCenterIdKey => $row['work_center_id'],
                $workCenterKey => $row['work_center'],
                $workContentKey => $row['work_content'],
                $positionKey => $row['position'],
                $parentKmatKey => $row['parent_kmat'],
                $subKmatKey => $row['sub_kmat'],
                $typicalNoKey => $row['typical_no'],
                $ortzKzKey => $row['ortz_kz'],
                $panelNoKey => $row['panel_no'],
                $isRevisionChangeKey => $row['is_revision_change'],
                $sendToReviewByKey => SharedManager::$fullname,
                $approvedByKey => ''
            ];

            $insertToExtensionDtoWorksQuery .= "($projectNumberKey,$nachbauNumberKey, $dtoNumberKey, $dtoDescriptionKey, $dtoTypicalNoKey, $productIdKey, $materialAddedStartsByKey, $materialAddedNoKey, $materialAddedDescriptionKey, 
                                                    $materialDeletedStartsByKey, $materialDeletedNoKey, $materialDeletedDescriptionKey, $operationKey, $isAccessoryKey, $statusKey, $quantityKey, $unitKey, $noteKey, 
                                                    $extensionKmatNameKey, $workCenterIdKey, $workCenterKey, $workContentKey, $positionKey, $parentKmatKey, $subKmatKey, $typicalNoKey,
                                                    $ortzKzKey, $panelNoKey, $sendToReviewByKey, $isRevisionChangeKey, $approvedByKey),";
            $index++;
        }

        if ($index > 0) {
            $insertToExtensionDtoWorksQuery = rtrim($insertToExtensionDtoWorksQuery, ',');
            DbManager::fetchPDOQuery('dto_configurator', $insertToExtensionDtoWorksQuery, $extensionDtoWorksArray, [], false);

            SharedManager::saveLog('log_dtoconfigurator', "CREATED | Extension DTO Works are transferred. | " . implode(' | ', $_POST));
            Journals::saveJournal("CREATED | Extension DTO Works are transferred. | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_OPERATIONS, ACTION_CREATED, implode(' | ', $_POST), "Nachbau Transfer");
        }

        return $skippedWorks;
    }

    public function transferInterchangeDtoChanges($projectNo, $selectedNachbau, $transferToNachbau, $allTypicalsByDto, $selectedNachbauExtraDtosFlipped, $typicalToPanelsMap): array {
        $interchangeDtoWorksArray = [];
        $skippedWorks = [];

        // Seçili aktarımdaki interchange dto çalışmalarını getir.
        $query = "SELECT * FROM project_works_interchange WHERE project_number = :pNo AND nachbau_number = :selectedNachbau AND deleted IS NULL";
        $selectedNachbauInterchangeDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':selectedNachbau' => $selectedNachbau])['data'] ?? [];

        if (empty($selectedNachbauInterchangeDtoWorks))
            return [];

        // OPTIMIZATION: Delete/Replace operasyonları için malzeme kontrolü
        $materialsToCheck = [];
        foreach ($selectedNachbauInterchangeDtoWorks as $row) {
            if (($row['operation'] === 'delete' || $row['operation'] === 'replace') && !empty($row['material_deleted_number'])) {
                $materialsToCheck[] = $row['material_deleted_number'];
            }
        }

        // Tüm malzemelerin nachbau'daki varlığını tek sorguda kontrol et
        $nachbauMaterialData = [];
        if (!empty($materialsToCheck)) {
            $nachbauMaterialData = $this->checkMaterialsExistInNachbau($projectNo, $transferToNachbau, $materialsToCheck);
        }

        $index = 0;
        $insertToInterchangeDtoWorksQuery = "INSERT INTO project_works_interchange(project_number, nachbau_number, dto_number, dto_description, product_id, material_added_starts_by, material_added_number, material_added_description, material_deleted_starts_by, material_deleted_number, material_deleted_description,
                   operation, quantity, unit, work_center_id, work_center, work_content, position, parent_kmat, typical_no, ortz_kz, panel_no, is_revision_change, send_to_review_by, approved_by) VALUES ";

        foreach ($selectedNachbauInterchangeDtoWorks as $row) {
            $formattedDtoNumber = $this->formatDtoNumber($row['dto_number']);
            $transferToNachbauDtoTypicals = $allTypicalsByDto[$formattedDtoNumber] ?? [];

            // DTO veya DTO'nun geçtiği bir tipik yeni aktarımda yoksa atla
            if (isset($selectedNachbauExtraDtosFlipped[$formattedDtoNumber])) {
                // Sadece tipik bazlı çalışıldığı için db de tipik bazlı insert işlemi var. Değişiklikler altındaki her panoya uygulanmalı.
                $typical = $row['typical_no'];

                if (isset($typicalToPanelsMap[$typical])) {
                    foreach ($typicalToPanelsMap[$typical] as $panelData) {
                        $skippedWorks[] = [
                            'project_number' => $row['project_number'],
                            'selected_nachbau' => $selectedNachbau,
                            'transfer_to_nachbau' => $transferToNachbau,
                            'released_dto_type_id' => 6,
                            'skip_reason' => 'DTO not found in target nachbau',
                            'dto_number' => $row['dto_number'],
                            'dto_description' => $row['dto_description'],
                            'operation' => $row['operation'],
                            'typical_no' => $typical,
                            'ortz_kz' => $panelData['ortz_kz'],
                            'panel_no' => $panelData['panel_no'],
                            'material_added_starts_by' => $row['material_added_starts_by'],
                            'material_added_number' => $row['material_added_number'],
                            'material_added_description' => $row['material_added_description'],
                            'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                            'material_deleted_number' => $row['material_deleted_number'],
                            'material_deleted_description' => $row['material_deleted_description'],
                            'work_center' => $row['work_center'],
                            'work_content' => $row['work_content'],
                            'parent_kmat' => $row['parent_kmat'],
                            'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                            'unit' => $row['unit'],
                            'is_revision_change' => $row['is_revision_change']
                        ];
                    }
                }
                continue;
            }

            if (!in_array($row['typical_no'], $transferToNachbauDtoTypicals)) {
                $typical = $row['typical_no'];

                if (isset($typicalToPanelsMap[$typical])) {
                    foreach ($typicalToPanelsMap[$typical] as $panelData) {
                        $skippedWorks[] = [
                            'project_number' => $row['project_number'],
                            'selected_nachbau' => $selectedNachbau,
                            'transfer_to_nachbau' => $transferToNachbau,
                            'released_dto_type_id' => 6,
                            'skip_reason' => 'Typical ' . $typical . ' not found in target nachbau for this DTO',
                            'dto_number' => $row['dto_number'],
                            'dto_description' => $row['dto_description'],
                            'operation' => $row['operation'],
                            'typical_no' => $typical,
                            'ortz_kz' => $panelData['ortz_kz'],
                            'panel_no' => $panelData['panel_no'],
                            'material_added_number' => $row['material_added_number'],
                            'material_added_description' => $row['material_added_description'],
                            'material_deleted_number' => $row['material_deleted_number'],
                            'material_deleted_description' => $row['material_deleted_description'],
                            'work_center' => $row['work_center'],
                            'work_content' => $row['work_content'],
                            'parent_kmat' => $row['parent_kmat'],
                            'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                            'unit' => $row['unit'],
                            'is_revision_change' => $row['is_revision_change']
                        ];
                    }
                }

                continue;
            }

            // Delete/Replace ise malzeme kontrolü
            if ($row['operation'] === 'delete' || $row['operation'] === 'replace') {
                $materialKey = $row['material_deleted_number'] . '_' . $row['typical_no'] . '_' . $row['ortz_kz'];

                if (!isset($nachbauMaterialData[$materialKey])) {
                    $typical = $row['typical_no'];
                    $ortzKz = $row['ortz_kz'];

                    // Only add skipped works for panels matching this specific ortz_kz
                    if (isset($typicalToPanelsMap[$typical])) {
                        foreach ($typicalToPanelsMap[$typical] as $panelData) {
                            if ($panelData['ortz_kz'] === $ortzKz) {
                                $skippedWorks[] = [
                                    'project_number' => $row['project_number'],
                                    'selected_nachbau' => $selectedNachbau,
                                    'transfer_to_nachbau' => $transferToNachbau,
                                    'released_dto_type_id' => 6,
                                    'skip_reason' => 'Material not found in target nachbau for this panel ' . $panelData['ortz_kz'] . '/' . $panelData['panel_no'],
                                    'dto_number' => $row['dto_number'],
                                    'dto_description' => $row['dto_description'],
                                    'operation' => $row['operation'],
                                    'typical_no' => $typical,
                                    'ortz_kz' => $panelData['ortz_kz'],
                                    'panel_no' => $panelData['panel_no'],
                                    'material_added_number' => $row['material_added_number'],
                                    'material_added_description' => $row['material_added_description'],
                                    'material_deleted_number' => $row['material_deleted_number'],
                                    'material_deleted_description' => $row['material_deleted_description'],
                                    'work_center' => $row['work_center'],
                                    'work_content' => $row['work_content'],
                                    'parent_kmat' => $row['parent_kmat'],
                                    'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                                    'unit' => $row['unit'],
                                    'is_revision_change' => $row['is_revision_change']
                                ];
                            }
                        }
                    }

                    continue;
                }
            }

            $projectNumberKey = ":project_number$index";
            $nachbauNumberKey = ":nachbau_number$index";
            $dtoNumberKey = ":dto_number$index";
            $dtoDescriptionKey = ":dto_description$index";
            $productIdKey = ":product_id$index";
            $materialAddedStartsByKey = ":material_added_starts_by$index";
            $materialAddedNoKey = ":material_added_number$index";
            $materialAddedDescriptionKey = ":material_added_description$index";
            $materialDeletedStartsByKey = ":material_deleted_starts_by$index";
            $materialDeletedNoKey = ":material_deleted_number$index";
            $materialDeletedDescriptionKey = ":material_deleted_description$index";
            $operationKey = ":operation$index";
            $quantityKey = ":quantity$index";
            $unitKey = ":unit$index";
            $workCenterIdKey = ":work_center_id$index";
            $workCenterKey = ":work_center$index";
            $workContentKey = ":work_content$index";
            $positionKey = ":position$index";
            $parentKmatKey = ":parent_kmat$index";
            $typicalNoKey = ":typical_no$index";
            $ortzKzKey = ":ortz_kz$index";
            $panelNoKey = ":panel_no$index";
            $isRevisionChangeKey = ":is_revision_change$index";
            $sendToReviewByKey = ":send_to_review_by$index";
            $approvedByKey = ":approved_by$index";

            $interchangeDtoWorksArray[] = [
                $projectNumberKey => $row['project_number'],
                $nachbauNumberKey => $transferToNachbau,
                $dtoNumberKey => $row['dto_number'],
                $dtoDescriptionKey => html_entity_decode($row['dto_description']),
                $productIdKey => $row['product_id'],
                $materialAddedStartsByKey => $row['material_added_starts_by'],
                $materialAddedNoKey => $row['material_added_number'],
                $materialAddedDescriptionKey => $row['material_added_description'],
                $materialDeletedStartsByKey => $row['material_deleted_starts_by'],
                $materialDeletedNoKey => $row['material_deleted_number'],
                $materialDeletedDescriptionKey => $row['material_deleted_description'],
                $operationKey => $row['operation'],
                $quantityKey => $row['quantity'],
                $unitKey => $row['unit'],
                $workCenterIdKey => $row['work_center_id'],
                $workCenterKey => $row['work_center'],
                $workContentKey => $row['work_content'],
                $positionKey => $row['position'],
                $parentKmatKey => $row['parent_kmat'],
                $typicalNoKey => $row['typical_no'],
                $ortzKzKey => $row['ortz_kz'],
                $panelNoKey => $row['panel_no'],
                $isRevisionChangeKey => $row['is_revision_change'],
                $sendToReviewByKey => SharedManager::$fullname,
                $approvedByKey => ''
            ];

            $insertToInterchangeDtoWorksQuery .= "($projectNumberKey, $nachbauNumberKey, $dtoNumberKey, $dtoDescriptionKey, $productIdKey, $materialAddedStartsByKey, $materialAddedNoKey, $materialAddedDescriptionKey, 
                                                    $materialDeletedStartsByKey, $materialDeletedNoKey, $materialDeletedDescriptionKey, $operationKey, $quantityKey, $unitKey, $workCenterIdKey, $workCenterKey, 
                                                    $workContentKey, $positionKey, $parentKmatKey, $typicalNoKey, $ortzKzKey, $panelNoKey, $isRevisionChangeKey, $sendToReviewByKey, $approvedByKey),";
            $index++;
        }

        if ($index > 0) {
            $insertToInterchangeDtoWorksQuery = rtrim($insertToInterchangeDtoWorksQuery, ',');
            DbManager::fetchPDOQuery('dto_configurator', $insertToInterchangeDtoWorksQuery, $interchangeDtoWorksArray, [], false);

            SharedManager::saveLog('log_dtoconfigurator', "CREATED | Interchange DTO Works are transferred. | " . implode(' | ', $_POST));
            Journals::saveJournal("CREATED | Interchange DTO Works are transferred. | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_OPERATIONS, ACTION_CREATED, implode(' | ', $_POST), "Nachbau Transfer");
        }

        return $skippedWorks;
    }

    public function transferMinusPriceDtoChanges($projectNo, $selectedNachbau, $transferToNachbau, $allTypicalsByDto, $selectedNachbauExtraDtosFlipped): array {
        $minusPriceDtoWorksArray = [];
        $skippedWorks = [];

        // Seçili aktarımdaki minus price dto çalışmalarını getir.
        $query = "SELECT * FROM project_works_minus_price WHERE project_number = :pNo AND nachbau_number = :selectedNachbau AND wont_be_produced = 1";
        $selectedNachbauMinPriDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':selectedNachbau' => $selectedNachbau])['data'];

        if (empty($selectedNachbauMinPriDtoWorks))
            return [];

        // OPTIMIZATION: Malzeme kontrolü için verileri topla
        $materialsToCheck = [];
        foreach ($selectedNachbauMinPriDtoWorks as $row) {
            if (!empty($row['material_deleted_number'])) {
                $materialsToCheck[] = $row['material_deleted_number'];
            }
        }

        $nachbauMaterialData = [];
        if (!empty($materialsToCheck)) {
            $nachbauMaterialData = $this->checkMaterialsExistInNachbau($projectNo, $transferToNachbau, $materialsToCheck);
        }

        $index = 0;
        $insertToMinPriDtoWorksQuery = "INSERT INTO project_works_minus_price(project_number, nachbau_number, dto_number, dto_description, dto_typical_number, wont_be_produced, minus_price_remove_type, 
                                                                                material_deleted_starts_by, material_deleted_number, material_deleted_description, parent_kmat, ortz_kz, 
                                                                                panel_no, position, quantity, unit, is_revision_change, send_to_review_by, publish_by) VALUES ";

        foreach ($selectedNachbauMinPriDtoWorks as $row) {
            $transferToNachbauDtoTypicals = $allTypicalsByDto[$row['dto_number']] ?? [];

            // DTO yeni aktarımda yoksa atla
            if (isset($selectedNachbauExtraDtosFlipped[$row['dto_number']])) {
                $skippedWorks[] = [
                    'project_number' => $row['project_number'],
                    'selected_nachbau' => $selectedNachbau,
                    'transfer_to_nachbau' => $transferToNachbau,
                    'released_dto_type_id' => 4,
                    'skip_reason' => 'DTO not found in target nachbau',
                    'dto_number' => $row['dto_number'],
                    'dto_description' => $row['dto_description'],
                    'operation' => 'delete',
                    'typical_no' => $row['dto_typical_number'],
                    'ortz_kz' => $row['ortz_kz'],
                    'panel_no' => $row['panel_no'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_added_description' => $row['material_added_description'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number'],
                    'material_deleted_description' => $row['material_deleted_description'],
                    'work_center' => $row['work_center'],
                    'work_content' => $row['work_content'],
                    'parent_kmat' => $row['parent_kmat'],
                    'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                    'unit' => $row['unit'],
                    'is_revision_change' => $row['is_revision_change']
                ];

                continue;
            }

            if (!in_array($row['dto_typical_number'], $transferToNachbauDtoTypicals)) {
                $skippedWorks[] = [
                    'project_number' => $row['project_number'],
                    'selected_nachbau' => $selectedNachbau,
                    'transfer_to_nachbau' => $transferToNachbau,
                    'released_dto_type_id' => 4,
                    'skip_reason' => 'Typical not found in target nachbau for this DTO',
                    'dto_number' => $row['dto_number'],
                    'dto_description' => $row['dto_description'],
                    'operation' => 'delete',
                    'typical_no' => $row['dto_typical_number'],
                    'ortz_kz' => $row['ortz_kz'],
                    'panel_no' => $row['panel_no'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_added_description' => $row['material_added_description'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number'],
                    'material_deleted_description' => $row['material_deleted_description'],
                    'work_center' => $row['work_center'],
                    'work_content' => $row['work_content'],
                    'parent_kmat' => $row['parent_kmat'],
                    'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                    'unit' => $row['unit'],
                    'is_revision_change' => $row['is_revision_change']
                ];

                continue;
            }

            // Silinen malzeme yeni aktarımda var mı kontrol et
            if (!empty($row['material_deleted_number'])) {
                $materialKey = $row['material_deleted_number'] . '_' . $row['dto_typical_number'] . '_' . $row['ortz_kz'];

                if (!isset($nachbauMaterialData[$materialKey])) {
                    $skippedWorks[] = [
                        'project_number' => $row['project_number'],
                        'selected_nachbau' => $selectedNachbau,
                        'transfer_to_nachbau' => $transferToNachbau,
                        'released_dto_type_id' => 4,
                        'skip_reason' => 'Material not found in target nachbau',
                        'dto_number' => $row['dto_number'],
                        'dto_description' => $row['dto_description'],
                        'operation' => 'delete',
                        'typical_no' => $row['dto_typical_number'],
                        'ortz_kz' => $row['ortz_kz'],
                        'panel_no' => $row['panel_no'],
                        'material_added_starts_by' => $row['material_added_starts_by'],
                        'material_added_number' => $row['material_added_number'],
                        'material_added_description' => $row['material_added_description'],
                        'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                        'material_deleted_number' => $row['material_deleted_number'],
                        'material_deleted_description' => $row['material_deleted_description'],
                        'work_center' => $row['work_center'],
                        'work_content' => $row['work_content'],
                        'parent_kmat' => $row['parent_kmat'],
                        'quantity' => preg_replace('/\.000$/', '', $row['quantity']),
                        'unit' => $row['unit'],
                        'is_revision_change' => $row['is_revision_change']
                    ];

                    continue;
                }
            }

            $projectNumberKey = ":project_number$index";
            $nachbauNumberKey = ":nachbau_number$index";
            $dtoNumberKey = ":dto_number$index";
            $dtoDescriptionKey = ":dto_description$index";
            $dtoTypicalNoKey = ":dto_typical_number$index";
            $wontBeProducedKey = ":wont_be_produced$index";
            $minusPriceRemoveTypeKey = ":minus_price_remove_type$index";
            $materialDeletedStartsByKey = ":material_deleted_starts_by$index";
            $materialDeletedNoKey = ":material_deleted_number$index";
            $materialDeletedDescriptionKey = ":material_deleted_description$index";
            $parentKmatKey = ":parent_kmat$index";
            $ortzKzKey = ":ortz_kz$index";
            $panelNoKey = ":panel_no$index";
            $positionKey = ":position$index";
            $quantityKey = ":quantity$index";
            $unitKey = ":unit$index";
            $isRevisionNachbauKey = ":is_revision_change$index";
            $sendToReviewByKey = ":send_to_review_by$index";
            $publishByKey = ":publish_by$index";

            $minusPriceDtoWorksArray[] = [
                $projectNumberKey => $row['project_number'],
                $nachbauNumberKey => $transferToNachbau,
                $dtoNumberKey => $row['dto_number'],
                $dtoDescriptionKey => html_entity_decode($row['dto_description']),
                $dtoTypicalNoKey => $row['dto_typical_number'],
                $wontBeProducedKey => $row['wont_be_produced'],
                $minusPriceRemoveTypeKey => $row['minus_price_remove_type'],
                $materialDeletedStartsByKey => $row['material_deleted_starts_by'],
                $materialDeletedNoKey => $row['material_deleted_number'],
                $materialDeletedDescriptionKey => $row['material_deleted_description'],
                $parentKmatKey => $row['parent_kmat'],
                $ortzKzKey => $row['ortz_kz'],
                $panelNoKey => $row['panel_no'],
                $positionKey => $row['position'],
                $quantityKey => $row['quantity'],
                $unitKey => $row['unit'],
                $isRevisionNachbauKey => $row['is_revision_change'],
                $sendToReviewByKey => SharedManager::$fullname,
                $publishByKey => NULL
            ];

            $insertToMinPriDtoWorksQuery .= "($projectNumberKey, $nachbauNumberKey, $dtoNumberKey, $dtoDescriptionKey, $dtoTypicalNoKey, $wontBeProducedKey, $minusPriceRemoveTypeKey,
                                                    $materialDeletedStartsByKey, $materialDeletedNoKey, $materialDeletedDescriptionKey, $parentKmatKey, $ortzKzKey, $panelNoKey, 
                                                    $positionKey, $quantityKey, $unitKey, $isRevisionNachbauKey, $sendToReviewByKey, $publishByKey),";
            $index++;
        }

        if ($index > 0) {
            $insertToMinPriDtoWorksQuery = rtrim($insertToMinPriDtoWorksQuery, ',');
            DbManager::fetchPDOQuery('dto_configurator', $insertToMinPriDtoWorksQuery, $minusPriceDtoWorksArray, [], false);

            SharedManager::saveLog('log_dtoconfigurator', "CREATED | Minus Price DTO Works are transferred. | " . implode(' | ', $_POST));
            Journals::saveJournal("CREATED | Minus Price DTO Works are transferred. | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_OPERATIONS, ACTION_CREATED, implode(' | ', $_POST), "Nachbau Transfer");
        }

        return $skippedWorks;
    }

    public function transferSpecialDtoChanges($projectNo, $selectedNachbau, $transferToNachbau, $allTypicalsByDto, $selectedNachbauExtraDtosFlipped, $typicalToPanelsMap): array
    {
        $specialDtoWorksArray = [];
        $skippedWorks = [];

        // Seçili aktarımdaki special dto çalışmalarını getir.
        $query = "SELECT * FROM project_works_special_dtos WHERE project_number = :pNo AND nachbau_number = :selectedNachbau AND deleted IS NULL";
        $selectedNachbauSpecialDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':selectedNachbau' => $selectedNachbau])['data'] ?? [];

        if (empty($selectedNachbauSpecialDtoWorks))
            return [];

        // OPTIMIZATION: Delete/Replace operasyonları için malzeme kontrolü
        $materialsToCheck = [];
        foreach ($selectedNachbauSpecialDtoWorks as $row) {
            if (($row['operation'] === 'delete' || $row['operation'] === 'replace') && !empty($row['material_number'])) {
                $materialsToCheck[] = $row['material_number'];
            }
        }

        // Tüm malzemelerin nachbau'daki varlığını tek sorguda kontrol et
        $nachbauMaterialData = [];
        if (!empty($materialsToCheck)) {
            $nachbauMaterialData = $this->checkMaterialsExistInNachbau($projectNo, $transferToNachbau, $materialsToCheck);
        }

        $index = 0;
        $insertToSpecialDtoWorksQuery = "INSERT INTO project_works_special_dtos(project_number, nachbau_number, dto_number, dto_description, operation, position, typical_no, ortz_kz, panel_no, material_number, material_description, is_accessory, accessory_typical_number, accessory_parent_kmat, wda_parent_kmat, release_quantity, release_unit, is_revision_change, send_to_review_by, publish_by) VALUES ";

        foreach ($selectedNachbauSpecialDtoWorks as $row) {
            $transferToNachbauDtoTypicals = $allTypicalsByDto[$row['dto_number']] ?? [];

            if ($row['is_accessory'] === '1') {
                $typicalNo = $row['accessory_typical_number'];
                $ortzKz = '';
                $panelNo = $this->getAccessoryKmatObjectOfProject($projectNo)['panel_no'];
                $parentKmat = $row['accessory_parent_kmat'];
                $materialAddedStartsBy = $this->getSapMaterialPrefixByMaterialNo($row['material_number']);
                $materialAdded = $row['material_number'];
                $materialAddedDesc = $row['material_description'];
                $materialDeletedStartsBy = '';
                $materialDeleted = '';
                $materialDeletedDesc = '';
            } else {
                $typicalNo = $row['typical_no'];
                $ortzKz = $row['ortz_kz'];
                $panelNo = $row['panel_no'];
                $parentKmat = $row['wda_parent_kmat'];
                $materialAddedStartsBy = '';
                $materialAdded = '';
                $materialAddedDesc = '';
                $materialDeletedStartsBy = $this->getSapMaterialPrefixByMaterialNo($row['material_number']);
                $materialDeleted = $row['material_number'];
                $materialDeletedDesc = $row['material_description'];
            }

            // DTO veya DTO'nun geçtiği bir tipik yeni aktarımda yoksa atla
            if (isset($selectedNachbauExtraDtosFlipped[$row['dto_number']])) {

                $skippedWorks[] = [
                    'project_number' => $row['project_number'],
                    'selected_nachbau' => $selectedNachbau,
                    'transfer_to_nachbau' => $transferToNachbau,
                    'released_dto_type_id' => 7,
                    'skip_reason' => 'DTO not found in target nachbau',
                    'dto_number' => $row['dto_number'],
                    'dto_description' => $row['dto_description'],
                    'operation' => $row['operation'],
                    'typical_no' => $typicalNo,
                    'ortz_kz' => $ortzKz,
                    'panel_no' => $panelNo,
                    'material_added_starts_by' => $materialAddedStartsBy,
                    'material_added_number' => $materialAdded,
                    'material_added_description' => $materialAddedDesc,
                    'material_deleted_starts_by' => $materialDeletedStartsBy,
                    'material_deleted_number' => $materialDeleted,
                    'material_deleted_description' => $materialDeletedDesc,
                    'work_center' => '',
                    'work_content' => '',
                    'parent_kmat' => $parentKmat,
                    'quantity' => preg_replace('/\.000$/', '', $row['release_quantity']),
                    'unit' => $row['release_unit'],
                    'is_revision_change' => $row['is_revision_change']
                ];

                continue;
            }

            if (!in_array($row['typical_no'], $transferToNachbauDtoTypicals)) {
                $skippedWorks[] = [
                    'project_number' => $row['project_number'],
                    'selected_nachbau' => $selectedNachbau,
                    'transfer_to_nachbau' => $transferToNachbau,
                    'released_dto_type_id' => 7,
                    'skip_reason' => 'Typical not found in target nachbau for this DTO',
                    'dto_number' => $row['dto_number'],
                    'dto_description' => $row['dto_description'],
                    'operation' => $row['operation'],
                    'typical_no' => $typicalNo,
                    'ortz_kz' => $ortzKz,
                    'panel_no' => $panelNo,
                    'material_added_starts_by' => $materialAddedStartsBy,
                    'material_added_number' => $materialAdded,
                    'material_added_description' => $materialAddedDesc,
                    'material_deleted_starts_by' => $materialDeletedStartsBy,
                    'material_deleted_number' => $materialDeleted,
                    'material_deleted_description' => $materialDeletedDesc,
                    'work_center' => '',
                    'work_content' => '',
                    'parent_kmat' => $parentKmat,
                    'quantity' => preg_replace('/\.000$/', '', $row['release_quantity']),
                    'unit' => $row['release_unit'],
                    'is_revision_change' => $row['is_revision_change']
                ];

                continue;
            }

            // Delete/Replace ise malzeme kontrolü
            if ($row['operation'] === 'delete' || $row['operation'] === 'replace') {
                $materialKey = $row['material_number'] . '_' . $row['typical_no'] . '_' . $row['ortz_kz'];

                if (!isset($nachbauMaterialData[$materialKey])) {
                    $skippedWorks[] = array_merge($row, ['skip_reason' => '']);

                    $skippedWorks[] = [
                        'project_number' => $row['project_number'],
                        'selected_nachbau' => $selectedNachbau,
                        'transfer_to_nachbau' => $transferToNachbau,
                        'released_dto_type_id' => 7,
                        'skip_reason' => 'Material not found in target nachbau for this typical/panel',
                        'dto_number' => $row['dto_number'],
                        'dto_description' => $row['dto_description'],
                        'operation' => $row['operation'],
                        'typical_no' => $typicalNo,
                        'ortz_kz' => $ortzKz,
                        'panel_no' => $panelNo,
                        'material_added_starts_by' => $materialAddedStartsBy,
                        'material_added_number' => $materialAdded,
                        'material_added_description' => $materialAddedDesc,
                        'material_deleted_starts_by' => $materialDeletedStartsBy,
                        'material_deleted_number' => $materialDeleted,
                        'material_deleted_description' => $materialDeletedDesc,
                        'work_center' => '',
                        'work_content' => '',
                        'parent_kmat' => $parentKmat,
                        'quantity' => preg_replace('/\.000$/', '', $row['release_quantity']),
                        'unit' => $row['release_unit'],
                        'is_revision_change' => $row['is_revision_change']
                    ];

                    continue;
                }
            }

            $projectNumberKey = ":project_number$index";
            $nachbauNumberKey = ":nachbau_number$index";
            $dtoNumberKey = ":dto_number$index";
            $dtoDescriptionKey = ":dto_description$index";
            $operationKey = ":operation$index";
            $positionKey = ":position$index";
            $typicalNoKey = ":typical_no$index";
            $ortzKzKey = ":ortz_kz$index";
            $panelNoKey = ":panel_no$index";
            $materialNumberKey = ":material_number$index";
            $materialDescriptionKey = ":material_description$index";
            $isAccessoryKey = ":is_accessory$index";
            $accessoryTypicalNumberKey = ":accessory_typical_number$index";
            $accessoryParentKmatKey = ":accessory_parent_kmat$index";
            $wdaParentKmatKey = ":wda_parent_kmat$index";
            $releaseQuantityKey = ":release_quantity$index";
            $releaseUnitKey = ":release_unit$index";
            $isRevisionChangeKey = ":is_revision_change$index";
            $sendToReviewByKey = ":send_to_review_by$index";
            $publishByKey = ":publish_by$index";

            $specialDtoWorksArray[] = [
                $projectNumberKey => $row['project_number'],
                $nachbauNumberKey => $transferToNachbau,
                $dtoNumberKey => $row['dto_number'],
                $dtoDescriptionKey => html_entity_decode($row['dto_description']),
                $operationKey => $row['operation'],
                $positionKey => $row['position'],
                $typicalNoKey => $row['typical_no'],
                $ortzKzKey => $row['ortz_kz'],
                $panelNoKey => $row['panel_no'],
                $materialNumberKey => $row['material_number'],
                $materialDescriptionKey => $row['material_description'],
                $isAccessoryKey => $row['is_accessory'],
                $accessoryTypicalNumberKey => $row['accessory_typical_number'],
                $accessoryParentKmatKey => $row['accessory_parent_kmat'],
                $wdaParentKmatKey => $row['wda_parent_kmat'],
                $releaseQuantityKey => $row['release_quantity'],
                $releaseUnitKey => $row['release_unit'],
                $isRevisionChangeKey => $row['is_revision_change'],
                $sendToReviewByKey => $row['send_to_review_by'],
                $publishByKey => NULL

            ];

            $insertToSpecialDtoWorksQuery .= "($projectNumberKey, $nachbauNumberKey, $dtoNumberKey, $dtoDescriptionKey, $operationKey, $positionKey, $typicalNoKey, $ortzKzKey, $panelNoKey,
                            $materialNumberKey, $materialDescriptionKey, $isAccessoryKey, $accessoryTypicalNumberKey, $accessoryParentKmatKey, $wdaParentKmatKey, $releaseQuantityKey, $releaseUnitKey, $isRevisionChangeKey, $sendToReviewByKey, $publishByKey),";
            $index++;
        }

        if ($index > 0) {
            $insertToSpecialDtoWorksQuery = rtrim($insertToSpecialDtoWorksQuery, ',');
            DbManager::fetchPDOQuery('dto_configurator', $insertToSpecialDtoWorksQuery, $specialDtoWorksArray, [], false);

            SharedManager::saveLog('log_dtoconfigurator', "CREATED | Special DTO Works are transferred. | " . implode(' | ', $_POST));
            Journals::saveJournal("CREATED | Special DTO Works are transferred. | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_OPERATIONS, ACTION_CREATED, implode(' | ', $_POST), "Nachbau Transfer");
        }

        return $skippedWorks;
    }

    public function transferKukoNotes($projectNo, $selectedNachbau, $transferToNachbau, $selectedNachbauExtraDtosFlipped): void
    {
        $query = "SELECT * FROM kuko_notes WHERE project_number = :pNo AND nachbau_number = :nNo AND deleted IS NULL";
        $selectedNachbauNotes = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $selectedNachbau])['data'];

        if (empty($selectedNachbauNotes))
            return;


        $query = "INSERT INTO kuko_notes(project_number, nachbau_number, dto_number, kuko_note, is_dto_deleted, dto_exclude_option, created_by, updated_by)";
        $parameters = [];

        foreach($selectedNachbauNotes as $note) {
            if (!in_array($this->formatDtoNumber($note['dto_number']), $selectedNachbauExtraDtosFlipped)) {
                $parameters[] = [
                    $note['project_number'],
                    $transferToNachbau,
                    $note['dto_number'],
                    $note['kuko_note'],
                    $note['is_dto_deleted'],
                    $note['dto_exclude_option'],
                    $note['created_by'],
                    $note['updated_by']
                ];
            }
        }

        if (!empty($parameters)) {
            DbManager::fetchInsert('dto_configurator', $query, $parameters);
        }
    }

    public function checkIfProjectHasSpareDtoWorks($projectNo, $selectedNachbau) {
        $query = "SELECT id FROM project_works_spare WHERE project_number = :projectNo AND nachbau_number = :selectedNachbau AND deleted IS NULL LIMIT 1";
        $spareWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':projectNo' => $projectNo, ':selectedNachbau' => $selectedNachbau])['data'];

        if (count($spareWorks) === 0)
            return false;

        return true;
    }

    /**
     * Belirtilen malzemelerin transfer-to nachbau'da ilgili tipik ve panolarda var olup olmadığını kontrol et
     * Tek sorguda tüm malzemeleri kontrol et (performans optimizasyonu)
     */
    private function checkMaterialsExistInNachbau($projectNo, $nachbauNo, $materialsToCheck): array {
        if (empty($materialsToCheck)) return [];

        $conditions = [];
        $params = [':pNo' => $projectNo, ':nNo' => $nachbauNo];

        foreach ($materialsToCheck as $index => $material) {
            $paramKey = ":mat$index";
            $conditions[] = "kmat LIKE $paramKey";
            $params[$paramKey] = "%$material%";
        }

        $whereClause = implode(' OR ', $conditions);
        $query = "SELECT kmat, typical_no, ortz_kz FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND ($whereClause)";
        $result = DbManager::fetchPDOQueryData('planning', $query, $params)['data'] ?? [];

        $materialData = [];
        foreach ($result as $row) {
            foreach ($materialsToCheck as $material) {
                if (str_contains($row['kmat'], $material)) {
                    $key = $material . '_' . $row['typical_no'] . '_' . $row['ortz_kz'];
                    $materialData[$key] = true;
                }
            }
        }

        return $materialData;
    }

    private function insertSkippedWorks($projectNo, $selectedNachbau, $transferToNachbau, $dtoTypeId, $skippedWorks): void {
        if (empty($skippedWorks)) return;

        $insertQuery = "INSERT INTO nachbau_transfer_skipped_works(
            project_number, selected_nachbau, transfer_to_nachbau, released_dto_type_id, skip_reason,
            dto_number, dto_description, operation, typical_no, ortz_kz, panel_no,
            material_added_starts_by, material_added_number, material_added_description,
            material_deleted_starts_by, material_deleted_number, material_deleted_description,
            work_center, work_content, parent_kmat, quantity, unit, active, created_by, updated_by
        ) VALUES ";

        $params = [];
        $index = 0;

        foreach ($skippedWorks as $work) {
            $pnKey = ":project_number$index";
            $snKey = ":selected_nachbau$index";
            $tnKey = ":transfer_to_nachbau$index";
            $dtKey = ":released_dto_type_id$index";
            $srKey = ":skip_reason$index";
            $dnKey = ":dto_number$index";
            $ddKey = ":dto_description$index";
            $opKey = ":operation$index";
            $tyKey = ":typical_no$index";
            $ozKey = ":ortz_kz$index";
            $plKey = ":panel_no$index";
            $masbKey = ":material_added_starts_by$index";
            $manKey = ":material_added_number$index";
            $madKey = ":material_added_description$index";
            $mdsbKey = ":material_deleted_starts_by$index";
            $mdnKey = ":material_deleted_number$index";
            $mddKey = ":material_deleted_description$index";
            $wcKey = ":work_center$index";
            $wcnKey = ":work_content$index";
            $pkKey = ":parent_kmat$index";
            $qtyKey = ":quantity$index";
            $unitKey = ":unit$index";
            $activeKey = ":active$index";
            $cbKey = ":created_by$index";
            $ubKey = ":updated_by$index";

            $params[$pnKey] = $projectNo;
            $params[$snKey] = $selectedNachbau;
            $params[$tnKey] = $transferToNachbau;
            $params[$dtKey] = $dtoTypeId;
            $params[$srKey] = $work['skip_reason'] ?? 'Unknown';
            $params[$dnKey] = $work['dto_number'] ?? null;
            $params[$ddKey] = isset($work['dto_description']) ? html_entity_decode($work['dto_description']) : null;
            $params[$opKey] = $work['operation'] ?? null;
            $params[$tyKey] = $work['typical_no'] ?? null;
            $params[$ozKey] = $work['ortz_kz'] ?? null;
            $params[$plKey] = $work['panel_no'] ?? null;
            $params[$masbKey] = $work['material_added_starts_by'] ?? null;
            $params[$manKey] = $work['material_added_number'] ?? null;
            $params[$madKey] = $work['material_added_description'] ?? null;
            $params[$mdsbKey] = $work['material_deleted_starts_by'] ?? null;
            $params[$mdnKey] = $work['material_deleted_number'] ?? null;
            $params[$mddKey] = $work['material_deleted_description'] ?? null;
            $params[$wcKey] = $work['work_center'] ?? null;
            $params[$wcnKey] = $work['work_content'] ?? null;
            $params[$pkKey] = $work['parent_kmat'] ?? $work['accessory_parent_kmat'] ?? $work['spare_parent_kmat'] ?? null;
            $params[$qtyKey] = $work['quantity'] ?? $work['release_quantity'] ?? null;
            $params[$unitKey] = $work['unit'] ?? $work['release_unit'] ?? null;
            $params[$activeKey] = 1;
            $params[$cbKey] = SharedManager::$fullname;
            $params[$ubKey] = SharedManager::$fullname;

            $insertQuery .= "($pnKey, $snKey, $tnKey, $dtKey, $srKey, $dnKey, $ddKey, $opKey, $tyKey, $ozKey, $plKey, $masbKey,$manKey, $madKey, $mdsbKey, $mdnKey, $mddKey, $wcKey, $wcnKey, $pkKey, $qtyKey, $unitKey, $activeKey, $cbKey, $ubKey),";
            $index++;
        }

        if ($index > 0) {
            $insertQuery = rtrim($insertQuery, ',');
            DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $params);

            SharedManager::saveLog('log_dtoconfigurator', "CREATED | Skipped $dtoTypeId id works saved to database. Count: $index | ProjectNo: $projectNo");
        }
    }

    public function getTransferSummaryOfNachbauTransfer()
    {
        $projectNo = $_GET['projectNo'];
        $currentNachbau = $_GET['currentNachbau'];

        $query = "SELECT transferred_from_nachbau 
                    FROM projects 
                    WHERE project_number = :pNo AND nachbau_number = :currentNachbau";

        $previousNachbau = DbManager::fetchPDOQueryData('dto_configurator', $query, [
                    ':pNo' => $projectNo,
                    ':currentNachbau' => $currentNachbau
                ])['data'][0]['transferred_from_nachbau'];

        if(empty($previousNachbau)) {
            returnHttpResponse(400, 'No transfer record was found for the Nachbau.');
        }

        $query = "SELECT * 
               FROM nachbau_transfer_skipped_works 
               WHERE project_number = :pNo 
               AND selected_nachbau = :previousNachbau 
               AND transfer_to_nachbau = :currentNachbau 
               AND active = 1";

        $skippedWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [
            ':pNo' => $projectNo,
            ':previousNachbau' => $previousNachbau,
            ':currentNachbau' => $currentNachbau
        ])['data'];

        // Initialize arrays for each DTO type
        $skippedProjectWorks = [];
        $skippedSpares = [];
        $skippedInterchanges = [];
        $skippedExtensions = [];
        $skippedMinusPrices = [];
        $skippedSpecials = [];

        // Group skipped works by released_dto_type_id
        foreach ($skippedWorks as $work) {
            switch ($work['released_dto_type_id']) {
                case '1':
                    $skippedProjectWorks[] = $work;
                    break;
                case '2':
                    $skippedSpares[] = $work;
                    break;
                case '6':
                    $skippedInterchanges[] = $work;
                    break;
                case '3':
                    $skippedExtensions[] = $work;
                    break;
                case '4':
                    $skippedMinusPrices[] = $work;
                    break;
                case '7':
                    $skippedSpecials[] = $work;
                    break;
            }
        }

        $responseData = [
            'skipped' => [
                'projectWorks' => $skippedProjectWorks,
                'spare' => $skippedSpares,
                'interchange' => $skippedInterchanges,
                'extension' => $skippedExtensions,
                'minusPrice' => $skippedMinusPrices,
                'special' => $skippedSpecials
            ],
            'fromNachbau' => $previousNachbau,
            'toNachbau' => $currentNachbau
        ];

        echo json_encode($responseData);
        exit();
    }
}

$controller = new NachbauTransferController($_POST);

$response = match ($_GET['action']) {
    'checkNachbauTransferDtoDifferences' => $controller->checkNachbauTransferDtoDifferences(),
    'getTransferSummaryOfNachbauTransfer' => $controller->getTransferSummaryOfNachbauTransfer(),
    default => ['status' => 400, 'message' => 'Invalid action']
};

$response = match ($_POST['action']) {
    'transferNachbauToAnother' => $controller->transferNachbauToAnother(),
    default => ['status' => 400, 'message' => 'Invalid action']
};

