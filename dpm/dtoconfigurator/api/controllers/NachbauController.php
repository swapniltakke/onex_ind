<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('memory_limit', '2048M');

class NachbauController extends BaseController
{
    public function getNachbauDataOfProject(): void
    {
        $projectNo = $_GET['projectNo'];

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Nachbau Data of Project Request | " . $projectNo);
        Journals::saveJournal("PROCESSING | Get Nachbau Data of Project Request | " . $projectNo, PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_FILTER, ACTION_PROCESSING, implode(' | ', $_GET), "Get Nachbau Data");

        // Key: Nachbaus - Value: Last Nachbau Time
        $nachbauTime = $this->getNachbauTimesOfProject($projectNo);
        $accessoryObjectOfProject = $this->getAccessoryKmatObjectOfProject($projectNo);
        $accessoryTypicalNo = $accessoryObjectOfProject['typical_no'];

        $nachbauDtosWithTypicals = $this->getDtosWithTypicalsForAllNachbaus($projectNo, $accessoryTypicalNo);

        $nachbauNos = array_map(fn($item) => $item['nachbau_no'], $nachbauDtosWithTypicals);
        $uniqueNachbaus = array_values(array_unique($nachbauNos));

        $nachbauPublishStatus = [];
        foreach($uniqueNachbaus as $nachbau) {
            $query = "SELECT project_status FROM projects WHERE project_number = :pNo AND nachbau_number = :nNo";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbau])['data'][0] ?? [];
            $nachbauPublishStatus[$nachbau] = $result['project_status'];
        }

        if (empty($nachbauDtosWithTypicals))
            returnHttpResponse(400, 'There is no DTO Number exists for this project.');

        foreach ($nachbauDtosWithTypicals as $row) {
            if ($row['typical_no'] === $accessoryTypicalNo)
            {
                $data[$row['nachbau_no']]['Accessories'][$accessoryTypicalNo][$row['ortz_kz']]['panel_no'] = $row['panel_no'];
                $data[$row['nachbau_no']]['Acessories'][$accessoryTypicalNo][$row['ortz_kz']]['dtos'][$row['kmat_name']] = $row['description'];
            } else {
                $data[$row['nachbau_no']]['Typical'][$row['typical_no']][$row['ortz_kz']]['panel_no'] = $row['panel_no'];
                $data[$row['nachbau_no']]['Typical'][$row['typical_no']][$row['ortz_kz']]['dtos'][$row['kmat_name']] = $row['description'];
                $data[$row['nachbau_no']]['Panel'][$row['ortz_kz']]['panel_no'] = $row['panel_no'];
                $data[$row['nachbau_no']]['Panel'][$row['ortz_kz']]['typical_no'] = $row['typical_no'];
                $data[$row['nachbau_no']]['Panel'][$row['ortz_kz']]['dtos'][$row['kmat_name']] = $row['description'];
            }

            $data[$row['nachbau_no']]['Time'] = $nachbauTime[$row['nachbau_no']]['LastUpdated'];
            $data[$row['nachbau_no']]['AccessoryParentKmat'] = $accessoryObjectOfProject['kmat'];
            $data[$row['nachbau_no']]['AccessoryTypicalCode'] = $accessoryTypicalNo;
            $data[$row['nachbau_no']]['AccessoryPanelNo'] = $accessoryObjectOfProject['panel_no'];
            $data[$row['nachbau_no']]['PublishStatus'] = $nachbauPublishStatus[$row['nachbau_no']];
        }

        uasort($data, function($a, $b) {
            $timeA = new DateTime($a['Time']);
            $timeB = new DateTime($b['Time']);
            return $timeB <=> $timeA;
        });

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Get Nachbau Data of Project Request " . $projectNo . " is Successfully Returned");
        Journals::saveJournal("RETURNED | Get Nachbau Data of Project Request " . $projectNo . " is Successfully Returned", PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_FILTER, ACTION_VIEWED, implode(' | ', $_GET), "Nachbau Data");

        // Start gzip output buffering
        ob_start("ob_gzhandler");

        // Send JSON header
        header("Content-Encoding: gzip");
        header("Content-Type: application/json");

        echo (json_encode($data));
        exit();
    }

    public function getNachbauTimesOfProject($projectNo)
    {
        $query = "SELECT FileName, DATE_FORMAT(LastUpdated, '%d.%m.%Y %H:%i') AS LastUpdated 
                  FROM log_nachbau WHERE FactoryNumber = :pNo
                  GROUP BY FileName
                  ORDER BY Id DESC";

        $result = DbManager::fetchPDOQueryData('logs', $query, [':pNo' => $projectNo])['data'];
        return array_combine(array_column($result, 'FileName'), $result); // key-value -> NachbauNo-LastUpdated
    }

    public function getNachbauFilesOfProjectWithStatus() {
        $projectNo = $_GET['projectNo'];

        $query = "SELECT FileName, DATE_FORMAT(LastUpdated, '%d.%m.%Y') AS LastUpdated 
              FROM log_nachbau WHERE FactoryNumber = :pNo
              GROUP BY FileName
              ORDER BY Id DESC";

        $result = DbManager::fetchPDOQueryData('logs', $query, [':pNo' => $projectNo])['data'];

        $nachbauFileNames = array_column($result, 'FileName');

        if (!empty($nachbauFileNames)) {
            $statusQuery = "SELECT nachbau_number, project_status FROM projects 
                        WHERE project_number = :pNo AND nachbau_number IN (:nNos)";

            $statusResult = DbManager::fetchPDOQueryData('dto_configurator', $statusQuery, [':pNo' => $projectNo, ':nNos' => $nachbauFileNames ])['data'] ?? [];

            $statusLookup = [];
            foreach ($statusResult as $row) {
                $statusLookup[$row['nachbau_number']] = $row['project_status'];
            }
        } else {
            $statusLookup = [];
        }

        $enrichedResult = [];
        foreach ($result as $row) {
            $fileName = $row['FileName'];
            $status = $statusLookup[$fileName] ?? null;
            $isPublished = ($status == 5);

            $enrichedResult[$fileName] = [
                'FileName' => $fileName,
                'LastUpdated' => $row['LastUpdated'],
                'isPublished' => $isPublished
            ];
        }

        echo json_encode($enrichedResult);
        exit();
    }



    public function getMinusPriceDtosOfNachbau(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];

        $query = "SELECT DISTINCT kmat_name as dto_number, description, typical_no
                    FROM nachbau_datas 
                    WHERE project_no = :pNo AND nachbau_no = :nNo
                    AND kmat_name LIKE '%MINPRI%' AND description != ''
                    ORDER BY dto_number";

        $data = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo'=>$nachbauNo])['data'] ?? [];
        echo (json_encode($data));exit;
    }

    public function getSpareDtosOfNachbau(): void {
        $spareWdaDtos = [];
        $sparePartDtos = [];
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $accessoryTypicalNo = $_GET['accessoryTypicalNo'];

        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT DISTINCT kmat_name as dto_number, description, typical_no
                  FROM nachbau_datas 
                  WHERE project_no = :pNo AND nachbau_no = :nNo
                  AND kmat_name LIKE '%::%'                  
                  AND kmat_name REGEXP :pattern
                  AND description != ''
                  ORDER BY dto_number";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo'=>$nachbauNo, ':pattern' => $nachbauDtoPattern])['data'] ?? [];

        foreach($result as $dto) {
            $dtoDescription = strtolower($this->formatDescription($dto['description'], 5));

            if(str_contains($dtoDescription, 'spare withdrawable') || str_contains($dtoDescription, 'spare wda') || str_contains($dtoDescription, 'spare cb truck')) {
                if (str_starts_with($dto['dto_number'], ':: KUKO'))
                    $dto['dto_number'] = $this->formatKukoDtoNumber($dto['dto_number']);
                else
                    $dto['dto_number'] = $this->formatDtoNumber($dto['dto_number']);

                $spareWdaDtos[] = $dto;
            }
            else if (str_contains($dtoDescription, 'spare part')) {

                if (str_starts_with($dto['dto_number'], ':: KUKO'))
                    $dto['dto_number'] = $this->formatKukoDtoNumber($dto['dto_number']);
                else
                    $dto['dto_number'] = $this->formatDtoNumber($dto['dto_number']);

                $sparePartDtos[] = $dto;
            }
        }

        $data = ['spareWdaDtos' => $spareWdaDtos, 'sparePartDtos' => $sparePartDtos];
        echo (json_encode($data));exit;
    }

    public function getSubMaterialListsOfWdaAndSpareChanges(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $typicalNo = $_GET['typicalNo'];
        $kmat = $_GET['kmat'];

        $panelsOfWdaTypical = $this->getOrtzKzsOfProjectTypical($projectNo, $nachbauNo, $typicalNo);

        // Bu sorgu, PA Withdrawable Unit veya PA Truck istasyonunun altındaki malzeme listelerini, istekden gelen tipiğe göre döndürüyor.
        $query = "SELECT typical_no, ortz_kz, panel_no,
                    CASE 
                        WHEN kmat LIKE '00%' THEN SUBSTRING(kmat, 3)
                        ELSE kmat 
                    END AS material_added_number,
                    SUM(qty) AS quantity,
                    unit,
                    kmat_name AS material_added_description
                FROM nachbau_datas
                WHERE project_no = :pNo
                  AND nachbau_no = :nNo
                  AND parent_kmat LIKE :pKmat
                  AND typical_no = :typicalNo
                  AND ortz_kz = :ortzKz
                  AND kmat NOT LIKE '0030%'
                GROUP BY kmat, typical_no, ortz_kz, panel_no, material_added_number, unit, kmat_name";
        $data = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':pKmat' => '%' . $kmat, ':typicalNo' => $typicalNo, ':ortzKz' => $panelsOfWdaTypical[0]])['data'] ?? [];

        if (empty($data))
            returnHttpResponse(404, 'PA Withdrawable Unit or PA Truck KMAT does not have lists under this typical.');

        //Bu sorgu, WDA listelerinde DTO lu değişiklik yapıldıysa ona göre listeleri düzenleyip son spare wda listesini hazırlar.
        $query = "SELECT material_added_number, material_added_description, material_deleted_number, material_deleted_description, release_item, release_quantity, release_type, unit
                  FROM order_changes WHERE project_number = :pNo AND nachbau_number = :nNo AND parent_kmat = :wdaKmat 
                  AND active = 1 AND (release_item = :spareTypical OR release_item IN (:panelsOfWdaTypical))";
        $spareDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo,
            ':wdaKmat' => $kmat, ':spareTypical' => $typicalNo, ':panelsOfWdaTypical' => $panelsOfWdaTypical])['data'] ?? [];

        if (!empty($spareDtoWorks)) {
            $existingMaterialNumbers = array_column($data, 'material_added_number');

            foreach ($spareDtoWorks as $work) {
                // REMOVE logic: remove material_deleted_number if it exists in $data
                if (!empty($work['material_deleted_number'])) {
                    $data = array_filter($data, function ($item) use ($work) {
                        return $item['material_added_number'] !== $work['material_deleted_number'];
                    });

                    // Also update existingMaterialNumbers
                    $existingMaterialNumbers = array_column($data, 'material_added_number');
                }

                // ADD logic: add material_added_number if it’s not already in $data
                if (!empty($work['material_added_number']) && !in_array($work['material_added_number'], $existingMaterialNumbers, true)) {
                    $data[] = [
                        "typical_no" => $typicalNo,
                        "ortz_kz" => '',
                        "panel_no" => $work['release_item'],
                        "material_added_number" => $work['material_added_number'],
                        "quantity" => $work['release_quantity'],
                        "unit" => $work['unit'],
                        "material_added_description" => $work['material_added_description']
                    ];

                    $existingMaterialNumbers[] = $work['material_added_number'];
                }
            }
        }

        echo (json_encode(array_values($data)));
        exit;
    }

    public function getDtosWithTypicalsForAllNachbaus($projectNo, $accessoryTypicalNumber): array {

        // If a transfer has 1 DTO and the next transfer removes that DTO and has 0 DTO, it wasn't capturing the TXT file with 0 DTO. I first query log_nachbau to ensure it also captures TXT files without DTOs in the order.
        $query = "SELECT FileName FROM log_nachbau WHERE FactoryNumber = :pNo GROUP BY FileName";
        $result = DbManager::fetchPDOQueryData('logs', $query, [':pNo' => $projectNo])['data'] ?? [];
        $nachbauAllTxts = array_column($result, 'FileName');

        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT nachbau_no, typical_no, ortz_kz, panel_no, kmat_name, description
                FROM nachbau_datas 
                WHERE project_no=:pNo 
                AND kmat_name REGEXP :pattern
                GROUP BY nachbau_no, typical_no, ortz_kz, panel_no, kmat_name
                ORDER BY nachbau_no DESC";

        $nachbauData = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':pattern' => $nachbauDtoPattern])['data'] ?? [];
        $nachbauTxtsWithDtos = array_unique(array_column($nachbauData, 'nachbau_no'));

        // Find extra elements in $nachbauAllTxts that are not in $nachbauTxtsWithDtos
        $extraNachbauNos = array_diff($nachbauAllTxts, $nachbauTxtsWithDtos);

        // Eğer DTOsuz aktarım varsa onuda ekle
        foreach ($extraNachbauNos as $extraNo) {
            $nachbauData[] = [
                'nachbau_no' => $extraNo,
                'typical_no' => null,
                'ortz_kz' => null,
                'panel_no' => null,
                'kmat_name' => null,
                'description' => null,
            ];
        }

        $accessoryDtos = [];
        $otherDtos = [];

        foreach ($nachbauData as $row) {
            if ($row['typical_no'] === $accessoryTypicalNumber) {
                $accessoryDtos[] = $row;
            } else {
                $otherDtos[] = $row;
            }
        }

        // Iterate over $accessoryDtos and format kmat_name
        foreach ($accessoryDtos as $index => $accessoryRow) {
            $kmatName = $accessoryRow['kmat_name'];

            if (str_contains($kmatName, 'KUKO_CON')) {
                $formattedDtoNumber = $this->formatKukoDtoNumber($kmatName);
            } else {
                $formattedDtoNumber = $this->formatDtoNumber($kmatName);
            }

            // Check if the formatted DTO number exists in $otherDtos
            foreach ($otherDtos as $otherRow) {
                if ($otherRow['nachbau_no'] === $accessoryRow['nachbau_no'])
                {
                    if (str_contains($otherRow['kmat_name'], $formattedDtoNumber)) {
                        // Remove the DTO from the accessory typical if it exists in other typicals
                        unset($accessoryDtos[$index]);
                        break;
                    }
                }
            }
            if (str_contains($accessoryRow['kmat_name'], 'KUKO_CON'))
                unset($accessoryDtos[$index]);
        }

        $accessoryDtos = array_values($accessoryDtos);
        return array_merge($accessoryDtos, $otherDtos);
    }


    public function getPanelsOfSelectedTypicals(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $selectedTypicals = explode(',', $_GET['selectedTypicals']);

        //EĞER MULTI-SELECTED TYPICAL VAR ISE
        if (empty($selectedTypicals) || !$selectedTypicals[0] ) {
            $condition = '';
            $params = [$projectNo, $nachbauNo];

        } else {
            $placeholders = implode(',', array_fill(0, count($selectedTypicals), '?'));

            $condition = "AND typical_no IN ($placeholders)";
            $params = array_merge([$projectNo, $nachbauNo], $selectedTypicals);
        }

        $query = "SELECT ortz_kz FROM nachbau_datas 
              WHERE project_no = ? AND nachbau_no = ? $condition AND ortz_kz != ''
              GROUP BY ortz_kz ORDER BY ortz_kz";

        $data = array_column(DbManager::fetchPDOQueryData('planning', $query, $params)['data'], 'ortz_kz');

        echo json_encode($data);
        exit();
    }


    public function getOrtzKzsAndPanelNoOfSelectedTypicals() {
        $page = $_GET['page'] ?? '';
        $selectedTypicals = $_GET['selectedTypicals'] ?? [];

        if ($page === 'admin') {
            $releasedProjectId = $_GET['releasedProjectId'] ?? '';

            $query = "SELECT * FROM view_released_projects WHERE released_project_id = :releasedProjectId";
            $releasedProject = DbManager::fetchPDOQueryData('dto_configurator', $query, [':releasedProjectId' => $releasedProjectId])['data'][0];

            // Son submission rejected veya approval ise, eklenen sipariş değişiklikleri ilgili projenin pending approval a gönderilmiş son release_project_id'sinde.
            if (in_array($releasedProject['submission_status'], ['4', '5'])) {
                $query = "SELECT released_project_id FROM view_released_projects 
                        WHERE project_id = :projectId AND submission_status NOT IN ('4', '5') 
                        ORDER BY released_project_id DESC LIMIT 1";
                $releasedProject = DbManager::fetchPDOQueryData('dto_configurator', $query, [':projectId' => $releasedProject['project_id']])['data'][0];
            }

            $projectNo = $releasedProject['project_number'];
            $nachbauNo = $releasedProject['nachbau_number'];

        } else {
            $projectNo = $_GET['projectNo'] ?? '';
            $nachbauNo = $_GET['nachbauNo'] ?? '';
        }

        $query = "SELECT ortz_kz, panel_no
                  FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND typical_no IN (:typicals) 
                  GROUP BY ortz_kz, panel_no";
        $data = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':typicals' => $selectedTypicals])['data'];


        echo json_encode($data);
        exit();
    }


    public function getTypicalsOfProject(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];

        $query = "SELECT typical_no FROM nachbau_datas 
                    WHERE project_no=:pNo AND nachbau_no=:nNo
                    GROUP BY typical_no ORDER BY typical_no";

        $data = array_column(DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'], 'typical_no');
        echo json_encode($data); exit();
    }


    public function getCountOfListTypes(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $accessoryTypicalCode = $_GET['accessoryTypicalCode'] ?? "";

        // MTool bulunan proje, configurator veritabanında yoksa insert atsın
        $this->insertProjectNachbauIntoDtoConfigurator($projectNo, $nachbauNo);

        $onlyTypicalAndPanelDtos = $this->filterOnlyTypicalAndPanelDtos($projectNo,$nachbauNo,$accessoryTypicalCode);
        $onlyAccessoryDtos = $this->filterOnlyAccessoryDtos($projectNo,$nachbauNo,$accessoryTypicalCode);

        $data = ['countTypicalAndPanel' => count($onlyTypicalAndPanelDtos), 'countAccessories' => count($onlyAccessoryDtos)];
        echo(json_encode($data));
    }

    public function filterOnlyTypicalAndPanelDtos($projectNo, $nachbauNo, $accessoryTypicalCode): array {
        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT t1.kmat_name as dto_number, description
                 FROM nachbau_datas as t1 
                 WHERE t1.project_no = :pNo 
                 AND t1.kmat_name 
                 REGEXP :pattern 
                 AND t1.nachbau_no = :nNo 
                 AND kmat_name LIKE '%::%' 
                 AND t1.kmat_name NOT LIKE '%KUKO%'
                 AND t1.typical_no <> :acc_typical
                 GROUP BY dto_number
                 ORDER BY dto_number";

        return DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':acc_typical' => $accessoryTypicalCode, ':pattern' => $nachbauDtoPattern])['data'] ?? [];
    }

    public function filterOnlyAccessoryDtos($projectNo, $nachbauNo, $accessoryTypicalCode): array {

        $allDtos = $this->getAllDtoDataByNachbau($projectNo, $nachbauNo);

        // Separate data into accessory typicals and others
        $accessoryDtos = [];
        $otherDtos = [];

        foreach ($allDtos as $row) {
            if ($row['typical_no'] === $accessoryTypicalCode) {
                $accessoryDtos[] = $row;
            } else {
                $otherDtos[] = $row;
            }
        }

        foreach ($accessoryDtos as $index => $accessoryRow) {
            $kmatName = $accessoryRow['dto_number'];

            if (str_contains($kmatName, 'KUKO_CON')) {
                $formattedDtoNumber = $this->formatKukoDtoNumber($kmatName);
            } else {
                $formattedDtoNumber = $this->formatDtoNumber($kmatName);
            }

            // Check if the formatted DTO number exists in $otherDtos
            foreach ($otherDtos as $otherRow) {
                if (str_contains($otherRow['dto_number'], $formattedDtoNumber)) {
                    unset($accessoryDtos[$index]);
                    break;
                }
            }
            if (str_contains($accessoryRow['dto_number'], 'KUKO_CON')) {
                unset($accessoryDtos[$index]);
            }
        }

        return $accessoryDtos;
    }

    public function getOnlyAccessoryDtos(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $accessoryTypicalCode = $_GET['accessoryTypicalCode'];

        $accessoryDtos = $this->filterOnlyAccessoryDtos($projectNo, $nachbauNo, $accessoryTypicalCode);

        echo(json_encode($accessoryDtos));
    }

    public function getAllTypicalAndPanelDtos(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $accessoryTypicalCode = $_GET['accessoryTypicalCode'];

        $typicalAndPanelDtos = $this->filterOnlyTypicalAndPanelDtos($projectNo, $nachbauNo, $accessoryTypicalCode);

        echo(json_encode($typicalAndPanelDtos));
    }

    public function getDtoNumbersByTypeNumber(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $listType = $_GET['listType'];
        $typeNumber = $_GET['typeNumber'];

        $typeCondition = '';
        if ($listType === 'Typical')
            $typeCondition = "AND typical_no = :typeNumber";
        elseif ($listType === 'Panel')
            $typeCondition = "AND ortz_kz = :typeNumber";

        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT kmat_name AS dto_number, description FROM nachbau_datas 
                    WHERE project_no = :pNo
                    AND nachbau_no = :nNo
                    AND kmat_name REGEXP :pattern
                    $typeCondition
                    GROUP BY kmat_name
                    ORDER BY kmat_name";

        $params = [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':typeNumber' => $typeNumber, ':pattern' => $nachbauDtoPattern];
        $data = DbManager::fetchPDOQueryData('planning', $query, $params)['data'] ?? [];
        echo (json_encode($data));
    }

    public function getNachbauErrorItemAndSelectedList(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $operation = $_GET['operation'];
        $materialNoAdded = $_GET['materialAddedNo'];
        $materialNoDeleted = $_GET['materialDeletedNo'];

        // Fetch typicals and panels together
        $query = "SELECT typical_no, ortz_kz, panel_no
                  FROM nachbau_datas
                  WHERE project_no = :pNo
                    AND nachbau_no = :nNo
                    GROUP BY ortz_kz, panel_no
                  ORDER BY typical_no, ortz_kz";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo'=>$projectNo, ':nNo'=>$nachbauNo])['data'] ?? [];

        $typicalsWithPanels = [];

        foreach ($result as $row) {
            $typical = $row['typical_no'];
            $panel = $row['ortz_kz'];

            // Initialize typical if not already in array
            if (!isset($typicalsWithPanels[$typical])) {
                $typicalsWithPanels[$typical] = [
                    'exists' => false,  // Set default existence status
                    'panels' => []
                ];
            }

            // Add the panel to the typical
            $typicalsWithPanels[$typical]['panels'][] = $panel;
        }

        // Fetch typicals and panels where material exists
        $query = "SELECT DISTINCT typical_no, ortz_kz
                  FROM nachbau_datas
                  WHERE project_no = :pNo
                    AND nachbau_no = :nNo
                    AND kmat LIKE :materialNo
                  ORDER BY typical_no, ortz_kz";

        if ($operation === 'add')
            $checkMaterial = $materialNoAdded;
        else
            $checkMaterial = $materialNoDeleted;

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo'=>$projectNo, ':nNo'=>$nachbauNo, ':materialNo' => '%'.$checkMaterial.'%'])['data'] ?? [];


        foreach ($result as $row) {
            $typical = $row['typical_no'];

            // Set existence to true for typicals where the material exists
            if (isset($typicalsWithPanels[$typical])) {
                $typicalsWithPanels[$typical]['exists'] = true;
            }
        }

        //Get if any panel already inserted before (Fetch selected items which is right box)
        $query = "SELECT typical_no, ortz_kz FROM project_works_nachbau_errors
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND material_added_number = :mAdded AND material_deleted_number = :mDeleted AND deleted IS NULL";
        $parameters = [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':mAdded' => $materialNoAdded, ':mDeleted' => $materialNoDeleted];
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'] ?? [];

        $insertedItems = [];
        if (!empty($result)) {
            foreach ($result as $row) {
                $typical = $row['typical_no'];
                $panel = $row['ortz_kz'];

                // Initialize the typical in the insertedItems array if it doesn't exist
                if (!isset($insertedItems[$typical])) {
                    $insertedItems[$typical] = [];
                }

                // Add the panel to the typical in the insertedItems array
                $insertedItems[$typical][] = $panel;
            }
        }

        $data = ['allTypicalsWithPanels' => $typicalsWithPanels, 'insertedItems' => $insertedItems];
        echo (json_encode($data));
        exit();
    }

    public function getProjectCurrentStatus(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $isAuthorizedToWorkOnProject = false;

        $query = "SELECT p.working_user, p.project_status as status_id, s.`status` as status_name, p.who_send_approval, p.send_review_date, p.reviewed_date, p.review_note, p.transferred_from_nachbau, p.is_revision
                    FROM projects p LEFT JOIN `status` s ON p.project_status = s.id 
                    WHERE project_number = :pNo AND nachbau_number = :nNo";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'][0] ?? [];

        if ($result['working_user'] === SharedManager::$fullname)
            $isAuthorizedToWorkOnProject = true;

        $data = [
            'name' => $result['working_user'] ?? '',
            'isAuthorizedToWorkOnProject' => $isAuthorizedToWorkOnProject,
            'projectStatusId' => $result['status_id'],
            'projectStatusName' => $result['status_name'],
            'whoSendApproval' => $result['who_send_approval'],
            'sendReviewDate' => $result['send_review_date'],
            'reviewedDate' => $result['reviewed_date'],
            'reviewNote' => $result['review_note'],
            'transferredFromNachbau' => $result['transferred_from_nachbau'],
            'isRevisionNachbau' => $result['is_revision']
        ];

        echo (json_encode($data));
        exit();
    }

    public function getDescriptionsOfAffectedDtoNumbers(): void {
        $affectedDtoResponseObj = [];
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];

        $query = "SELECT affected_dto_numbers FROM project_work_view 
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND (affected_dto_numbers != NULL OR affected_dto_numbers != '')";
        $affectedDtoNumbersNotEmptyRows = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];
        $affectedDtoNumbersStrArray = array_column($affectedDtoNumbersNotEmptyRows, 'affected_dto_numbers');

        $affectedDtoNumbers = [];
        foreach ($affectedDtoNumbersStrArray as $row) {
            $dtosArr = explode('|', $row);
            foreach ($dtosArr as $dto) {
                if (!in_array($dto, $affectedDtoNumbers))
                    $affectedDtoNumbers[] = $dto;
            }
        }

        foreach ($affectedDtoNumbers as $affectedDto) {
            $query = "SELECT description FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat_name LIKE :dtoNumber AND description != '' LIMIT 1";
            $affectedDtoDescription = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => '%'.$affectedDto.'%'])['data'][0]['description'] ?? [];
            $affectedDtoDescription = $this->formatDescription($affectedDtoDescription, 5);

            $affectedDtoResponseObj[$affectedDto] = $affectedDtoDescription;
        }

        echo json_encode($affectedDtoResponseObj);
        exit();
    }

    public function updateMinusPriceDtoChange()
    {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Update Minus Price DTO Change Request | " . implode('|', $_POST));
        Journals::saveJournal("PROCESSING | Update Minus Price DTO Change Request | " . implode('|', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Update Minus Price DTO Change");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $dtoNumber = $_POST['dtoNumber'];
        $description = $_POST['description'];
        $typicalNo = $_POST['typicalNo'];
        $wontBeProduced = $_POST['wontBeProduced'] === 'true' ? 1 : 0;
        $isRevisionNachbau = $_POST['isRevisionNachbau'];

        $query = "SELECT position, kmat, qty, unit, kmat_name, parent_kmat, typical_no, ortz_kz, panel_no
                    FROM nachbau_datas
                    WHERE project_no = :pNo
                      AND nachbau_no = :nNo
                      AND typical_no = :typicalNo
                      AND kmat NOT LIKE '003003%' 
                      AND kmat NOT LIKE '003013%'
                      AND kmat <> ''";

        $data = DbManager::fetchPDOQuery('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':typicalNo' => $typicalNo])['data'] ?? [];

        $query = "SELECT id FROM project_works_minus_price 
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNo AND dto_typical_number = :typicalNo AND minus_price_remove_type = 1";

        $existingMinPriData = DbManager::fetchPDOQuery('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNo' => $this->formatDtoNumber($dtoNumber), ':typicalNo' => $typicalNo])['data'] ?? [];

        if (empty($existingMinPriData) && $wontBeProduced === 1) {
            $query = "INSERT INTO project_works_minus_price(project_number, nachbau_number, dto_number, dto_description, dto_typical_number, wont_be_produced, minus_price_remove_type, material_deleted_starts_by, material_deleted_number, material_deleted_description, parent_kmat, ortz_kz, panel_no, position, quantity, unit, is_revision_change, send_to_review_by, publish_by)";

            foreach($data as $row) {
                $parameters[] = [
                    $projectNo,
                    $nachbauNo,
                    $this->formatDtoNumber($dtoNumber),
                    $this->formatDescription($description, 4),
                    $typicalNo,
                    $wontBeProduced,
                    1,
                    $this->getSapMaterialPrefixByMaterialNo(preg_replace('/^00/', '', $row['kmat'])),
                    preg_replace('/^00/', '', $row['kmat']),
                    $row['kmat_name'],
                    ltrim($row['parent_kmat'], '0'),
                    $row['ortz_kz'],
                    $row['panel_no'],
                    $row['position'],
                    $row['qty'],
                    $row['unit'],
                    $isRevisionNachbau,
                    SharedManager::$fullname,
                    NULL
                ];
            }

            DbManager::fetchInsert('dto_configurator', $query, $parameters);

            SharedManager::saveLog('log_dtoconfigurator',"CREATED | Minus Price DTO Change Lists Inserted Successfully | " . implode('|', $_POST));
            Journals::saveJournal("CREATED | Minus Price DTO Change Lists Inserted Successfully | " . implode('|', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Insert Minus Price DTO Change");

        } else {
            $existingIds = array_column($existingMinPriData, 'id');

            $query = "UPDATE project_works_minus_price SET wont_be_produced = :wbp AND minus_price_remove_type = 1 WHERE id IN (:ids)";
            DbManager::fetchPDOQuery('dto_configurator', $query, [':wbp' => $wontBeProduced, ':ids' => $existingIds])['data'] ?? [];

            SharedManager::saveLog('log_dtoconfigurator',"UPDATED | Minus Price DTO Change Wont Be Produced Updated Successfully | " . implode('|', $_POST));
            Journals::saveJournal("UPDATED | Minus Price DTO Change Wont Be Produced Updated Successfully Successfully | " . implode('|', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Update Minus Price DTO Change");
        }
    }

    public function getMinusPriceDtoStatus(): void
    {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $dtoNumber = $_GET['dtoNumber'];
        $typicalNo = $_GET['typicalNo'];
        $minusPriceDtoChangeExists = false;

        // Daha önce bu dto eklenmiş mi
        $query = "SELECT wont_be_produced FROM project_works_minus_price 
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNo AND dto_typical_number = :typicalNo AND wont_be_produced = 1";

        $data = DbManager::fetchPDOQuery('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNo' => $this->formatDtoNumber($dtoNumber), ':typicalNo' => $typicalNo])['data'] ?? [];

        if (!empty($data))
            $minusPriceDtoChangeExists = true;

        echo json_encode($minusPriceDtoChangeExists); exit();
    }

    public function getJTTypicalAndPanels(): void {
        $projectNo = $_GET['projectNo'];

        $query = "SELECT nachbau_no, typical_no, ortz_kz, panel_no
                    FROM nachbau_datas 
                    WHERE project_no=:pNo
                    AND ortz_kz != ''
                    GROUP BY nachbau_no, typical_no, ortz_kz
                    ORDER BY nachbau_no DESC";

        $nachbauData = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo])['data'] ?? [];

        $structuredResponse = [];
        foreach ($nachbauData as $row) {
            $nachbauNo = $row['nachbau_no'];
            $typicalNo = $row['typical_no'];
            $ortzKz = $row['ortz_kz'];
            $panelNo = $row['panel_no'];

            if (!isset($structuredResponse[$nachbauNo])) {
                $structuredResponse[$nachbauNo] = [
                    'Typical' => [],
                    'Panel' => [],
                ];
            }

            if (!isset($structuredResponse[$nachbauNo]['Typical'][$typicalNo])) {
                $structuredResponse[$nachbauNo]['Typical'][$typicalNo] = [];
            }

            if (!isset($structuredResponse[$nachbauNo]['Typical'][$typicalNo][$ortzKz])) {
                $structuredResponse[$nachbauNo]['Typical'][$typicalNo][$ortzKz] = [
                    'panel_no' => $panelNo
                ];
            }

            if (!isset($structuredResponse[$nachbauNo]['Panel'][$ortzKz])) {
                $structuredResponse[$nachbauNo]['Panel'][$ortzKz] = [
                    'panel_no' => $panelNo,
                    'typical_no' => $typicalNo
                ];
            }
        }

        echo json_encode($structuredResponse);
        exit();
    }

    public function getMinusPriceDtoNachbauListOfProject() {

        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $typicalNo = $_GET['typicalNo'];

        $query = "SELECT Id as id, position, kmat as material_deleted_number, qty as quantity, unit,
                         kmat_name as material_description, parent_kmat, typical_no, ortz_kz, panel_no 
                    FROM nachbau_datas
                    WHERE project_no = :pNo AND nachbau_no = :nNo AND typical_no = :typicalNo";
        $data = DbManager::fetchPDOQuery('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':typicalNo' => $typicalNo])['data'] ?? [];

        echo json_encode($data);
        exit();
    }

    public function moveMaterialToWontBeProduced():void {
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $rowData = $_POST['rowData'];
        $dtoNumber = $_POST['dtoNumber'];
        $description = $_POST['description'];
        $typicalNo = $_POST['typicalNo'];
        $isRevisionNachbau = $_POST['isRevisionNachbau'];

        $query = "SELECT id FROM project_works_minus_price WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNumber 
                  AND dto_typical_number = :typicalNo AND material_deleted_number = :mDeleted AND minus_price_remove_type = 2 AND wont_be_produced = 1";

        $data = DbManager::fetchPDOQuery('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => $this->formatDtoNumber($dtoNumber) ,':typicalNo' => $typicalNo, ':mDeleted' => ltrim($rowData['material_deleted_number'], '0')])['data'] ?? [];

        if (!empty($data))
            returnHttpResponse('400', 'This material is already deleted from ' . $typicalNo);

        $query = "INSERT INTO project_works_minus_price(project_number, nachbau_number, dto_number, dto_description, dto_typical_number, wont_be_produced, minus_price_remove_type,
                material_deleted_starts_by, material_deleted_number, material_deleted_description, parent_kmat, ortz_kz, panel_no, position, quantity, unit, is_revision_change, send_to_review_by, publish_by)";

        $parameters[] = [
            $projectNo,
            $nachbauNo,
            $this->formatDtoNumber($dtoNumber),
            $this->formatDescription($description, 4),
            $typicalNo,
            1,
            2,
            $this->getSapMaterialPrefixByMaterialNo(preg_replace('/^00/', '', $rowData['material_deleted_number'])),
            preg_replace('/^00/', '', $rowData['material_deleted_number']),
            $rowData['material_deleted_description'],
            ltrim($rowData['parent_kmat'], '0'),
            $rowData['ortz_kz'],
            $rowData['panel_no'],
            $rowData['position'],
            $rowData['quantity'],
            $rowData['unit'],
            $isRevisionNachbau,
            SharedManager::$fullname,
            NULL
        ];

        $response_insert = DbManager::fetchInsert('dto_configurator', $query, $parameters);
        $lastInsertedMinPriId = $response_insert["pdoConnection"]->lastInsertId();
        echo json_encode($lastInsertedMinPriId);
        exit();
    }

    public function getExistingMinusPriceWontBeProducedItems():void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $dtoNumber = $this->formatDtoNumber($_GET['dtoNumber']);
        $typicalNo = $_GET['typicalNo'];

        $query = "SELECT id, material_deleted_number, material_deleted_description, quantity, unit FROM project_works_minus_price
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNumber 
                  AND dto_typical_number = :typicalNo AND minus_price_remove_type = 2 AND wont_be_produced = 1";

        $data = DbManager::fetchPDOQuery('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => $dtoNumber ,':typicalNo' => $typicalNo])['data'] ?? [];
        echo json_encode($data);
        exit();
    }

    public function removeMinusPriceWork(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Delete Material Request For Minus Price Work With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Delete Material Request For Minus Price Work With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Delete Minus Price Work");

        $minusPriceWorkId = $_POST['minusPriceWorkId'];

        $query = "SELECT id FROM project_works_minus_price WHERE id = :id";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $minusPriceWorkId])['data'][0] ?? [];

        if (empty($result))
            returnHttpResponse(404, "Minus price work could not found in DB!");

        $query = "UPDATE project_works_minus_price SET wont_be_produced = 0 WHERE id = :id";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':id' => $minusPriceWorkId]);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Minus Price Work Deleted Successfully With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Minus Price Work Deleted Successfully With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_DELETED, implode(' | ', $_POST), "Delete Minus Price Work");
    }

    public function checkIfThereIsPossibleMissingMaterialLists() {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];

        $query = "SELECT dto_number, material_added_number, material_deleted_number, release_item, work_content 
                  FROM order_changes 
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND parent_kmat LIKE '%|%'";

        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];

        echo json_encode($result);
        exit();
    }

    public function getReleasedOrderChangesForAKDImportByProjectNo($projectNo, $nachbauNo) {
        $xmlChangesOutput = [];

        $query = "SELECT operation, release_type, release_item, release_quantity, parent_kmat, work_center, work_content, affected_dto_numbers,
                         material_added_starts_by, material_added_number, material_added_sap_number, material_deleted_starts_by, material_deleted_number, material_deleted_sap_number, is_revision_change
                  FROM order_changes
                  WHERE project_number=:pNo AND nachbau_number=:nNo";
        $orderChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];


        //  - Batch get all unique CTH/VTH SAP materials if exists
        $cthVthMlfbs = [];
        foreach ($orderChanges as $row) {
            if($row['material_deleted_starts_by'] === ':: CTH:' || $row['material_deleted_starts_by'] === ':: VTH:') {
                $cthVthMlfbs[] = $row['material_deleted_number'];
            }
            if($row['material_added_starts_by'] === ':: CTH:' || $row['material_added_starts_by'] === ':: VTH:') {
                $cthVthMlfbs[] = $row['material_added_number'];
            }
        }

        // Get unique MLFBs and batch lookup their SAP numbers
        $uniqueMlfbs = array_unique($cthVthMlfbs);
        $mlfbToSapMap = [];
        foreach ($uniqueMlfbs as $mlfb) {
            $mlfbToSapMap[$mlfb] = $this->getSapMaterialNumberOfMlfb($mlfb);
        }

        foreach ($orderChanges as $row) {
            $item = [
                'nachbau_row_type' => $row['release_type'],
                'operation' => $row['operation'],
                'release_type' => $row['release_type'],
                'release_item' => $row['release_item'],
                'release_quantity' => $row['release_quantity'],
                'parent_kmat' => $row['parent_kmat'],
                'material_added_starts_by' => $row['material_added_starts_by'],
                'material_added_number' => $row['material_added_number'],
                'material_added_sap_number' => $row['material_added_sap_number'],
                'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                'material_deleted_number' => $row['material_deleted_number'],
                'material_deleted_sap_number' => $row['material_deleted_sap_number'],
                'work_center' => $row['work_center'],
                'work_content' => $row['work_content'],
                'affected_dto_numbers' => $row['affected_dto_numbers'],
                'is_revision_change' => $row['is_revision_change']
            ];

            if($row['material_deleted_starts_by'] === ':: CTH:' || $row['material_deleted_starts_by'] === ':: VTH:') {
                // BOM'daki replace veya silme işleminde CTH/VTH kablo kiti bulunuyorsa, MLFB sinden malzeme numarası getirilir. TBC ekibi AKD Import'ta önemli olan Malzeme Nosu.
                $cableCodeMlfb = $row['material_deleted_number'];
                $item['material_deleted_cable_code'] = $cableCodeMlfb;

                // Use cached SAP number instead of calling the method again
                $cableCodeSapNo = $mlfbToSapMap[$cableCodeMlfb] ?? $item['material_deleted_sap_number'];
                if (empty($cableCodeSapNo)) {
                    returnHttpResponse('500', $cableCodeMlfb . ' VTH/CTH kodunun SAP karşılığı bulunamadı!');
                }

                $item['material_deleted_number'] = $cableCodeSapNo;
            }

            if($row['material_added_starts_by'] === ':: CTH:' || $row['material_added_starts_by'] === ':: VTH:') {
                // BOM'daki replace veya silme işleminde CTH/VTH kablo kiti bulunuyorsa, MLFB sinden malzeme numarası getirilir. TBC ekibi AKD Import'ta önemli olan Malzeme Nosu.
                $cableCodeMlfb = $row['material_added_number'];
                $item['material_added_cable_code'] = $cableCodeMlfb;

                // Use cached SAP number instead of calling the method again
                $cableCodeSapNo = $mlfbToSapMap[$cableCodeMlfb] ?? $item['material_added_sap_number'];
                if (empty($cableCodeSapNo)) {
                    returnHttpResponse('500', $cableCodeMlfb . ' VTH/CTH kodunun SAP karşılığı bulunamadı!');
                }

                $item['material_added_number'] = $cableCodeSapNo;
            }

            $xmlChangesOutput[] = $item;
        }

        //SPARE CHANGES FOR AKD IMPORT
        $query = "SELECT accessory_typical_number, release_quantity, accessory_parent_kmat, material_added_starts_by, material_added_number, is_revision_change
                  FROM project_works_spare
                  WHERE project_number=:pNo AND nachbau_number=:nNo AND deleted IS NULL";
        $spareChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];

        foreach ($spareChanges as $row) {
            $item = [
                'nachbau_row_type' => 'Accessories',
                'operation' => 'add',
                'release_type' => 'Accessories',
                'release_item' => $row['accessory_typical_number'],
                'release_quantity' => $row['release_quantity'],
                'parent_kmat' => $row['accessory_parent_kmat'],
                'material_added_starts_by' => $row['material_added_starts_by'],
                'material_added_number' => $row['material_added_number'],
                'material_deleted_starts_by' => '',
                'material_deleted_number' => '',
                'is_revision_change' => $row['is_revision_change']
            ];

            $xmlChangesOutput[] = $item;
        }

        //NACHBAU ERROR CHANGES FOR AKD IMPORT
        $query = "SELECT operation, ortz_kz as release_item, quantity as release_quantity, parent_kmat, material_added_starts_by, material_added_number, material_deleted_starts_by, material_deleted_number, is_revision_change
                  FROM project_works_nachbau_errors
                  WHERE project_number=:pNo AND nachbau_number=:nNo AND deleted IS NULL";
        $nachbauErrorChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];

        foreach ($nachbauErrorChanges as $row) {
            $item = [
                'nachbau_row_type' => 'Panel',
                'operation' => $row['operation'],
                'release_type' => 'Panel',
                'release_item' => $row['release_item'],
                'release_quantity' => $row['release_quantity'],
                'parent_kmat' => $row['parent_kmat'],
                'material_added_starts_by' => $row['material_added_starts_by'],
                'material_added_number' => $row['material_added_number'],
                'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                'material_deleted_number' => $row['material_deleted_number'],
                'is_revision_change' => $row['is_revision_change']
            ];

            $xmlChangesOutput[] = $item;
        }

        //MINUS CHANGES FOR AKD IMPORT
        $query = "SELECT ortz_kz as release_item, quantity as release_quantity, parent_kmat, material_deleted_starts_by, material_deleted_number, is_revision_change 
                  FROM project_works_minus_price 
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND wont_be_produced = 1";
        $minusPriceChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

        foreach ($minusPriceChanges as $row) {
            $item = [
                'nachbau_row_type' => 'Panel',
                'operation' => 'delete',
                'release_type' => 'Panel',
                'release_item' => $row['release_item'],
                'release_quantity' => $row['release_quantity'],
                'parent_kmat' => $row['parent_kmat'],
                'material_added_starts_by' => '',
                'material_added_number' => '',
                'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                'material_deleted_number' => $row['material_deleted_number'],
                'is_revision_change' => $row['is_revision_change']
            ];

            $xmlChangesOutput[] = $item;
        }

        //EXTENSION CHANGES FOR AKD IMPORT
        $query = "SELECT operation, is_accessory, typical_no, ortz_kz, quantity as release_quantity, material_added_starts_by, material_added_number, material_deleted_starts_by, material_deleted_number, parent_kmat, sub_kmat, is_revision_change 
                  FROM project_works_extensions WHERE project_number = :pNo AND nachbau_number = :nNo AND deleted IS NULL";
        $extensionChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

        foreach ($extensionChanges as $row) {
            if ($row['is_accessory'] === '1') {
                $releaseType = 'Typical';
                $releaseItem = $row['typical_no'];
                $parentKmat = $row['parent_kmat'];
            } else {
                $releaseType = 'Panel';
                $releaseItem = $row['ortz_kz'];
                $parentKmat = !empty($row['sub_kmat']) ? $row['sub_kmat'] : $row['parent_kmat'];
            }

            $item = [
                'nachbau_row_type' => $releaseType,
                'operation' => $row['operation'],
                'release_type' => $releaseType,
                'release_item' => $releaseItem,
                'release_quantity' => $row['release_quantity'],
                'parent_kmat' => $parentKmat,
                'material_added_starts_by' => $row['material_added_starts_by'],
                'material_added_number' => $row['material_added_number'],
                'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                'material_deleted_number' => $row['material_deleted_number'],
                'is_revision_change' => $row['is_revision_change']
            ];

            $xmlChangesOutput[] = $item;
        }

        //INTERCHANGIBILITY CHANGES FOR AKD IMPORT
        $query = "SELECT operation, typical_no as release_item, quantity as release_quantity, parent_kmat, material_added_starts_by, material_added_number, material_deleted_starts_by, material_deleted_number, is_revision_change
                  FROM project_works_interchange WHERE project_number = :pNo AND nachbau_number = :nNo AND deleted IS NULL";
        $interchangeDtoChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

        foreach ($interchangeDtoChanges as $row) {
            $item = [
                'nachbau_row_type' => 'Typical',
                'operation' => $row['operation'],
                'release_type' => 'Typical',
                'release_item' => $row['release_item'],
                'release_quantity' => $row['release_quantity'],
                'parent_kmat' => $row['parent_kmat'],
                'material_added_starts_by' => $row['material_added_starts_by'],
                'material_added_number' => $row['material_added_number'],
                'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                'material_deleted_number' => $row['material_deleted_number'],
                'is_revision_change' => $row['is_revision_change'],
            ];

            $xmlChangesOutput[] = $item;
        }


        //SPECIAL DTO CHANGES FOR AKD IMPORT
        $query = "SELECT operation, accessory_typical_number, accessory_parent_kmat, ortz_kz as release_item, wda_parent_kmat, release_quantity, material_number, is_revision_change
                 FROM project_works_special_dtos WHERE project_number = :pNo AND nachbau_number = :nNo AND deleted IS NULL";
        $specialDtoChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

        foreach ($specialDtoChanges as $row) {
            if ($row['is_accessory'] === '1') {
                $releaseType = 'Typical';
                $releaseItem = $row['accessory_typical_number'];
                $parentKmat = $row['accessory_parent_kmat'];
                $materialAddedStartsBy = 'A7E00';
                $materialAdded = $row['material_number'];
                $materialDeletedStartsBy = '';
                $materialDeleted = '';
            } else {
                $releaseType = 'Panel';
                $releaseItem = $row['release_item'];
                $parentKmat = $row['wda_parent_kmat'];
                $materialAddedStartsBy = '';
                $materialAdded = '';
                $materialDeletedStartsBy = 'A7E00';
                $materialDeleted = $row['material_number'];
            }

            $item = [
                'nachbau_row_type' => $releaseType,
                'operation' => $row['operation'],
                'release_type' => $releaseType,
                'release_item' => $releaseItem,
                'release_quantity' => $row['release_quantity'],
                'parent_kmat' => $parentKmat,
                'material_added_starts_by' => $materialAddedStartsBy,
                'material_added_number' => $materialAdded,
                'material_deleted_starts_by' => $materialDeletedStartsBy,
                'material_deleted_number' => $materialDeleted,
                'is_revision_change' => $row['is_revision_change']
            ];

            $xmlChangesOutput[] = $item;
        }

        return $xmlChangesOutput;
    }

    public function getReleasedOrderChangesForAKDImportById($releasedProjectId) {
        $xmlChangesOutput = [];

        $query = "SELECT * FROM view_released_order_changes WHERE released_project_id = :releasedProjectId";
        $releasedOrderChanges = DbManager::fetchPDOQueryData('dto_configurator', $query,[':releasedProjectId' => $releasedProjectId])['data'] ?? [];

        if (count($releasedOrderChanges) === 0) {
            returnHttpResponse(500, 'No order change has been found for this project.');
        }

        foreach ($releasedOrderChanges as $row) {
            //STANDARD CHANGES
            if($row['released_dto_type_id'] === '1') {
                $item = [
                    'nachbau_row_type' => 'Panel',
                    'operation' => $row['operation'],
                    'release_type' => $row['release_type'],
                    'release_item' => $row['ortz_kz'], // Tipik bazlı çalışılsa da, released order changes view'ında pano pano insert edildiği için ortz kz, release item olarak alınır.
                    'typical_no' => $row['typical_no'],
                    'ortz_kz' => $row['ortz_kz'],
                    'panel_no' => $row['panel_no'],
                    'release_quantity' => $row['release_quantity'],
                    'parent_kmat' => $row['parent_kmat'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number'],
                    'work_center' => $row['work_center'],
                    'work_content' => $row['work_content'],
                    'affected_dto_numbers' => $row['affected_dto_numbers']
                ];

                if($row['material_deleted_starts_by'] === ':: CTH:' || $row['material_deleted_starts_by'] === ':: VTH:') {
                    // BOM'daki replace veya silme işleminde CTH/VTH kablo kiti bulunuyorsa, MLFB sinden malzeme numarası getirilir. TBC ekibi AKD Import'ta önemli olan Malzeme Nosu.
                    $cableCodeMlfb = $row['material_deleted_number'];
                    $item['material_deleted_cable_code'] = $cableCodeMlfb;

                    $cableCodeSapNo = $this->getSapMaterialNumberOfMlfb($cableCodeMlfb);
                    if (empty($cableCodeSapNo)) {
                        returnHttpResponse('500', $cableCodeMlfb . ' VTH/CTH kodunun SAP karşılığı bulunamadı!');
                    }

                    $item['material_deleted_number'] = $cableCodeSapNo;
                }

                $xmlChangesOutput[] = $item;
            }

            //SPARE CHANGES
            else if ($row['released_dto_type_id'] === '2') {
                $item = [
                    'nachbau_row_type' => 'Accessories',
                    'operation' => $row['operation'],
                    'release_type' => $row['release_type'],
                    'release_item' => $row['typical_no'],
                    'release_quantity' => $row['release_quantity'],
                    'parent_kmat' => $row['parent_kmat'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number']
                ];

                $xmlChangesOutput[] = $item;
            }
            // EXTENSION CHANGES
            else if ($row['released_dto_type_id'] === '3') {
                if ($row['is_accessory'] === '1') {
                    $nachbauRowType = 'Accessories';
                    $releaseItem = $row['typical_no'];
                    $ortzKz = '';
                    $typicalNo = '';
                    $panelNo = '';
                } else {
                    $nachbauRowType = 'Panel';
                    $releaseItem = $row['ortz_kz'];
                    $ortzKz = $row['ortz_kz'];
                    $typicalNo = $row['typical_no'];
                    $panelNo = $row['panel_no'];
                }

                $item = [
                    'nachbau_row_type' => $nachbauRowType,
                    'operation' => $row['operation'],
                    'release_type' => $row['release_type'],
                    'release_item' => $releaseItem,
                    'typical_no' => $typicalNo,
                    'ortz_kz' => $ortzKz,
                    'panel_no' => $panelNo,
                    'release_quantity' => $row['release_quantity'],
                    'parent_kmat' => $row['parent_kmat'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number']
                ];

                $xmlChangesOutput[] = $item;
            }
            // MINUS PRICE CHANGES
            else if ($row['released_dto_type_id'] === '4') {
                $item = [
                    'nachbau_row_type' => 'Panel',
                    'release_item' => $row['ortz_kz'],
                    'operation' => $row['operation'],
                    'release_type' => $row['release_type'],
                    'typical_no' => $row['typical_no'],
                    'ortz_kz' => $row['ortz_kz'],
                    'panel_no' => $row['panel_no'],
                    'release_quantity' => $row['release_quantity'],
                    'parent_kmat' => $row['parent_kmat'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number']
                ];

                $xmlChangesOutput[] = $item;
            }
            // NACHBAU ERRORS
            else if ($row['released_dto_type_id'] === '5') {
                $item = [
                    'nachbau_row_type' => 'Panel',
                    'operation' => $row['operation'],
                    'release_type' => $row['release_type'],
                    'release_item' => $row['ortz_kz'],
                    'release_quantity' => $row['release_quantity'],
                    'typical_no' => $row['typical_no'],
                    'ortz_kz' => $row['ortz_kz'],
                    'panel_no' => $row['panel_no'],
                    'parent_kmat' => $row['parent_kmat'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number']
                ];

                $xmlChangesOutput[] = $item;
            }
            // INTERCHANGIBILITY CHANGES
            else if ($row['released_dto_type_id'] === '6') {
                $item = [
                    'nachbau_row_type' => 'Panel',
                    'operation' => $row['operation'],
                    'release_type' => $row['release_type'],
                    'release_item' => $row['ortz_kz'],
                    'release_quantity' => $row['release_quantity'],
                    'typical_no' => $row['typical_no'],
                    'ortz_kz' => $row['ortz_kz'],
                    'panel_no' => $row['panel_no'],
                    'parent_kmat' => $row['parent_kmat'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number']
                ];

                $xmlChangesOutput[] = $item;
            }
            // SPECIAL DTO CHANGES
            else if ($row['released_dto_type_id'] === '7') {
                if ($row['is_accessory'] === '1') {
                    $nachbauRowType = 'Typical';
                    $releaseItem = $row['typical_no'];
                    $typicalNo = '';
                    $ortzKz = '';
                    $panelNo = '';
                } else {
                    $nachbauRowType = 'Panel';
                    $releaseItem = $row['ortz_kz'];
                    $typicalNo = $row['typical_no'];
                    $ortzKz = $row['ortz_kz'];
                    $panelNo = $row['panel_no'];
                }

                $item = [
                    'nachbau_row_type' => $nachbauRowType,
                    'operation' => $row['operation'],
                    'release_type' => $row['release_type'],
                    'release_item' => $releaseItem,
                    'typical_no' => $typicalNo,
                    'ortz_kz' => $ortzKz,
                    'panel_no' => $panelNo,
                    'release_quantity' => $row['release_quantity'],
                    'parent_kmat' => $row['parent_kmat'],
                    'material_added_starts_by' => $row['material_added_starts_by'],
                    'material_added_number' => $row['material_added_number'],
                    'material_deleted_starts_by' => $row['material_deleted_starts_by'],
                    'material_deleted_number' => $row['material_deleted_number']
                ];

                $xmlChangesOutput[] = $item;
            }
        }

        if (count($xmlChangesOutput) > 0)
            $xmlChangesOutput = $this->deduplicateOrderChanges($xmlChangesOutput);

        return $xmlChangesOutput;
    }

    public function generateAKDImportXmlFile() {
        $xmlOperation = $_POST['operation'] ?? null;
        $page = $_POST['page'] ?? null;
        $isAdminPage = ($page === 'admin');

        // Get project details and order changes based on page type
        if ($isAdminPage) {
            $releasedProjectId = $_POST['releasedProjectId'];
            $query = "SELECT project_number, nachbau_number FROM view_released_projects WHERE released_project_id = :releasedProjectId";
            $releasedProjectDetails = DbManager::fetchPDOQueryData('dto_configurator', $query,[':releasedProjectId' => $releasedProjectId])['data'][0] ?? [];

            $projectNo = $releasedProjectDetails['project_number'];
            $nachbauNo = $releasedProjectDetails['nachbau_number'];
            $orderChanges = $this->getReleasedOrderChangesForAKDImportById($releasedProjectId);
        } else {
            $projectNo = $_POST['projectNo'] ?? null;
            $nachbauNo = $_POST['nachbauNo'] ?? null;
            $orderChanges = $this->getReleasedOrderChangesForAKDImportByProjectNo($projectNo, $nachbauNo);
        }

        // Yayın onayı olan siparişte herhangi bir değişiklik yoksa AKD XML dosyası oluşmamalı. Bazen ürün yönetimi sadece not olarak yayınlıyor. DTOlu değişiklik olmuyor. O durum için.
        if (empty($orderChanges)) {
            return [
                'success' => true,
                'message' => 'No order changes available, so AKD Import XML file generation was skipped.',
                'file_path' => '',
                'file_name' => ''
            ];
        }

        $typicalToPanelsMap = $this->getTypicalAndPanelsDictionary($projectNo, $nachbauNo);

        $query = "SELECT DISTINCT material_number, sap_material_number FROM materials WHERE material_number IS NOT NULL";
        $dtoConfMaterials = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'];
        $materialsSapNumbersMap = [];
        foreach ($dtoConfMaterials as $row) {
            $materialsSapNumbersMap[$row['material_number']] = $row['sap_material_number'];
        }

        // Add query cache before the main loop
        $parentKmatLookups = [];
        $xmlDataOutput = [];

        foreach ($orderChanges as $change) {
            if ($change['nachbau_row_type'] !== 'Panel') {
                $typical = $change['release_item'];

                if (isset($typicalToPanelsMap[$typical])) {
                    foreach ($typicalToPanelsMap[$typical] as $panelData) {
                        // Only need to lookup for these specific conditions
                        if (!$isAdminPage && $change['operation'] !== 'add' &&
                            $change['material_deleted_starts_by'] !== ':: CTH:' &&
                            $change['material_deleted_starts_by'] !== ':: VTH:') {

                            $parentKmatLookups[] = [
                                'typical' => $typical,
                                'ortz_kz' => $panelData['ortz_kz'],
                                'panel_no' => $panelData['panel_no'],
                                'material' => $change['material_deleted_number']
                            ];
                        }
                    }
                }
            }
        }

        // Now fetch ALL parent_kmats in one or a few queries
        $parentKmatQueryCache = [];
        if (!empty($parentKmatLookups)) {
            // Group by material to reduce query complexity
            $materialGroups = [];
            foreach ($parentKmatLookups as $lookup) {
                $materialGroups[$lookup['material']][] = $lookup;
            }

            foreach ($materialGroups as $material => $lookups) {
                // Fetch all parent_kmats for this material across all panels at once.
                $query = "SELECT parent_kmat, typical_no, ortz_kz, panel_no 
                  FROM nachbau_datas 
                  WHERE project_no = :pNo 
                  AND nachbau_no = :nNo 
                  AND kmat LIKE :kmat";

                $allResults = DbManager::fetchPDOQueryData('planning', $query, [
                    ':pNo' => $projectNo,
                    ':nNo' => $nachbauNo,
                    ':kmat' => '%' . $material
                ])['data'];

                // Cache the results by lookup key
                foreach ($allResults as $row) {
                    $cacheKey = "{$projectNo}|{$nachbauNo}|{$row['typical_no']}|{$row['ortz_kz']}|{$row['panel_no']}|{$material}";

                    if (!isset($parentKmatQueryCache[$cacheKey])) {
                        $parentKmatQueryCache[$cacheKey] = [];
                    }

                    $cleanParentKmat = preg_replace('/^00/', '', $row['parent_kmat']);
                    $parentKmatQueryCache[$cacheKey][] = $cleanParentKmat;
                }
            }
        }

        $hasAffectedDtos = false;
        foreach ($orderChanges as $change) {

            // affected_dto_numbers kontrolü - sadece bir kere set et (siparişte var mı kontrolü)
            if (!$hasAffectedDtos && !empty($change['affected_dto_numbers'])) {
                $hasAffectedDtos = true;
            }

            // PANEL CHANGES
            if ($change['nachbau_row_type'] === 'Panel') {
                if ($isAdminPage) {
                    // Admin: Use typical_no, ortz_kz, panel_no from change directly
                    $sap_pos_no = null;
                    if (isset($typicalToPanelsMap[$change['typical_no']])) {
                        foreach ($typicalToPanelsMap[$change['typical_no']] as $panel) {
                            if ($panel['ortz_kz'] === $change['ortz_kz'] && $panel['panel_no'] === $change['panel_no']) {
                                $sap_pos_no = $panel['sap_pos_no'];
                                break;
                            }
                        }
                    }

                    $xmlDataOutput[] = [
                        'operation' => $change['operation'],
                        'typical_no' => $change['typical_no'],
                        'ortz_kz' => $change['ortz_kz'],
                        'panel_no' => $change['panel_no'],
                        'sap_pos_no' => $sap_pos_no,
                        'release_quantity' => preg_replace('/\.000$/', '', $change['release_quantity']),
                        'parent_kmat' => $change['parent_kmat'],
                        'material_added_starts_by' => $change['material_added_starts_by'] ?? '',
                        'material_added_number' => $change['material_added_number'] ?? '',
                        'material_added_cable_code' => $change['material_added_cable_code'] ?? '',
                        'material_deleted_starts_by' => $change['material_deleted_starts_by'] ?? '',
                        'material_deleted_number' => $change['material_deleted_number'] ?? '',
                        'material_deleted_cable_code' => $change['material_deleted_cable_code'] ?? '',
                        'work_center' => $change['work_center'],
                        'work_content' => $change['work_content'],
                        'affected_dto_numbers' => $change['affected_dto_numbers']
                    ];

                } else {
                    // Non-admin: Use release_item as ortz_kz and find matching panels
                    $ortzKz = $change['release_item'];

                    foreach ($typicalToPanelsMap as $typical => $panels) {
                        foreach ($panels as $panelData) {
                            if ($panelData['ortz_kz'] === $ortzKz) {
                                $xmlDataOutput[] = [
                                    'operation' => $change['operation'],
                                    'typical_no' => $typical,
                                    'ortz_kz' => $ortzKz,
                                    'panel_no' => $panelData['panel_no'],
                                    'sap_pos_no' => $panelData['sap_pos_no'],
                                    'release_quantity' => preg_replace('/\.000$/', '', $change['release_quantity']),
                                    'parent_kmat' => $change['parent_kmat'],
                                    'material_added_starts_by' => $change['material_added_starts_by'] ?? '',
                                    'material_added_number' => $change['material_added_number'] ?? '',
                                    'material_added_cable_code' => $change['material_added_cable_code'] ?? '',
                                    'material_deleted_starts_by' => $change['material_deleted_starts_by'] ?? '',
                                    'material_deleted_number' => $change['material_deleted_number'] ?? '',
                                    'material_deleted_cable_code' => $change['material_deleted_cable_code'] ?? '',
                                    'work_center' => $change['work_center'],
                                    'work_content' => $change['work_content'],
                                    'affected_dto_numbers' => $change['affected_dto_numbers']
                                ];
                            }
                        }
                    }
                }

            }
            else {
                // NON-PANEL CHANGES (same logic for both admin and non-admin)
                $typical = $change['release_item'];

                if (isset($typicalToPanelsMap[$typical])) {
                    foreach ($typicalToPanelsMap[$typical] as $panelData) {
                        $parentKmats = [$change['parent_kmat']];

                        // Handle multiple parent kmats for deletions (except for admin page or specific conditions)
                        if (!$isAdminPage && $change['operation'] !== 'add' &&
                            $change['material_deleted_starts_by'] !== ':: CTH:' &&
                            $change['material_deleted_starts_by'] !== ':: VTH:') {

                            // USE PRE-FETCHED CACHE
                            $cacheKey = "{$projectNo}|{$nachbauNo}|{$typical}|{$panelData['ortz_kz']}|{$panelData['panel_no']}|{$change['material_deleted_number']}";

                            if (isset($parentKmatQueryCache[$cacheKey])) {
                                $parentKmats = $parentKmatQueryCache[$cacheKey];
                            } else {
                                // If no parent_kmats found for delete/replace operation, skip this panel
                                // This means the material to be deleted doesn't exist in this panel
                                $parentKmats = [];
                            }
                        }

                        // IMPORTANT: Skip this panel if it's a delete/replace operation but material doesn't exist
                        if (($change['operation'] === 'delete' || $change['operation'] === 'replace') && empty($parentKmats)) {
                            continue; // Skip to next panel
                        }

                        // Create an item for each parent_kmat
                        foreach ($parentKmats as $parentKmat) {
                            $xmlDataOutput[] = [
                                'operation' => $change['operation'],
                                'typical_no' => $typical,
                                'ortz_kz' => $panelData['ortz_kz'],
                                'panel_no' => $panelData['panel_no'],
                                'sap_pos_no' => $panelData['sap_pos_no'],
                                'release_quantity' => preg_replace('/\.000$/', '', $change['release_quantity']),
                                'parent_kmat' => $parentKmat,
                                'material_added_starts_by' => $change['material_added_starts_by'] ?? '',
                                'material_added_number' => $change['material_added_number'] ?? '',
                                'material_added_cable_code' => $change['material_added_cable_code'] ?? '',
                                'material_deleted_starts_by' => $change['material_deleted_starts_by'] ?? '',
                                'material_deleted_number' => $change['material_deleted_number'] ?? '',
                                'material_deleted_cable_code' => $change['material_deleted_cable_code'] ?? '',
                                'work_center' => $change['work_center'],
                                'work_content' => $change['work_content'],
                                'affected_dto_numbers' => $change['affected_dto_numbers']
                            ];
                        }
                    }
                }
            }
        }

        if ($hasAffectedDtos && !$isAdminPage)
            $xmlDataOutput = $this->deduplicateOrderChanges($xmlDataOutput);

        $addMaterials = [];
        foreach ($xmlDataOutput as $entry) {
            if ($entry['operation'] === 'add' && $entry['material_deleted_starts_by'] !== ':: CTH:' && $entry['material_deleted_starts_by'] !== ':: VTH:' && !isset($materialsSapNumbersMap[$entry['material_added_number']])) {
                $addMaterials[] = $entry['material_added_number'];
            }
        }

        $uniqueAddMaterials = array_unique($addMaterials);
        $addMaterialSapMap = [];
        $sapCallCount = 0;
        foreach ($uniqueAddMaterials as $material) {
            $sapCallCount++;
            $addMaterialSapMap[$material] = $this->getFullSapMaterialFrom064($material) ?? 'A7E00';
        }

        // Batch get SAP numbers for regular DELETE operations
        $regularMaterials = [];
        foreach ($xmlDataOutput as $entry) {
            if ($entry['operation'] !== 'add' && $entry['material_deleted_starts_by'] !== ':: CTH:' && $entry['material_deleted_starts_by'] !== ':: VTH:' && !isset($materialsSapNumbersMap[$entry['material_deleted_number']])) {
                $regularMaterials[] = $entry['material_deleted_number'];
            }
        }

        $uniqueRegularMaterials = array_unique($regularMaterials);
        $regularMaterialSapMap = [];
        $sapCallCount = 0;
        foreach ($uniqueRegularMaterials as $material) {
            $sapCallCount++;
            $regularMaterialSapMap[$material] = $this->getFullSapMaterialFrom064($material);
        }

        $cableLookups = [];
        $regularLookups = [];

        foreach ($xmlDataOutput as $entry) {
            if ($entry['operation'] === 'delete' || $entry['operation'] === 'replace') {
                if($entry['material_deleted_starts_by'] === ':: CTH:' || $entry['material_deleted_starts_by'] === ':: VTH:') {
                    $cableLookups[] = [
                        'cable_code' => $entry['material_deleted_cable_code'],
                        'parent_kmat' => $entry['parent_kmat'],
                        'ortz_kz' => $entry['ortz_kz'],
                        'panel_no' => $entry['panel_no'],
                        'cache_key' => "cable_{$projectNo}_{$nachbauNo}_{$entry['material_deleted_cable_code']}_{$entry['parent_kmat']}_{$entry['ortz_kz']}_{$entry['panel_no']}"
                    ];
                } else if($entry['material_added_starts_by'] === ':: CTH:' || $entry['material_added_starts_by'] === ':: VTH:') {
                    $cableLookups[] = [
                        'cable_code' => $entry['material_added_cable_code'],
                        'parent_kmat' => $entry['parent_kmat'],
                        'ortz_kz' => $entry['ortz_kz'],
                        'panel_no' => $entry['panel_no'],
                        'cache_key' => "cable_{$projectNo}_{$nachbauNo}_{$entry['material_added_cable_code']}_{$entry['parent_kmat']}_{$entry['ortz_kz']}_{$entry['panel_no']}"
                    ];
                } else {
                    $regularLookups[] = [
                        'material' => $entry['material_deleted_number'],
                        'parent_kmat' => $entry['parent_kmat'],
                        'ortz_kz' => $entry['ortz_kz'],
                        'panel_no' => $entry['panel_no'],
                        'cache_key' => "regular_{$projectNo}_{$nachbauNo}_{$entry['material_deleted_number']}_{$entry['parent_kmat']}_{$entry['ortz_kz']}_{$entry['panel_no']}"
                    ];
                }
            }
        }

        $queryCache = [];
        if (!empty($cableLookups)) {
            // Group by cable_code to reduce queries
            $cableGroups = [];
            foreach ($cableLookups as $lookup) {
                $cableGroups[$lookup['cable_code']][] = $lookup;
            }

            foreach ($cableGroups as $cableCode => $lookups) {
                $query = "SELECT DISTINCT qty, position_ext, kmat_name, parent_kmat, ortz_kz, panel_no 
                  FROM nachbau_datas 
                  WHERE project_no = :pNo 
                  AND nachbau_no = :nNo 
                  AND kmat_name LIKE :kmatName";

                $results = DbManager::fetchPDOQueryData('planning', $query, [
                    ':pNo' => $projectNo,
                    ':nNo' => $nachbauNo,
                    ':kmatName' => '%' . $cableCode
                ])['data'];

                // Map results to cache keys
                foreach ($results as $row) {
                    // Clean parent_kmat for matching
                    $cleanParentKmat = preg_replace('/^00/', '', $row['parent_kmat']);

                    foreach ($lookups as $lookup) {
                        // Match based on parent_kmat, ortz_kz, and panel_no
                        $lookupParentKmat = preg_replace('/^00/', '', $lookup['parent_kmat']);

                        if ($cleanParentKmat === $lookupParentKmat &&
                            $row['ortz_kz'] === $lookup['ortz_kz'] &&
                            $row['panel_no'] === $lookup['panel_no']) {
                            $queryCache[$lookup['cache_key']] = $row;
                        }
                    }
                }
            }
        }

        // Batch fetch regular material lookups
        if (!empty($regularLookups)) {
            // Group by material to reduce queries
            $materialGroups = [];
            foreach ($regularLookups as $lookup) {
                $materialGroups[$lookup['material']][] = $lookup;
            }

            foreach ($materialGroups as $material => $lookups) {
                $query = "SELECT DISTINCT qty, position_ext, kmat, parent_kmat, ortz_kz, panel_no 
                  FROM nachbau_datas 
                  WHERE project_no = :pNo 
                  AND nachbau_no = :nNo 
                  AND kmat LIKE :kmat";

                $results = DbManager::fetchPDOQueryData('planning', $query, [
                    ':pNo' => $projectNo,
                    ':nNo' => $nachbauNo,
                    ':kmat' => '%' . $material
                ])['data'];

                // Map results to cache keys
                foreach ($results as $row) {
                    // Clean parent_kmat for matching
                    $cleanParentKmat = preg_replace('/^00/', '', $row['parent_kmat']);

                    foreach ($lookups as $lookup) {
                        // Match based on parent_kmat, ortz_kz, and panel_no
                        $lookupParentKmat = preg_replace('/^00/', '', $lookup['parent_kmat']);

                        if ($cleanParentKmat === $lookupParentKmat &&
                            $row['ortz_kz'] === $lookup['ortz_kz'] &&
                            $row['panel_no'] === $lookup['panel_no']) {
                            $queryCache[$lookup['cache_key']] = $row;
                        }
                    }
                }
            }
        }
        $panelsGrouped = [];

        foreach ($xmlDataOutput as $entry) {
            $key = $entry['typical_no'] . '|' . $entry['ortz_kz'] . '|' . $entry['sap_pos_no'];

            if (!isset($panelsGrouped[$key])) {
                $panelsGrouped[$key] = [
                    'PanelPosNo' => $entry['sap_pos_no'],
                    'PanelTypicalName' => $entry['typical_no'],
                    'PanelName' => $entry['ortz_kz'],
                    'Changes' => []
                ];
            }

            $change = [
                'KMAT' => 'A7E00' . $entry['parent_kmat'],
                'REMOVE' => [],
                'ADD' => []
            ];

            //ADDITION OR REPLACE
            if ($entry['operation'] === 'add' || $entry['operation'] === 'replace') {
                if($entry['material_added_starts_by'] === ':: CTH:' || $entry['material_added_starts_by'] === ':: VTH:') {
                    $cacheKey = "cable_{$projectNo}_{$nachbauNo}_{$entry['material_added_cable_code']}_{$entry['parent_kmat']}_{$entry['ortz_kz']}_{$entry['panel_no']}";
                } else {
                    $cacheKey = "regular_{$projectNo}_{$nachbauNo}_{$entry['material_added_number']}_{$entry['parent_kmat']}_{$entry['ortz_kz']}_{$entry['panel_no']}";
                }

                if($entry['material_added_starts_by'] === ':: CTH:' || $entry['material_added_starts_by'] === ':: VTH:') {
                    $materialAddedNumber = $entry['material_added_number'];
                } else {
                    $materialAddedNumber = $materialsSapNumbersMap[$entry['material_added_number']] ?? $regularMaterialSapMap[$entry['material_added_number']] ?? 'A7E00';
                }

                $change['ADD'] = [
                    'MATNR' => $materialAddedNumber,
                    'MENGE' => $entry['release_quantity']
                ];
            }

            // REMOVAL OR REPLACE
            if ($entry['operation'] === 'delete' || $entry['operation'] === 'replace') {
                // CREATE CACHE KEY
                if($entry['material_deleted_starts_by'] === ':: CTH:' || $entry['material_deleted_starts_by'] === ':: VTH:') {
                    $cacheKey = "cable_{$projectNo}_{$nachbauNo}_{$entry['material_deleted_cable_code']}_{$entry['parent_kmat']}_{$entry['ortz_kz']}_{$entry['panel_no']}";
                } else {
                    $cacheKey = "regular_{$projectNo}_{$nachbauNo}_{$entry['material_deleted_number']}_{$entry['parent_kmat']}_{$entry['ortz_kz']}_{$entry['panel_no']}";
                }

                $nachbauPanelData = $queryCache[$cacheKey] ?? null;

                if($entry['material_deleted_starts_by'] === ':: CTH:' || $entry['material_deleted_starts_by'] === ':: VTH:') {
                    $materialDeletedNumber = $entry['material_deleted_number'];

                    if (empty($nachbauPanelData)) {
                        returnHttpResponse('500', $entry['material_deleted_cable_code'] . ' VTH/CTH kodunun aktarımdaki POS değeri bulunamadı!');
                    }
                } else {
                    $materialDeletedNumber = $materialsSapNumbersMap[$entry['material_deleted_number']] ?? $regularMaterialSapMap[$entry['material_deleted_number']] ?? 'A7E00';
                }

                if (!empty($nachbauPanelData)) {
                    $change['REMOVE'] = [
                        'MATNR' => $materialDeletedNumber,
                        'MENGE' => preg_replace('/\.000$/', '', $nachbauPanelData['qty']),
                        'POS' => $nachbauPanelData['position_ext']
                    ];
                }
            }

            $panelsGrouped[$key]['Changes'][] = $change;
        }

        // Son olarak panel pos no ya göre sıralama
        uksort($panelsGrouped, function ($a, $b) {
            $aParts = explode('|', $a);
            $bParts = explode('|', $b);
            return (int)$aParts[2] <=> (int)$bParts[2];
        });

        $this->xmlData = [
            'SalesOrder' => $projectNo,
            'Panels' => array_values($panelsGrouped)
        ];

        require_once "./AKDImportController.php";
        $akdClassInstance = new AKDImportController($this->xmlData);
        $today = date('Y-m-d');
        $fileName = "Delta_{$projectNo}-{$nachbauNo}-{$today}";

        if ($xmlOperation === 'copyToFolder') {
            $destinationFolderPath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\OrderProcessingCenter\\02_OPC_OM_1_2\\Boom_Change\\AKD Import Kontrol\\XML";
            $result = $akdClassInstance->copyToFolder($destinationFolderPath, $fileName);

            header('Content-Type: application/json');
            echo json_encode($result);
        } else {
            $akdClassInstance->download($fileName);
        }

        exit;
    }


    /**
     * Birden fazla DTO'ya etki eden değişiklikleri (affected_dto_numbers dolu olanları)
     * tekillleştirir. dto_number ve dto_description dışındaki tüm alanlar aynıysa
     * sadece bir satır döndürür.
     */
    private function deduplicateOrderChanges($orderChanges) {
        $uniqueChanges = [];

        foreach ($orderChanges as $change) {
            // Sadece affected_dto_numbers DOLU olan satırlara deduplication uygula
            if (!empty($change['affected_dto_numbers'])) {
                // dto_number, dto_description ve affected_dto_numbers HARİÇ
                // diğer tüm alanlardan unique key oluştur
                $keyParts = [
                    $change['nachbau_row_type'] ?? '',
                    $change['operation'] ?? '',
                    $change['release_type'] ?? '',
                    $change['release_item'] ?? '',
                    $change['typical_no'] ?? '',
                    $change['ortz_kz'] ?? '',
                    $change['panel_no'] ?? '',
                    $change['release_quantity'] ?? '',
                    $change['parent_kmat'] ?? '',
                    $change['material_added_starts_by'] ?? '',
                    $change['material_added_number'] ?? '',
                    $change['material_deleted_starts_by'] ?? '',
                    $change['material_deleted_number'] ?? '',
                    $change['work_center'] ?? '',
                    $change['work_content'] ?? ''
                ];

                // Key oluştur
                $changeKey = implode('|', $keyParts);

                // Bu key'i daha önce görmediysen, ekle
                if (!isset($uniqueChanges[$changeKey])) {
                    $uniqueChanges[$changeKey] = $change;
                }
                // Eğer bu key'i daha önce gördüysen, ikinci (ve sonraki) satırları ignore et

            } else {
                // affected_dto_numbers BOŞ ise deduplication yapma, her satırı koru
                // Her satır için benzersiz bir key kullan
                $uniqueKey = uniqid('change_', true);
                $uniqueChanges[$uniqueKey] = $change;
            }
        }

        // Array'in key'lerini temizle, sıralı indexed array döndür
        return array_values($uniqueChanges);
    }

}


$controller = new NachbauController($_POST);

$response = match ($_GET['action']) {
    'getNachbauDataOfProject' => $controller->getNachbauDataOfProject(),
    'getNachbauFilesOfProjectWithStatus' => $controller->getNachbauFilesOfProjectWithStatus(),
    'getPanelsOfSelectedTypicals' => $controller->getPanelsOfSelectedTypicals(),
    'getTypicalsOfProject' => $controller->getTypicalsOfProject(),
    'getSpareDtosOfNachbau' => $controller->getSpareDtosOfNachbau(),
    'getSubMaterialListsOfWdaAndSpareChanges' => $controller->getSubMaterialListsOfWdaAndSpareChanges(),
    'getCountOfListTypes' => $controller->getCountOfListTypes(),
    'getAllTypicalAndPanelDtos' => $controller->getAllTypicalAndPanelDtos(),
    'getOnlyAccessoryDtos' => $controller->getOnlyAccessoryDtos(),
    'getDtoNumbersByTypeNumber' => $controller->getDtoNumbersByTypeNumber(),
    'getNachbauErrorItemAndSelectedList' => $controller->getNachbauErrorItemAndSelectedList(),
    'getProjectCurrentStatus' => $controller->getProjectCurrentStatus(),
    'getOrderSummary' => $controller->getOrderSummary(),
    'getDescriptionsOfAffectedDtoNumbers' => $controller->getDescriptionsOfAffectedDtoNumbers(),
    'getMinusPriceDtosOfNachbau' => $controller->getMinusPriceDtosOfNachbau(),
    'getMinusPriceDtoStatus' => $controller->getMinusPriceDtoStatus(),
    'getMinusPriceDtoNachbauListOfProject' => $controller->getMinusPriceDtoNachbauListOfProject(),
    'getExistingMinusPriceWontBeProducedItems' => $controller->getExistingMinusPriceWontBeProducedItems(),
    'getJTTypicalAndPanels' => $controller->getJTTypicalAndPanels(),
    'checkIfThereIsPossibleMissingMaterialLists' => $controller->checkIfThereIsPossibleMissingMaterialLists(),
    'getOrtzKzsAndPanelNoOfSelectedTypicals' => $controller->getOrtzKzsAndPanelNoOfSelectedTypicals(),
    default => ['status' => 400, 'message' => 'Invalid action']
};

$response = match ($_POST['action']) {
    'updateMinusPriceDtoChange' => $controller->updateMinusPriceDtoChange(),
    'moveMaterialToWontBeProduced' => $controller->moveMaterialToWontBeProduced(),
    'removeMinusPriceWork' => $controller->removeMinusPriceWork(),
    'generateAKDImportXmlFile' => $controller->generateAKDImportXmlFile(),
    default => ['status' => 400, 'message' => 'Invalid action']
};

