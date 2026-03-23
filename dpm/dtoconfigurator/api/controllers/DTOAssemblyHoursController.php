<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
require_once $_SERVER["DOCUMENT_ROOT"] .  '/shared/PHPExcel/Classes/PHPExcel.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('memory_limit', '512M');

class DTOAssemblyHoursController extends BaseController {

    public function getDtosWithPanelKeys($projectNo, $nachbauNo): array {
        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT ortz_kz, GROUP_CONCAT(DISTINCT t1.kmat_name SEPARATOR '||') as dtos_concat 
                  FROM nachbau_datas AS t1 
                  WHERE t1.project_no=:pNo AND t1.nachbau_no=:nNo AND ortz_kz != ''
                     AND t1.kmat_name REGEXP :pattern
                  GROUP BY ortz_kz";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':pattern' => $nachbauDtoPattern])['data'] ?? [];

        $data = [];
        foreach($result as $row){
            $data[$row['ortz_kz']] = explode('||', $row['dtos_concat']);
        }

        return $data;
    }

    public function getDTOAssemblyHoursMatrixData() {
        try {
            SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | Calculate Panel Based Assembly Hours Request");
            Journals::saveJournal("PROCESSING | Calculate Panel Based Assembly Hours Request", PAGE_DTO_ASSEMBLY_HOURS, DTO_ASSEMBLY_HOURS_PANEL_BY, ACTION_PROCESSING, null, "DTO Assembly Hours Panel");

            $projectNo = $_GET['projectNo'];
            $nachbauNo = $_GET['nachbauNo'];

            $query = "SELECT * FROM view_released_projects WHERE project_number = :pNo AND nachbau_number = :nNo AND submission_status = 5";
            $isProjectReleased = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];

            if (count($isProjectReleased) === 0) {
                returnHttpResponse(400, 'Project’s mechanical release has not been done yet.');
            }

            // Get DTOs with descriptions
            $dtosWithDescriptions = $this->getDtos($projectNo, $nachbauNo);

            // Get DTOs grouped by panel keys (ortz_kz)
            $dtoPanels = $this->getDtosWithPanelKeys($projectNo, $nachbauNo);

            // Get all unique DTOs that exist in panels
            $dtosInPanels = [];
            $validDtoNumbers = [];
            foreach ($dtoPanels as $dtosList) {
                foreach ($dtosList as $dto) {
                    $dtosInPanels[$dto] = true;
                    $validDtoNumbers[] = $this->formatDtoNumber($dto);
                }
            }
            $validDtoNumbers = array_unique($validDtoNumbers);

            // Bulk fetch all order changes for all DTOs at once
            $allOrderChanges = $this->getBulkOrderChanges($projectNo, $nachbauNo, $validDtoNumbers);

            // Bulk fetch all material hours at once
            $allMaterialHours = $this->getBulkMaterialHours($allOrderChanges);

            // Prepare panels array for frontend
            $panels = [];
            foreach ($dtoPanels as $panelKey => $dtosList) {
                $panels[] = [
                    'panelId' => $panelKey,
                    'panelName' => $panelKey
                ];
            }

            // Prepare DTO data with panel hours
            $dtoData = [];
            foreach ($dtosWithDescriptions as $dtoInfo) {
                $actualDtoNumber = $dtoInfo['DtoNumber'];

                if (!isset($dtosInPanels[$actualDtoNumber])) {
                    continue;
                }

                $formattedDtoNumber = $this->formatDtoNumber($actualDtoNumber);
                $panelHours = [];

                foreach ($dtoPanels as $panelKey => $dtosList) {
                    $addedHours = 0;
                    $deletedHours = 0;

                    if (in_array($actualDtoNumber, $dtosList)) {
                        $dtoChanges = $allOrderChanges[$formattedDtoNumber] ?? [];

                        foreach ($dtoChanges as $change) {
                            if ($change['ortz_kz'] === $panelKey) {
                                if ($change['operation'] === 'add' || $change['operation'] === 'replace') {
                                    $addedMaterial = $change['material_added_starts_by'] . $change['material_added_number'];
                                    $materialData = $allMaterialHours[$addedMaterial] ?? null;
                                    if ($materialData) {
                                        $addedHours += $materialData['totalHours'];
                                    }
                                }
                                if ($change['operation'] === 'delete' || $change['operation'] === 'replace') {
                                    $deletedMaterial = $change['material_deleted_starts_by'] . $change['material_deleted_number'];
                                    $materialData = $allMaterialHours[$deletedMaterial] ?? null;
                                    if ($materialData) {
                                        $deletedHours += $materialData['totalHours'];
                                    }
                                }
                            }
                        }
                    }

                    $addedUnit = $addedHours > 0 ? $this->getBestUnit($addedHours) : 'h';
                    $deletedUnit = $deletedHours > 0 ? $this->getBestUnit($deletedHours) : 'h';

                    $panelHours[] = [
                        'panelId' => $panelKey,
                        'addedHours' => $addedHours > 0 ? $this->formatTimeWithUnit($addedHours, $addedUnit) : null,
                        'addedUnit' => $addedUnit,
                        'deletedHours' => $deletedHours > 0 ? $this->formatTimeWithUnit($deletedHours, $deletedUnit) : null,
                        'deletedUnit' => $deletedUnit
                    ];
                }

                $dtoData[] = [
                    'dtoNumber' => $formattedDtoNumber,
                    'dtoDescription' => $this->formatDescription($dtoInfo['description'], 4) ?? '',
                    'panelHours' => $panelHours
                ];
            }

            $response = [
                'panels' => $panels,
                'dtoData' => $dtoData
            ];

            SharedManager::saveLog('log_dtoconfigurator', "RETURNED | Panel Based Assembly Hours Calculated Successfully");
            Journals::saveJournal("RETURNED | Panel Based Assembly Hours Calculated Successfully", PAGE_DTO_ASSEMBLY_HOURS, DTO_ASSEMBLY_HOURS_PANEL_BY, ACTION_VIEWED, null, "DTO Assembly Hours Panel");

            echo json_encode($response);
            exit();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
        }
    }

    private function getBulkOrderChanges($projectNo, $nachbauNo, $dtoNumbers) {
        if (empty($dtoNumbers)) return [];

        $query = "SELECT dto_number, ortz_kz, operation, material_added_starts_by, material_added_number, material_deleted_starts_by, material_deleted_number
              FROM view_released_order_changes 
              WHERE project_number = :pNo AND nachbau_number = :nNo 
              AND dto_number IN (:dtoNumbers)";

        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumbers' => $dtoNumbers])['data'] ?? [];

        // Group by DTO number for easier access
        $grouped = [];
        foreach ($result as $row) {
            $grouped[$row['dto_number']][] = $row;
        }

        return $grouped;
    }

    private function getBulkMaterialHours($allOrderChanges) {
        $allMaterials = [];

        // Collect all unique materials
        foreach ($allOrderChanges as $dtoChanges) {
            foreach ($dtoChanges as $change) {
                if (!empty($change['material_added_starts_by']) && !empty($change['material_added_number'])) {
                    $allMaterials[] = $change['material_added_starts_by'] . $change['material_added_number'];
                }
                if (!empty($change['material_deleted_starts_by']) && !empty($change['material_deleted_number'])) {
                    $allMaterials[] = $change['material_deleted_starts_by'] . $change['material_deleted_number'];
                }
            }
        }

        $allMaterials = array_unique($allMaterials);
        if (empty($allMaterials)) return [];

        // Sap: 63,32 -> 32 ondalık değerdir.  Sap: 6.332 -> 6332 ye eşitir. Noktalar ignore, virgüller replace with dots to get proper calculations.
        $query = "SELECT 
                      Material, 
                      BaseQty,
                      CAST(REPLACE(REPLACE(StdVal1, '.', ''), ',', '.') AS DECIMAL(10,2)) as SetupTime,
                      Unit1 as SetupTimeUnit,
                      CAST(REPLACE(REPLACE(StdVal2, '.', ''), ',', '.') AS DECIMAL(10,2)) as ProcessTime,
                      Unit2 as ProcessTimeUnit
                  FROM sap_spiridon_074 WHERE Material IN (:allMaterials)";

        $result = DbManager::fetchPDOQueryData('rpa', $query, [':allMaterials' => $allMaterials])['data'] ?? [];

        $materialHours = [];
        foreach ($result as $row) {
            $baseQty = floatval($row['BaseQty']);
            $setupTime = floatval($row['SetupTime']);
            $processTime = floatval($row['ProcessTime']);
            $setupUnit = strtoupper($row['SetupTimeUnit'] ?? 'H');
            $processUnit = strtoupper($row['ProcessTimeUnit'] ?? 'H');

            // Convert setup time to hours
            $setupTimeInHours = $this->convertToHours($setupTime, $setupUnit);

            // Convert process time to hours
            $processTimeInHours = $this->convertToHours($processTime, $processUnit);

            $processTimePerPiece = $processTimeInHours / $baseQty;
            $totalHours = $processTimePerPiece + $setupTimeInHours;

            $materialHours[$row['Material']] = [
                'totalHours' => $totalHours,
                'unit' => $this->getBestUnit($totalHours)
            ];
        }

        return $materialHours;
    }

    private function convertToHours($time, $unit) {
        switch (strtoupper($unit)) {
            case 'S':
            case 'SEC':
                return $time / 3600; // seconds to hours
            case 'M':
            case 'MIN':
                return $time / 60; // minutes to hours
            case 'H':
            case 'HR':
            default:
                return $time; // already in hours
        }
    }

    private function getBestUnit($hours) {
        if ($hours >= 1) {
            return 'h';
        } elseif ($hours >= 1/60) {
            return 'm';
        } else {
            return 's';
        }
    }

    private function formatTimeWithUnit($hours, $unit) {
        switch ($unit) {
            case 's':
                return round($hours * 3600, 2);
            case 'm':
                return round($hours * 60, 2);
            case 'h':
            default:
                return round($hours, 2);
        }
    }


    // STATION BASED CALCULATION STARTS

    public function validateProjectAndLot() {
        $projectNo = $_GET['projectNo'];
        $lot = $_GET['lotNo'];

        // Step 1: Proje DTO Configurator üzerinden yayınlanmalı BOM Change datasına ulaşabilmek için.
        $query = "SELECT project_number, project_name FROM view_released_projects WHERE project_number = :pNo AND submission_status = 5";
        $isProjectReleasedViaDtoConfigurator = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo])['data'];

        if (count($isProjectReleasedViaDtoConfigurator) === 0)
            returnHttpResponse(400, 'Project ' . $projectNo . ' has not released via DTO Configurator.');

        // Step 2: Girilen Proje-Lot eşleştirmeleri OPT altında bulunuyor mu kontrolü.
        $query = "SELECT id FROM sap_opt WHERE OrderNo = :pNo AND Lot = :lot";
        $projectLotData = DbManager::fetchPDOQueryData('rpa', $query, [':pNo' => $projectNo, ':lot' => $lot])['data'];

        if (count($projectLotData) === 0)
            returnHttpResponse(400, 'Project ' . $projectNo . ' does not contain lot ' . $lot . ' in SAP.');

        echo json_encode($isProjectReleasedViaDtoConfigurator);
        exit();
    }

    // NEW CALCULATE FUNCTION
    public function calculateStationBasedAssemblyHours() {
        ini_set('max_execution_time', 180); // Max 5dk

        SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | Calculate Station Based Assembly Hours Request");
        Journals::saveJournal("PROCESSING | Calculate Station Based Assembly Hours Request", PAGE_DTO_ASSEMBLY_HOURS, DTO_ASSEMBLY_HOURS_STATION_BY, ACTION_PROCESSING, null, "DTO Assembly Hours Station");

        /*
         projectLotEntries request body is below:
         array(10) {
              [0]=>
              array(2) {
                ["project"]=>
                string(10) "7024058306"
                ["lot"]=>
                string(1) "1"
              }
              [1]=>
              array(2) {
                ["project"]=>
                string(10) "7024059178"
                ["lot"]=>
                string(1) "1"
              }
              ...
         */
        $projectLotEntries = $_GET['projectLotEntries'];
        $allTableData = [];

        // PERFORMANCE OPTIMIZATION: Bulk fetch data for all projects at once
        $projectNumbers = array_unique(array_column($projectLotEntries, 'project'));
        $allProjectOrderChanges = $this->getBulkProjectOrderChanges($projectNumbers);
        $allProjectLotPanels = $this->getBulkProjectLotPanels($projectLotEntries);
        $allMaterials = $this->collectAllMaterialsFromChanges($allProjectOrderChanges);
        $allMaterialHours = $this->getBulkMaterialHoursDirectly($allMaterials);

        foreach ($projectLotEntries as $entry) {
            // Step 1: Siparişin DTO Configurator sisteminde yapılmış BOM değişikliklerini getir. Montaj süresi hesabı olduğu için, aksesuarları getirmiyorum.
            $releasedOrderChanges = $allProjectOrderChanges[$entry['project']] ?? [];

            if (empty($releasedOrderChanges)) {
                continue;
            }

            // Step 2: Projenin lotunda bulunan panoları getir.
            $sapPanelNos = $allProjectLotPanels["{$entry['project']}-{$entry['lot']}"] ?? [];

            if (empty($sapPanelNos)) {
                continue;
            }

            // Step 3: $sapPanelNos arrayinin her bir elementi $change['sap_panel_no'] ya eşit olan $releasedOrderChanges leri filtrele. Yani sadece ilgili lottaki geçen panolara ait değişiklikleri getir.
            $filteredReleasedOrderChanges = array_values(array_filter($releasedOrderChanges, function($change) use ($sapPanelNos) {
                return in_array($change['sap_panel_no'], $sapPanelNos);
            }));

            if (empty($filteredReleasedOrderChanges)) {
                continue;
            }

            // Step 4: İlgili lottaki değişiklikleri getirdikten sonra, süreleri hesapla ve response hazırla. Rows -> DTO Number, Column Headers -> Work Center + Work Content (Station)
            $dtoNumbers = array_unique(array_column($filteredReleasedOrderChanges, 'dto_number'));

            $workCenters = [];
            foreach ($filteredReleasedOrderChanges as $change) {
                $workCenterKey = $change['work_center'];
                if (!isset($workCenters[$workCenterKey])) {
                    $workCenters[$workCenterKey] = [
                        'work_center' => $change['work_center'],
                        'work_content' => $change['work_content'],
                        'column_header' => $change['work_center'] . ' - ' . $change['work_content']
                    ];
                }
            }

            // Group changes by DTO and Work Center
            $changesByDtoAndWorkCenter = [];
            foreach ($filteredReleasedOrderChanges as $change) {
                $dtoNumber = $change['dto_number'];
                $workCenter = $change['work_center'];

                if (!isset($changesByDtoAndWorkCenter[$dtoNumber])) {
                    $changesByDtoAndWorkCenter[$dtoNumber] = [];
                }

                if (!isset($changesByDtoAndWorkCenter[$dtoNumber][$workCenter])) {
                    $changesByDtoAndWorkCenter[$dtoNumber][$workCenter] = [];
                }

                $changesByDtoAndWorkCenter[$dtoNumber][$workCenter][] = $change;
            }

            // Build table data structure for THIS project-lot
            foreach ($dtoNumbers as $dtoNumber) {
                $rowData = [
                    'dto_number' => $dtoNumber,
                    'project_number' => $entry['project'],
                    'lot_number' => $entry['lot']
                ];

                // Add data for each work center column
                foreach ($workCenters as $workCenterKey => $workCenterInfo) {
                    $columnKey = 'wc_' . $workCenterKey;

                    if (isset($changesByDtoAndWorkCenter[$dtoNumber][$workCenterKey])) {
                        $changes = $changesByDtoAndWorkCenter[$dtoNumber][$workCenterKey];

                        $addedHours = 0;
                        $deletedHours = 0;

                        foreach ($changes as $change) {
                            if ($change['operation'] === 'add' || $change['operation'] === 'replace') {
                                $addedMaterial = $change['material_added_starts_by'] . $change['material_added_number'];
                                $materialData = $allMaterialHours[$addedMaterial] ?? null;
                                if ($materialData) {
                                    $addedHours += $materialData['totalHours'];
                                }
                            }
                            if ($change['operation'] === 'delete' || $change['operation'] === 'replace') {
                                $deletedMaterial = $change['material_deleted_starts_by'] . $change['material_deleted_number'];
                                $materialData = $allMaterialHours[$deletedMaterial] ?? null;
                                if ($materialData) {
                                    $deletedHours += $materialData['totalHours'];
                                }
                            }
                        }

                        $addedUnit = $addedHours > 0 ? $this->getBestUnit($addedHours) : 'h';
                        $deletedUnit = $deletedHours > 0 ? $this->getBestUnit($deletedHours) : 'h';

                        $rowData[$columnKey] = [
                            'addedHours' => $addedHours > 0 ? $this->formatTimeWithUnit($addedHours, $addedUnit) : 0,
                            'addedUnit' => $addedUnit,
                            'deletedHours' => $deletedHours > 0 ? $this->formatTimeWithUnit($deletedHours, $deletedUnit) : 0,
                            'deletedUnit' => $deletedUnit,
                            'change_count' => count($changes),
                            'changes' => $changes
                        ];
                    } else {
                        $rowData[$columnKey] = [
                            'addedHours' => 0,
                            'addedUnit' => 'h',
                            'deletedHours' => 0,
                            'deletedUnit' => 'h',
                            'change_count' => 0,
                            'changes' => []
                        ];
                    }
                }

                $allTableData[] = $rowData;
            }
        }

        SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | Station Based Assembly Hours Calculated Successfully");
        Journals::saveJournal("PROCESSING | Station Based Assembly Hours Calculated Successfully", PAGE_DTO_ASSEMBLY_HOURS, DTO_ASSEMBLY_HOURS_STATION_BY, ACTION_VIEWED, null, "DTO Assembly Hours Station");

        ob_start("ob_gzhandler");
        header("Content-Encoding: gzip");
        header("Content-Type: application/json");

        echo json_encode([
            'success' => true,
            'data' => $allTableData
        ]);
        exit();
    }

    // PERFORMANCE OPTIMIZATION: Bulk fetch order changes for all projects
    private function getBulkProjectOrderChanges($projectNumbers) {
        if (empty($projectNumbers)) return [];

        $query = "SELECT project_number, dto_number, operation, typical_no, ortz_kz, panel_no, release_quantity, 
                     material_added_starts_by, material_added_number, material_deleted_starts_by, material_deleted_number,
                     work_center, work_content
              FROM view_released_order_changes 
              WHERE project_number IN (:projectNumbers) AND is_accessory = 0 AND active = 1";

        $allChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':projectNumbers' => $projectNumbers])['data'];

        // Group by project number and add sap_panel_no
        $groupedChanges = [];
        foreach ($allChanges as $change) {
            $projectNo = $change['project_number'];
            $change['sap_panel_no'] = $this->convertNachbauPanelNoToMToolPosNo($projectNo, '', $change['typical_no'], $change['ortz_kz'], $change['panel_no']);

            if (!isset($groupedChanges[$projectNo])) {
                $groupedChanges[$projectNo] = [];
            }
            $groupedChanges[$projectNo][] = $change;
        }

        return $groupedChanges;
    }

    // PERFORMANCE OPTIMIZATION: Bulk fetch panel data for all project-lot combinations
    private function getBulkProjectLotPanels($projectLotEntries) {
        if (empty($projectLotEntries)) return [];

        // Build WHERE conditions for all project-lot combinations
        $whereConditions = [];
        $params = [];
        foreach ($projectLotEntries as $index => $entry) {
            $whereConditions[] = "(OrderNo = :project{$index} AND Lot = :lot{$index})";
            $params[":project{$index}"] = $entry['project'];
            $params[":lot{$index}"] = $entry['lot'];
        }

        $query = "SELECT OrderNo, Lot, Item 
              FROM sap_opt 
              WHERE " . implode(' OR ', $whereConditions) . "
              GROUP BY OrderNo, Lot, Item";

        $allPanels = DbManager::fetchPDOQueryData('rpa', $query, $params)['data'];

        // Group by project-lot combination
        $groupedPanels = [];
        foreach ($allPanels as $panel) {
            $key = "{$panel['OrderNo']}-{$panel['Lot']}";
            if (!isset($groupedPanels[$key])) {
                $groupedPanels[$key] = [];
            }
            $groupedPanels[$key][] = $panel['Item'];
        }

        return $groupedPanels;
    }

    // PERFORMANCE "OPTIMIZATION": Collect all materials from all order changes at once
    private function collectAllMaterialsFromChanges($allProjectOrderChanges) {
        $allMaterials = [];

        foreach ($allProjectOrderChanges as $projectChanges) {
            foreach ($projectChanges as $change) {
                if (!empty($change['material_added_starts_by']) && !empty($change['material_added_number'])) {
                    $allMaterials[] = $change['material_added_starts_by'] . $change['material_added_number'];
                }
                if (!empty($change['material_deleted_starts_by']) && !empty($change['material_deleted_number'])) {
                    $allMaterials[] = $change['material_deleted_starts_by'] . $change['material_deleted_number'];
                }
            }
        }

        return array_unique($allMaterials);
    }

    private function getBulkMaterialHoursDirectly($allMaterials) {
        if (empty($allMaterials)) return [];

        // Same logic as your panel-based calculation
        $query = "SELECT 
                  Material, 
                  BaseQty,
                  CAST(REPLACE(REPLACE(StdVal1, '.', ''), ',', '.') AS DECIMAL(10,2)) as SetupTime,
                  Unit1 as SetupTimeUnit,
                  CAST(REPLACE(REPLACE(StdVal2, '.', ''), ',', '.') AS DECIMAL(10,2)) as ProcessTime,
                  Unit2 as ProcessTimeUnit
              FROM sap_spiridon_074 WHERE Material IN (:allMaterials)";

        $result = DbManager::fetchPDOQueryData('rpa', $query, [':allMaterials' => $allMaterials])['data'] ?? [];

        $materialHours = [];
        foreach ($result as $row) {
            $baseQty = floatval($row['BaseQty']);
            $setupTime = floatval($row['SetupTime']);
            $processTime = floatval($row['ProcessTime']);
            $setupUnit = strtoupper($row['SetupTimeUnit'] ?? 'H');
            $processUnit = strtoupper($row['ProcessTimeUnit'] ?? 'H');

            // Convert setup time to hours
            $setupTimeInHours = $this->convertToHours($setupTime, $setupUnit);

            // Convert process time to hours
            $processTimeInHours = $this->convertToHours($processTime, $processUnit);

            $processTimePerPiece = $processTimeInHours / $baseQty;
            $totalHours = $processTimePerPiece + $setupTimeInHours;

            $materialHours[$row['Material']] = [
                'totalHours' => $totalHours,
                'unit' => $this->getBestUnit($totalHours)
            ];
        }

        return $materialHours;
    }

    public function exportStationBasedAssemblyHoursToExcel() {
        SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | Export Station Based Assembly Hours to Excel Request");
        Journals::saveJournal("PROCESSING | Export Station Based Assembly Hours to Excel Request", PAGE_DTO_ASSEMBLY_HOURS, DTO_ASSEMBLY_HOURS_STATION_BY, ACTION_PROCESSING, null, "DTO Assembly Hours Station");

        $projectLotEntries = json_decode($_POST['projectLotEntries'], true);
        $exportData = json_decode($_POST['exportData'], true);

        if (empty($projectLotEntries) || empty($exportData)) {
            returnHttpResponse(400, 'Invalid Input - Missing project lot entries or export data');
            exit;
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->removeSheetByIndex(0);
        $sheetIndex = 0;

        foreach ($exportData as $projectLotKey => $projectLotData) {
            $sheet = $objPHPExcel->createSheet($sheetIndex);
            $sheetName = "{$projectLotData['project_number']}-{$projectLotData['lot_number']}";
            $sheet->setTitle($sheetName);

            $this->createExcelSheetForProjectLot($sheet, $projectLotData);

            $sheetIndex++;
        }

        $objPHPExcel->setActiveSheetIndex(0);

        $timestamp = date('Y-m-d_H-i-s');
        $excelFileName = "DTO_Assembly_Hours_Station_Based_{$timestamp}.xlsx";

        // Send response headers and output the file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $excelFileName);
        header('Cache-Control: max-age=0');

        SharedManager::saveLog('log_dtoconfigurator', "RETURNED | Station Based Assembly Hours to Excel Exported Successfully.");
        Journals::saveJournal("RETURNED | Station Based Assembly Hours to Excel Exported Successfully.", PAGE_DTO_ASSEMBLY_HOURS, DTO_ASSEMBLY_HOURS_STATION_BY, ACTION_VIEWED, null, "DTO Assembly Hours Station");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    private function createExcelSheetForProjectLot($sheet, $projectLotData) {
        $dtoData = $projectLotData['dtos'];
        $projectNumber = $projectLotData['project_number'];
        $lotNumber = $projectLotData['lot_number'];

        // Extract unique work centers
        $workCenterSet = [];
        foreach ($dtoData as $dto) {
            foreach ($dto as $key => $value) {
                if (str_starts_with($key, 'wc_')) {
                    $workCenter = str_replace('wc_', '', $key);
                    $workCenterSet[$workCenter] = true;
                }
            }
        }

        // Get work center details - IMPROVED VERSION
        $workCenters = [];
        $workCenterDetails = []; // Store found work centers first

        // First pass: collect work center details from changes that exist
        foreach (array_keys($workCenterSet) as $wcKey) {
            foreach ($dtoData as $dto) {
                $wcData = $dto["wc_{$wcKey}"] ?? null;
                if ($wcData && !empty($wcData['changes'])) {
                    $change = $wcData['changes'][0];
                    $workCenterDetails[$wcKey] = [
                        'workCenter' => $change['work_center'],
                        'workContent' => $change['work_content'],
                        'columnHeader' => $change['work_center'] . ' - ' . $change['work_content']
                    ];
                    break; // Found details for this work center
                }
            }
        }

        // Second pass: only include work centers that have actual changes (non-zero hours)
        foreach (array_keys($workCenterSet) as $wcKey) {
            $hasActualChanges = false;

            // Check if any DTO has non-zero hours for this work center
            foreach ($dtoData as $dto) {
                $wcData = $dto["wc_{$wcKey}"] ?? null;
                if ($wcData && ($wcData['addedHours'] > 0 || $wcData['deletedHours'] > 0)) {
                    $hasActualChanges = true;
                    break;
                }
            }

            // Only include work centers that have actual changes AND we found their details
            if ($hasActualChanges && isset($workCenterDetails[$wcKey])) {
                $workCenters[] = $workCenterDetails[$wcKey];
            }
        }

        // ADD PROJECT INFO AT THE TOP
        $sheet->setCellValue('A1', "Project: {$projectNumber} - Lot: {$lotNumber}");
        $sheet->mergeCells('A1:' . PHPExcel_Cell::stringFromColumnIndex(count($workCenters) + 1) . '1');

        // Style project info row
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => ['rgb' => 'B8CCE4']
            ]
        ]);

        // Write header row (now in row 3)
        $headerRow = 3;
        $colIndex = 0;

        // DTO Number column
        $sheet->setCellValueByColumnAndRow($colIndex++, $headerRow, 'DTO Number');

        // Work center columns
        foreach ($workCenters as $workCenter) {
            $sheet->setCellValueByColumnAndRow($colIndex++, $headerRow, $workCenter['columnHeader']);
        }

        // Total column
        $sheet->setCellValueByColumnAndRow($colIndex, $headerRow, 'Total');

        // Write data rows (starting from row 4)
        $currentRow = 4;
        $columnTotals = [];

        // Initialize column totals
        foreach ($workCenters as $index => $workCenter) {
            $columnTotals[$index] = ['addedHours' => 0, 'deletedHours' => 0];
        }

        foreach ($dtoData as $dto) {
            $colIndex = 0;

            // DTO Number
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $dto['dto_number']);

            $rowTotalAdded = 0;
            $rowTotalDeleted = 0;

            // Work center data
            foreach ($workCenters as $wcIndex => $workCenter) {
                $wcKey = "wc_{$workCenter['workCenter']}";
                $wcData = $dto[$wcKey] ?? null;

                $cellValue = '';
                $cellCoordinate = PHPExcel_Cell::stringFromColumnIndex($colIndex) . $currentRow;

                if ($wcData && ($wcData['addedHours'] > 0 || $wcData['deletedHours'] > 0)) {
                    if ($wcData['addedHours'] > 0) {
                        $cellValue .= "+{$wcData['addedHours']}{$wcData['addedUnit']}";
                        $columnTotals[$wcIndex]['addedHours'] += $this->convertToHours($wcData['addedHours'], $wcData['addedUnit']);
                        $rowTotalAdded += $this->convertToHours($wcData['addedHours'], $wcData['addedUnit']);
                    }
                    if ($wcData['deletedHours'] > 0) {
                        if ($cellValue) $cellValue .= "\n";
                        $cellValue .= "-{$wcData['deletedHours']}{$wcData['deletedUnit']}";
                        $columnTotals[$wcIndex]['deletedHours'] += $this->convertToHours($wcData['deletedHours'], $wcData['deletedUnit']);
                        $rowTotalDeleted += $this->convertToHours($wcData['deletedHours'], $wcData['deletedUnit']);
                    }

                    // Apply color formatting for this cell
                    $this->applyNumberColorFormatting($sheet, $cellCoordinate, $cellValue);
                } else {
                    $cellValue = '-';
                }

                $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $cellValue);
            }

            // Calculate and add row total
            $rowNetTotal = $rowTotalAdded - $rowTotalDeleted;
            $rowTotalCell = '';
            $totalCellCoordinate = PHPExcel_Cell::stringFromColumnIndex($colIndex) . $currentRow;

            if ($rowNetTotal != 0) {
                $bestUnit = $this->getBestUnit(abs($rowNetTotal));
                $formattedTotal = $this->formatTimeWithUnit(abs($rowNetTotal), $bestUnit);
                $sign = $rowNetTotal < 0 ? '-' : '+';
                $rowTotalCell = "{$sign}{$formattedTotal}{$bestUnit}";

                // Apply color formatting for total cell
                $this->applyNumberColorFormatting($sheet, $totalCellCoordinate, $rowTotalCell);
            } else {
                $rowTotalCell = '-';
            }

            $sheet->setCellValueByColumnAndRow($colIndex, $currentRow, $rowTotalCell);

            // Zebra styling: if currentRow is even, fill with light gray
            if (($currentRow % 2) === 0) {
                $lastCol = PHPExcel_Cell::stringFromColumnIndex($colIndex);
                $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")
                    ->applyFromArray([
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'EFEFEF']
                        ]
                    ]);
            }

            $currentRow++;
        }

        // Add Grand Total row
        $grandTotalRow = $currentRow;
        $colIndex = 0;

        // Grand Total label
        $sheet->setCellValueByColumnAndRow($colIndex++, $grandTotalRow, 'Grand Total');

        $grandTotalAdded = 0;
        $grandTotalDeleted = 0;

        // Column totals
        foreach ($workCenters as $wcIndex => $workCenter) {
            $colTotal = $columnTotals[$wcIndex];
            $netHours = $colTotal['addedHours'] - $colTotal['deletedHours'];

            $grandTotalAdded += $colTotal['addedHours'];
            $grandTotalDeleted += $colTotal['deletedHours'];

            $cellValue = '';
            $cellCoordinate = PHPExcel_Cell::stringFromColumnIndex($colIndex) . $grandTotalRow;

            if ($netHours != 0) {
                $bestUnit = $this->getBestUnit(abs($netHours));
                $formattedNet = $this->formatTimeWithUnit(abs($netHours), $bestUnit);
                $sign = $netHours < 0 ? '-' : '+';
                $cellValue = "{$sign}{$formattedNet}{$bestUnit}";

                // Apply color formatting
                $this->applyNumberColorFormatting($sheet, $cellCoordinate, $cellValue);
            } else {
                $cellValue = '-';
            }

            $sheet->setCellValueByColumnAndRow($colIndex++, $grandTotalRow, $cellValue);
        }

        // Grand total for Total column
        $grandNetTotal = $grandTotalAdded - $grandTotalDeleted;
        $grandTotalCell = '';
        $grandTotalCellCoordinate = PHPExcel_Cell::stringFromColumnIndex($colIndex) . $grandTotalRow;

        if ($grandTotalAdded > 0) {
            $addedUnit = $this->getBestUnit($grandTotalAdded);
            $formattedAdded = $this->formatTimeWithUnit($grandTotalAdded, $addedUnit);
            $grandTotalCell .= "+{$formattedAdded}{$addedUnit}";
        }

        if ($grandTotalDeleted > 0) {
            if ($grandTotalCell) $grandTotalCell .= "\n";
            $deletedUnit = $this->getBestUnit($grandTotalDeleted);
            $formattedDeleted = $this->formatTimeWithUnit($grandTotalDeleted, $deletedUnit);
            $grandTotalCell .= "-{$formattedDeleted}{$deletedUnit}";
        }

        if ($grandNetTotal != 0) {
            if ($grandTotalCell) $grandTotalCell .= "\n";
            $netUnit = $this->getBestUnit(abs($grandNetTotal));
            $formattedNet = $this->formatTimeWithUnit(abs($grandNetTotal), $netUnit);
            $netSign = $grandNetTotal < 0 ? '-' : '+';
            $grandTotalCell .= "{$netSign}{$formattedNet}{$netUnit}";
        }

        $sheet->setCellValueByColumnAndRow($colIndex, $grandTotalRow, $grandTotalCell);

        // Apply color formatting to grand total cell
        if ($grandTotalCell !== '-') {
            $this->applyNumberColorFormatting($sheet, $grandTotalCellCoordinate, $grandTotalCell);
        }

        // Styling
        $this->applyExcelStyling($sheet, $headerRow, $grandTotalRow, count($workCenters) + 2);
    }

    private function applyNumberColorFormatting($sheet, $cellCoordinate, $cellValue) {
        // Check if cell contains positive or negative numbers
        $hasPositive = str_contains($cellValue, '+');
        $hasNegative = str_contains($cellValue, '-');

        if ($hasPositive && $hasNegative) {
            // Mixed content - apply rich text formatting
            $richText = new PHPExcel_RichText();

            $lines = explode("\n", $cellValue);
            foreach ($lines as $index => $line) {
                if ($index > 0) {
                    $richText->createText("\n");
                }

                $textRun = $richText->createTextRun($line);
                if (strpos($line, '+') === 0) {
                    $textRun->getFont()->setColor(new PHPExcel_Style_Color('008000'));
                } elseif (strpos($line, '-') === 0) {
                    $textRun->getFont()->setColor(new PHPExcel_Style_Color('FF0000'));
                }
            }

            $sheet->getCell($cellCoordinate)->setValue($richText);
        } elseif ($hasPositive) {
            $sheet->getStyle($cellCoordinate)->getFont()->setColor(new PHPExcel_Style_Color('008000'));
        } elseif ($hasNegative) {
            $sheet->getStyle($cellCoordinate)->getFont()->setColor(new PHPExcel_Style_Color('FF0000'));
        }
    }

    private function applyExcelStyling($sheet, $headerRow, $lastRow, $totalColumns) {
        $lastCol = PHPExcel_Cell::stringFromColumnIndex($totalColumns - 1);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15); // DTO Number
        for ($i = 1; $i < $totalColumns - 1; $i++) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setWidth(25); // Work centers (increased width)
        }
        $sheet->getColumnDimension($lastCol)->setWidth(20); // Total

        // Set row height for project info
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Header styling
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")
            ->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => ['rgb' => 'D8E4BC']
                ]
            ]);

        // Grand total row styling
        $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")
            ->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => ['rgb' => 'D0E8FF']
                ]
            ]);

        // Borders for all data cells (excluding project info row)
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")
            ->applyFromArray([
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    ]
                ]
            ]);

        // Border for project info row
        $sheet->getStyle("A1:{$lastCol}1")
            ->applyFromArray([
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                    ]
                ]
            ]);

        // Center align all cells
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        // Wrap text for cells with multiple lines
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")
            ->getAlignment()
            ->setWrapText(true);
    }
    // STATION BASED CALCULATION ENDS
}


$controller = new DTOAssemblyHoursController($_POST);

$response = match ($_GET['action']) {
    'getDTOAssemblyHoursMatrixData' => $controller->getDTOAssemblyHoursMatrixData(),
    'validateProjectAndLot' => $controller->validateProjectAndLot(),
    'calculateStationBasedAssemblyHours' => $controller->calculateStationBasedAssemblyHours(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};


$response = match ($_POST['action']) {
    'exportStationBasedAssemblyHoursToExcel' => $controller->exportStationBasedAssemblyHoursToExcel(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};