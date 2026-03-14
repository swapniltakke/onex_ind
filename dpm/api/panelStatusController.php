<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";

header('Content-Type:application/json;charset=utf-8;');

// Simple timing array
$timings = [];
$globalStart = microtime(true);
$timerStack = []; // Track nested timers

function startTimer($label) {
    global $timings, $timerStack;
    $timerStack[] = $label;
    $timings[$label] = ['start' => microtime(true), 'label' => $label];
}

function endTimer($label) {
    global $timings, $timerStack;
    if (isset($timings[$label]) && $timings[$label]['start']) {
        $timings[$label]['end'] = microtime(true);
        $timings[$label]['duration'] = ($timings[$label]['end'] - $timings[$label]['start']) * 1000; // ms
    }
    array_pop($timerStack);
}

function getTimings() {
    global $timings, $globalStart;
    $totalTime = (microtime(true) - $globalStart) * 1000;
    
    // Create array with proper labels
    $sortedTimings = [];
    foreach ($timings as $label => $data) {
        if (isset($data['duration'])) {
            $sortedTimings[$label] = [
                'label' => $label,
                'duration' => $data['duration']
            ];
        }
    }
    
    // Sort by duration descending
    usort($sortedTimings, function($a, $b) {
        return $b['duration'] <=> $a['duration'];
    });
    
    return [
        'total_time_ms' => round($totalTime, 2),
        'timings' => $sortedTimings
    ];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$debug = isset($_GET['debug']) && $_GET['debug'] === '1'; // Enable with ?debug=1

switch ($action) {
    case 'getPanelStatus':
        getPanelStatus();
        break;
    case 'getProjectNumbers':
        getProjectNumbers();
        break;
    case 'getClientNames':
        getClientNames();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

// Log to file if debug is enabled
if ($debug) {
    $logDir = $_SERVER['DOCUMENT_ROOT'] . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/panel_status_debug_' . date('Y-m-d') . '.log';
    $logContent = "\n" . str_repeat("=", 120) . "\n";
    $logContent .= "DEBUG LOG - " . date('Y-m-d H:i:s') . "\n";
    $logContent .= "Action: " . $action . "\n";
    $logContent .= str_repeat("=", 120) . "\n";
    
    $perfData = getTimings();
    $logContent .= "TOTAL EXECUTION TIME: " . $perfData['total_time_ms'] . " ms\n";
    $logContent .= str_repeat("-", 120) . "\n";
    $logContent .= sprintf("%-70s | %15s | %10s\n", "Operation", "Duration (ms)", "% of Total");
    $logContent .= str_repeat("-", 120) . "\n";
    
    $totalDuration = $perfData['total_time_ms'];
    
    foreach ($perfData['timings'] as $data) {
        $percentage = ($data['duration'] / $totalDuration) * 100;
        $logContent .= sprintf("%-70s | %15.2f | %9.2f%%\n", 
            substr($data['label'], 0, 70), 
            $data['duration'],
            $percentage
        );
    }
    
    $logContent .= str_repeat("=", 120) . "\n";
    file_put_contents($logFile, $logContent, FILE_APPEND);
}

exit;

function getPanelStatus() {
    // Get filter parameters
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;
    
    // Parse date range if provided
    $dateRangeProvided = isset($_GET['dateRange']) && !empty($_GET['dateRange']);
    $startDate = date('Y-m-d'); // Default to today
    $endDate = date('Y-m-d');   // Default to today
    
    if ($dateRangeProvided) {
        $dates = explode(' - ', $_GET['dateRange']);
        if (count($dates) == 2) {
            $startDate = date('Y-m-d', strtotime(str_replace('.', '-', $dates[0])));
            $endDate = date('Y-m-d', strtotime(str_replace('.', '-', $dates[1])));
        }
    }
    
    // Other filters
    $clientName = isset($_GET['clientName']) ? $_GET['clientName'] : '';
    $project = isset($_GET['project']) ? $_GET['project'] : '';
    $tabType = isset($_GET['tabType']) ? $_GET['tabType'] : 'breaker'; // Default to breaker tab
    
    // Determine which data to fetch based on tab type
    if ($tabType === 'breaker') {
        fetchBreakerData($page, $limit, $offset, $startDate, $endDate, $clientName, $project, $dateRangeProvided, $tabType);
    } else {
        fetchPanelData($page, $limit, $offset, $startDate, $endDate, $clientName, $project, $dateRangeProvided, $tabType);
    }
}

function fetchBreakerData($page, $limit, $offset, $startDate, $endDate, $clientName, $project, $dateRangeProvided, $tabType) {
    // Check if project filter is active (highest priority)
    $projectFilterActive = !empty($project);
    
    // Check if client name filter is active (second priority)
    $clientNameFilterActive = !empty($clientName);
    
    if ($projectFilterActive) {
        // PRIORITY 1: Project Filter
        $transactionResult = getTransactionDataForBreakerByProject($project);
        
        if (empty($transactionResult['data'])) {
            echo json_encode([
                'status' => 'success',
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => 0
                ],
                'filters' => [
                    'clientName' => $clientName,
                    'project' => $project,
                    'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                    'filterPriority' => 'project',
                    'tabType' => $tabType
                ]
            ]);
            return;
        }
        
        $mergedData = $transactionResult['data'];
        
        // Apply pagination to the merged results
        $totalPanels = count($mergedData);
        $paginatedPanels = array_slice($mergedData, $offset, $limit);
        
        // Process each panel to get station statuses
        $panels = [];
        foreach ($paginatedPanels as $index => $row) {
            $projectName = getProjectNameFromContacts($row['project_no']);
            
            $stationStatuses = getBreakerStationStatuses(
                $row['project_no'], 
                $row['item_no'],
                $row['production_no'],
                $row['serial_no'],
                $startDate, 
                $endDate, 
                true, 
                $dateRangeProvided
            );
            
            $panels[] = [
                'serial_no' => $row['serial_no'],
                'project_no' => $row['project_no'],
                'item_no' => $row['item_no'],
                'production_no' => $row['production_no'],
                'panel_no' => $row['panel_no'] ? $row['panel_no'] : '',
                'project_name' => $projectName,
                'stations' => $stationStatuses
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $panels,
            'pagination' => [
                'total' => $totalPanels,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($totalPanels / $limit)
            ],
            'filters' => [
                'clientName' => $clientName,
                'project' => $project,
                'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                'filterPriority' => 'project',
                'tabType' => $tabType
            ]
        ]);
    } elseif ($clientNameFilterActive) {
        // PRIORITY 2: Client Name Filter
        // Get factory numbers for the client name
        $factoryNumbers = getFactoryNumbersByClientName($clientName);
        
        if (empty($factoryNumbers)) {
            echo json_encode([
                'status' => 'success',
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => 0
                ],
                'filters' => [
                    'clientName' => $clientName,
                    'project' => $project,
                    'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                    'filterPriority' => 'clientName',
                    'tabType' => $tabType
                ]
            ]);
            return;
        }
        
        // Get transaction data for all factory numbers
        $transactionResult = getTransactionDataForBreakerByClientName($factoryNumbers);
        
        if (empty($transactionResult['data'])) {
            echo json_encode([
                'status' => 'success',
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => 0
                ],
                'filters' => [
                    'clientName' => $clientName,
                    'project' => $project,
                    'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                    'filterPriority' => 'clientName',
                    'tabType' => $tabType
                ]
            ]);
            return;
        }
        
        $mergedData = $transactionResult['data'];
        
        // Apply pagination to the merged results
        $totalPanels = count($mergedData);
        $paginatedPanels = array_slice($mergedData, $offset, $limit);
        
        // Process each panel to get station statuses
        $panels = [];
        foreach ($paginatedPanels as $index => $row) {
            $projectName = getProjectNameFromContacts($row['project_no']);
            
            $stationStatuses = getBreakerStationStatuses(
                $row['project_no'], 
                $row['item_no'],
                $row['production_no'],
                $row['serial_no'],
                $startDate, 
                $endDate, 
                true, 
                $dateRangeProvided
            );
            
            $panels[] = [
                'serial_no' => $row['serial_no'],
                'project_no' => $row['project_no'],
                'item_no' => $row['item_no'],
                'production_no' => $row['production_no'],
                'panel_no' => $row['panel_no'] ? $row['panel_no'] : '',
                'project_name' => $projectName,
                'stations' => $stationStatuses
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $panels,
            'pagination' => [
                'total' => $totalPanels,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($totalPanels / $limit)
            ],
            'filters' => [
                'clientName' => $clientName,
                'project' => $project,
                'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                'filterPriority' => 'clientName',
                'tabType' => $tabType
            ]
        ]);
    } else {
        // PRIORITY 3: Date Range Filter (or default to today)
        // Build SQL query for unique project+item combinations using generated columns
        $sql = "
            SELECT 
                DISTINCT 
                barcode_serial_no AS serial_no,
                barcode_project_no AS project_no,
                barcode_item_no AS item_no,
                barcode_production_no AS production_no,
                panel_no,
                barcode
            FROM 
                (
                    SELECT barcode_serial_no, barcode_project_no, barcode_item_no, barcode_production_no, panel_no, barcode, up_date FROM tbl_transactions
                    UNION
                    SELECT barcode_serial_no, barcode_project_no, barcode_item_no, barcode_production_no, NULL as panel_no, barcode, up_date FROM tbl_warehousebarcode
                ) t
            WHERE 1=1
            ";
        
        // Initialize params array
        $params = [];
        
        // Apply date range filter only if date range is provided
        if ($dateRangeProvided) {
            $sql .= " AND DATE(t.up_date) BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        } 
        // Default to today if no filters are provided
        else {
            $sql .= " AND DATE(t.up_date) = CURDATE()";
        }
        
        // Group by generated columns to ensure uniqueness
        $sql .= " GROUP BY barcode_serial_no, barcode_project_no, barcode_item_no, barcode_production_no";
        $sql .= " ORDER BY barcode_project_no DESC, barcode_item_no ASC LIMIT $limit OFFSET $offset";
        
        // Execute the query
        $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);
        
        // Process results
        $panels = [];
        
        if (!empty($result['data'])) {
            foreach ($result['data'] as $index => $row) {
                $projectName = getProjectNameFromContacts($row['project_no']);
                
                $stationStatuses = getBreakerStationStatuses(
                    $row['project_no'], 
                    $row['item_no'],
                    $row['production_no'],
                    $row['serial_no'],
                    $startDate, 
                    $endDate, 
                    false, 
                    $dateRangeProvided
                );
                
                $panels[] = [
                    'serial_no' => $row['serial_no'],
                    'project_no' => $row['project_no'],
                    'item_no' => $row['item_no'],
                    'production_no' => $row['production_no'],
                    'panel_no' => $row['panel_no'] ? $row['panel_no'] : '',
                    'project_name' => $projectName,
                    'stations' => $stationStatuses
                ];
            }
        }
        
        // Get total count for pagination
        $countSql = "
            SELECT 
                COUNT(DISTINCT CONCAT(barcode_serial_no, '_', barcode_project_no, '_', barcode_item_no, '_', barcode_production_no)) as total
            FROM 
                (
                    SELECT barcode_serial_no, barcode_project_no, barcode_item_no, barcode_production_no, up_date FROM tbl_transactions
                    UNION
                    SELECT barcode_serial_no, barcode_project_no, barcode_item_no, barcode_production_no, up_date FROM tbl_warehousebarcode
                ) t
            WHERE 1=1
            ";
        
        $countParams = [];
        
        // Apply date range filter only if date range is provided
        if ($dateRangeProvided) {
            $countSql .= " AND DATE(t.up_date) BETWEEN :startDate AND :endDate";
            $countParams[':startDate'] = $startDate;
            $countParams[':endDate'] = $endDate;
        } 
        // Default to today if no filters are provided
        else {
            $countSql .= " AND DATE(t.up_date) = CURDATE()";
        }
        
        $countResult = DbManager::fetchPDOQueryData('spectra_db', $countSql, $countParams);
        
        $totalPanels = 0;
        
        if (!empty($countResult['data'][0]['total'])) {
            $totalPanels = $countResult['data'][0]['total'];
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $panels,
            'pagination' => [
                'total' => $totalPanels,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($totalPanels / $limit)
            ],
            'filters' => [
                'clientName' => $clientName,
                'project' => $project,
                'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                'filterPriority' => $dateRangeProvided ? 'dateRange' : 'today',
                'tabType' => $tabType
            ]
        ]);
    }
}

function fetchPanelData($page, $limit, $offset, $startDate, $endDate, $clientName, $project, $dateRangeProvided, $tabType) {
    // Check if project filter is active (highest priority)
    $projectFilterActive = !empty($project);
    
    // Check if client name filter is active (second priority)
    $clientNameFilterActive = !empty($clientName);
    
    if ($projectFilterActive) {
        // PRIORITY 1: Project Filter
        $sapData = getSapPosData($project);
        
        if (empty($sapData)) {
            echo json_encode([
                'status' => 'success',
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => 0
                ],
                'filters' => [
                    'clientName' => $clientName,
                    'project' => $project,
                    'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                    'filterPriority' => 'project',
                    'tabType' => $tabType
                ]
            ]);
            return;
        }
        
        $checklistResult = getChecklistDataForPanelByProject($project);
        
        $mergedData = mergeSapWithChecklistData($sapData, $checklistResult['data'] ?? []);
        
        // Apply pagination to the merged results
        $totalPanels = count($mergedData);
        $paginatedPanels = array_slice($mergedData, $offset, $limit);
        
        // Process each panel to get station statuses
        $panels = [];
        foreach ($paginatedPanels as $index => $row) {
            $projectName = getProjectNameFromContacts($row['project_no']);
            
            $stationStatuses = getPanelStationStatuses(
                $row['project_no'], 
                $row['item_no'],
                $row['production_no'],
                $startDate, 
                $endDate, 
                true, 
                $dateRangeProvided
            );
            
            $panels[] = [
                'serial_no' => '',
                'project_no' => $row['project_no'],
                'item_no' => $row['item_no'],
                'production_no' => $row['production_no'],
                'panel_type' => $row['panel_type'] ? $row['panel_type'] : '',
                'panel_name' => $row['panel_name'] ? $row['panel_name'] : '',
                'typical_name' => $row['typical_name'] ? $row['typical_name'] : '',
                'project_name' => $projectName,
                'stations' => $stationStatuses
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $panels,
            'pagination' => [
                'total' => $totalPanels,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($totalPanels / $limit)
            ],
            'filters' => [
                'clientName' => $clientName,
                'project' => $project,
                'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                'filterPriority' => 'project',
                'tabType' => $tabType
            ]
        ]);
    } elseif ($clientNameFilterActive) {
        // PRIORITY 2: Client Name Filter
        // Get data from tbl_checklist_data where project_name matches clientName
        $checklistResult = getChecklistDataForPanelByClientName($clientName);
        
        if (empty($checklistResult['data'])) {
            echo json_encode([
                'status' => 'success',
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => 0
                ],
                'filters' => [
                    'clientName' => $clientName,
                    'project' => $project,
                    'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                    'filterPriority' => 'clientName',
                    'tabType' => $tabType
                ]
            ]);
            return;
        }
        
        $mergedData = $checklistResult['data'];
        
        // Apply pagination to the merged results
        $totalPanels = count($mergedData);
        $paginatedPanels = array_slice($mergedData, $offset, $limit);
        
        // Process each panel to get station statuses
        $panels = [];
        foreach ($paginatedPanels as $index => $row) {
            $projectName = getProjectNameFromContacts($row['project_no']);
            
            $stationStatuses = getPanelStationStatuses(
                $row['project_no'], 
                $row['item_no'],
                $row['production_no'],
                $startDate, 
                $endDate, 
                true, 
                $dateRangeProvided
            );
            
            $panels[] = [
                'serial_no' => '',
                'project_no' => $row['project_no'],
                'item_no' => $row['item_no'],
                'production_no' => $row['production_no'],
                'panel_type' => $row['panel_type'] ? $row['panel_type'] : '',
                'panel_name' => $row['panel_name'] ? $row['panel_name'] : '',
                'typical_name' => $row['typical_name'] ? $row['typical_name'] : '',
                'project_name' => $projectName,
                'stations' => $stationStatuses
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $panels,
            'pagination' => [
                'total' => $totalPanels,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($totalPanels / $limit)
            ],
            'filters' => [
                'clientName' => $clientName,
                'project' => $project,
                'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                'filterPriority' => 'clientName',
                'tabType' => $tabType
            ]
        ]);
    } else {
        // PRIORITY 3: Date Range Filter (or default to today)
        // Build SQL query for unique order_no+item_no combinations, fetching latest by create_date
        $sql = "
            SELECT 
                ranked.order_no,
                ranked.item_no,
                ranked.production_order_no,
                ranked.project_name,
                ranked.panel_type,
                ranked.location_name,
                ranked.typical_name
            FROM (
                SELECT 
                    cd.order_no,
                    cd.item_no,
                    cd.production_order_no,
                    cd.project_name,
                    cd.panel_type,
                    cd.location_name,
                    cd.typical_name,
                    ROW_NUMBER() OVER (PARTITION BY cd.order_no, cd.item_no ORDER BY cd.create_date DESC) as rn
                FROM 
                    tbl_checklist_data cd
                WHERE 1=1
        ";

        // Initialize params array
        $params = [];

        // Apply date range filter only if date range is provided
        if ($dateRangeProvided) {
            $sql .= " AND DATE(cd.create_date) BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        } 
        // Default to today if no filters are provided
        else {
            $sql .= " AND DATE(cd.create_date) = CURDATE()";
        }

        $sql .= "
            ) ranked
            WHERE ranked.rn = 1
            ORDER BY ranked.order_no DESC, ranked.item_no ASC 
            LIMIT $limit OFFSET $offset
        ";

        // Execute the query
        $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);
        
        // Process results
        $panels = [];
        
        if (!empty($result['data'])) {
            foreach ($result['data'] as $index => $row) {
                $projectName = getProjectNameFromContacts($row['order_no']);
                
                $stationStatuses = getPanelStationStatuses(
                    $row['order_no'], 
                    $row['item_no'],
                    $row['production_order_no'],
                    $startDate, 
                    $endDate, 
                    false, 
                    $dateRangeProvided
                );
                
                $panels[] = [
                    'serial_no' => '',
                    'project_no' => $row['order_no'],
                    'item_no' => $row['item_no'],
                    'production_no' => $row['production_order_no'],
                    'panel_type' => $row['panel_type'] ? $row['panel_type'] : '',
                    'panel_name' => $row['location_name'] ? $row['location_name'] : '',
                    'typical_name' => $row['typical_name'] ? $row['typical_name'] : '',
                    'project_name' => $projectName,
                    'stations' => $stationStatuses
                ];
            }
        }
        
        // Get total count for pagination
        $countSql = "
            SELECT 
                COUNT(DISTINCT CONCAT(cd.order_no, '_', cd.item_no)) as total
            FROM 
                tbl_checklist_data cd
            WHERE 1=1
        ";
        
        $countParams = [];
        
        // Apply date range filter only if date range is provided
        if ($dateRangeProvided) {
            $countSql .= " AND DATE(cd.create_date) BETWEEN :startDate AND :endDate";
            $countParams[':startDate'] = $startDate;
            $countParams[':endDate'] = $endDate;
        } 
        // Default to today if no filters are provided
        else {
            $countSql .= " AND DATE(cd.create_date) = CURDATE()";
        }
        
        $countResult = DbManager::fetchPDOQueryData('spectra_db', $countSql, $countParams);
        
        $totalPanels = 0;
        
        if (!empty($countResult['data'][0]['total'])) {
            $totalPanels = $countResult['data'][0]['total'];
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $panels,
            'pagination' => [
                'total' => $totalPanels,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($totalPanels / $limit)
            ],
            'filters' => [
                'clientName' => $clientName,
                'project' => $project,
                'dateRange' => $dateRangeProvided ? $_GET['dateRange'] : '',
                'filterPriority' => $dateRangeProvided ? 'dateRange' : 'today',
                'tabType' => $tabType
            ]
        ]);
    }
}

/**
 * Merge SAP data with checklist data
 */
function mergeSapWithChecklistData($sapData, $checklistData) {
    $checklistMap = [];
    foreach ($checklistData as $checklist) {
        $key = $checklist['project_no'] . '_' . $checklist['item_no'] . '_' . 
               $checklist['panel_name'] . '_' . $checklist['typical_name'];
        $checklistMap[$key] = $checklist;
    }
    
    $mergedData = [];
    foreach ($sapData as $sapRow) {
        $key = $sapRow['ProjectNo'] . '_' . $sapRow['PosNo'] . '_' . 
               $sapRow['PanelName'] . '_' . $sapRow['TypicalName'];
        
        if (isset($checklistMap[$key])) {
            $mergedData[] = $checklistMap[$key];
        } else {
            $mergedData[] = [
                'project_no' => $sapRow['ProjectNo'],
                'item_no' => $sapRow['PosNo'],
                'production_no' => '',
                'panel_type' => '',
                'panel_name' => $sapRow['PanelName'],
                'typical_name' => $sapRow['TypicalName']
            ];
        }
    }
    
    return $mergedData;
}

/**
 * Get transaction data for breaker by project number
 * OPTIMIZED: Uses generated columns instead of REGEXP_SUBSTR
 */
function getTransactionDataForBreakerByProject($projectNo) {
    $sql = "
        SELECT 
            barcode_serial_no AS serial_no,
            barcode_project_no AS project_no,
            barcode_item_no AS item_no,
            barcode_production_no AS production_no,
            '' AS panel_no,
            barcode
        FROM 
            (
                SELECT barcode, barcode_serial_no, barcode_project_no, barcode_item_no, barcode_production_no FROM tbl_transactions
                UNION
                SELECT barcode, barcode_serial_no, barcode_project_no, barcode_item_no, barcode_production_no FROM tbl_warehousebarcode
            ) t
        WHERE 
            barcode_project_no = :projectNo
        ORDER BY 
            barcode_item_no ASC
    ";
    
    $params = [':projectNo' => $projectNo];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);
    
    return $result;
}

/**
 * Get transaction data for breaker by client name (using factory numbers)
 */
function getTransactionDataForBreakerByClientName($factoryNumbers) {
    // Create placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($factoryNumbers), '?'));
    
    $sql = "
        SELECT 
            barcode_serial_no AS serial_no,
            barcode_project_no AS project_no,
            barcode_item_no AS item_no,
            barcode_production_no AS production_no,
            '' AS panel_no,
            barcode
        FROM 
            (
                SELECT barcode, barcode_serial_no, barcode_project_no, barcode_item_no, barcode_production_no FROM tbl_transactions
                UNION
                SELECT barcode, barcode_serial_no, barcode_project_no, barcode_item_no, barcode_production_no FROM tbl_warehousebarcode
            ) t
        WHERE 
            barcode_project_no IN ($placeholders)
        ORDER BY 
            barcode_project_no DESC, barcode_item_no ASC
    ";
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $factoryNumbers);
    
    return $result;
}

/**
 * Get checklist data for panel by client name
 */
function getChecklistDataForPanelByClientName($clientName) {
    $sql = "
        WITH ranked_data AS (
            SELECT 
                order_no AS project_no,
                item_no,
                production_order_no AS production_no,
                panel_type,
                location_name AS panel_name,
                typical_name, 
                create_date,
                ROW_NUMBER() OVER (PARTITION BY order_no, item_no, panel_type, location_name, typical_name ORDER BY create_date DESC) AS rn
            FROM 
                tbl_checklist_data
            WHERE 
                project_name = :clientName
        )
        SELECT 
            project_no,
            item_no,
            production_no,
            panel_type,
            panel_name,
            typical_name,
            create_date
        FROM 
            ranked_data
        WHERE 
            rn = 1
        ORDER BY 
            project_no DESC, item_no ASC
    ";
    
    $params = [':clientName' => $clientName];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);
    
    return $result;
}

/**
 * Get checklist data for panel by project number
 */
function getChecklistDataForPanelByProject($projectNo) {
    $sql = "
        WITH ranked_data AS (
            SELECT 
                order_no AS project_no,
                item_no,
                production_order_no AS production_no,
                panel_type,
                location_name AS panel_name,
                typical_name, 
                create_date,
                ROW_NUMBER() OVER (PARTITION BY order_no, item_no, panel_type, location_name, typical_name ORDER BY create_date DESC) AS rn
            FROM 
                tbl_checklist_data
            WHERE 
                order_no = :projectNo
        )
        SELECT 
            project_no,
            item_no,
            production_no,
            panel_type,
            panel_name,
            typical_name,
            create_date
        FROM 
            ranked_data
        WHERE 
            rn = 1
        ORDER BY 
            item_no ASC
    ";
    
    $params = [':projectNo' => $projectNo];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);
    
    return $result;
}

/**
 * Get factory numbers by client name from MTool_INKWA database
 */
function getFactoryNumbersByClientName($clientName) {
    $sql = "
        SELECT DISTINCT FactoryNumber
        FROM dbo.OneX_ProjectContacts
        WHERE ProjectName = :projectName
        AND FactoryNumber IS NOT NULL
    ";
    
    $params = [':projectName' => $clientName];
    
    $result = DbManager::fetchPDOQueryData('MTool_INKWA', $sql, $params);
    
    $factoryNumbers = [];
    if (!empty($result['data'])) {
        foreach ($result['data'] as $row) {
            $factoryNumbers[] = $row['FactoryNumber'];
        }
    }
    
    return $factoryNumbers;
}

/**
 * Get project name from OneX_ProjectContacts table
 */
function getProjectNameFromContacts($factoryNumber) {
    $sql = "SELECT ProjectName
        FROM dbo.OneX_ProjectContacts
        WHERE FactoryNumber = :factoryNumber";
    
    $params = [':factoryNumber' => $factoryNumber];
    
    $result = DbManager::fetchPDOQueryData('MTool_INKWA', $sql, $params);
    
    if (!empty($result['data'][0]['ProjectName'])) {
        return $result['data'][0]['ProjectName'];
    }
    
    return '';
}

/**
 * Get status for all breaker stations
 * OPTIMIZED: Uses generated columns instead of REGEXP_SUBSTR
 */
function getBreakerStationStatuses($projectNo, $itemNo, $productionNo, $serialNo, $startDate, $endDate, $projectFilterActive, $dateRangeProvided) {
    
    $stations = [
        0 => ['id' => 0, 'name' => 'Breaker Warehouse', 'status' => 'not_started', 'data' => null],
        1 => ['id' => 1, 'name' => 'Breaker Dropping', 'status' => 'not_started', 'data' => null],
        2 => ['id' => 2, 'name' => 'Breaker Stamping', 'status' => 'not_started', 'data' => null],
        3 => ['id' => 3, 'name' => 'Panel LV Box', 'status' => 'not_started', 'data' => null],
        4 => ['id' => 4, 'name' => 'Panel Structure', 'status' => 'not_started', 'data' => null],
        5 => ['id' => 5, 'name' => 'Panel Warehouse', 'status' => 'not_started', 'data' => null],
        6 => ['id' => 6, 'name' => 'Panel Assembly', 'status' => 'not_started', 'data' => null],
        7 => ['id' => 7, 'name' => 'Final Testing', 'status' => 'not_started', 'data' => null]
    ];
    
    $stationData = [];
    
    // OPTIMIZED: Single combined query for both warehouse and transaction data
    $combinedSql = "
        WITH all_data AS (
            -- Warehouse data
            SELECT 
                barcode_serial_no AS serial_no,
                barcode_project_no AS project_no,
                barcode_item_no AS item_no,
                barcode_production_no AS production_no,
                'Breaker Warehouse' as station_name,
                0 as station_id,
                up_date as start_time,
                up_date as end_date,
                1 as status,
                remark as remarks,
                'Warehouse User' as user_id,
                up_date,
                NULL as stage_id,
                ROW_NUMBER() OVER (PARTITION BY 'Breaker Warehouse' ORDER BY up_date DESC) as rn
            FROM 
                tbl_warehousebarcode
            WHERE 
                barcode_project_no = :projectNo
                AND barcode_item_no = :itemNo
                AND barcode_production_no = :productionNo
                AND barcode_serial_no = :serialNo
    ";
    
    if (!$projectFilterActive && $dateRangeProvided) {
        $combinedSql .= " AND DATE(up_date) BETWEEN :startDate AND :endDate";
    } else if (!$projectFilterActive && !$dateRangeProvided) {
        $combinedSql .= " AND DATE(up_date) = CURDATE()";
    }
    
    $combinedSql .= "
            UNION ALL
            
            -- Transaction data
            SELECT 
                barcode_serial_no AS serial_no,
                barcode_project_no AS project_no,
                barcode_item_no AS item_no,
                barcode_production_no AS production_no,
                station_name,
                station_id,
                start_time,
                end_date,
                status,
                remarks,
                user_id,
                up_date,
                stage_id,
                ROW_NUMBER() OVER (PARTITION BY station_name ORDER BY stage_id DESC, up_date DESC) as rn
            FROM 
                tbl_transactions
            WHERE 
                barcode_project_no = :projectNo
                AND barcode_item_no = :itemNo
                AND barcode_production_no = :productionNo
                AND barcode_serial_no = :serialNo
    ";
    
    if (!$projectFilterActive && $dateRangeProvided) {
        $combinedSql .= " AND DATE(up_date) BETWEEN :startDate AND :endDate";
    } else if (!$projectFilterActive && !$dateRangeProvided) {
        $combinedSql .= " AND DATE(up_date) = CURDATE()";
    }
    
    $combinedSql .= "
        )
        SELECT 
            serial_no,
            project_no,
            item_no,
            production_no,
            station_name,
            station_id,
            start_time,
            end_date,
            status,
            remarks,
            user_id,
            up_date,
            stage_id
        FROM all_data
        WHERE rn = 1
        ORDER BY station_id, up_date DESC
    ";
    
    $combinedParams = [
        ':projectNo' => $projectNo,
        ':itemNo' => $itemNo,
        ':productionNo' => $productionNo,
        ':serialNo' => $serialNo
    ];
    
    if (!$projectFilterActive && $dateRangeProvided) {
        $combinedParams[':startDate'] = $startDate;
        $combinedParams[':endDate'] = $endDate;
    }
    
    $combinedResult = DbManager::fetchPDOQueryData('spectra_db', $combinedSql, $combinedParams);
    
    // Query to get all stage_ids for each station to calculate min and max up_date
    $stageIdSql = "
        SELECT 
            station_name,
            station_id,
            MIN(up_date) as first_up_date,
            MAX(up_date) as last_up_date,
            status,
            remarks,
            user_id,
            -- Get the user_id from the first stage_id (earliest up_date)
            (SELECT user_id FROM tbl_transactions t2 
             WHERE t2.station_name = t1.station_name 
             AND t2.barcode_project_no = :projectNo
             AND t2.barcode_item_no = :itemNo
             AND t2.barcode_production_no = :productionNo
             AND t2.barcode_serial_no = :serialNo
             ORDER BY t2.up_date ASC LIMIT 1) as first_user_id,
            -- Get the user_id from the last stage_id (latest up_date)
            (SELECT user_id FROM tbl_transactions t3 
             WHERE t3.station_name = t1.station_name 
             AND t3.barcode_project_no = :projectNo
             AND t3.barcode_item_no = :itemNo
             AND t3.barcode_production_no = :productionNo
             AND t3.barcode_serial_no = :serialNo
             ORDER BY t3.up_date DESC LIMIT 1) as last_user_id
        FROM 
            tbl_transactions t1
        WHERE 
            barcode_project_no = :projectNo
            AND barcode_item_no = :itemNo
            AND barcode_production_no = :productionNo
            AND barcode_serial_no = :serialNo
    ";
    
    if (!$projectFilterActive && $dateRangeProvided) {
        $stageIdSql .= " AND DATE(up_date) BETWEEN :startDate AND :endDate";
    } else if (!$projectFilterActive && !$dateRangeProvided) {
        $stageIdSql .= " AND DATE(up_date) = CURDATE()";
    }
    
    $stageIdSql .= "
        GROUP BY 
            station_name, 
            station_id, 
            status, 
            remarks, 
            user_id
        ORDER BY 
            station_id ASC
    ";
    
    $stageIdParams = [
        ':projectNo' => $projectNo,
        ':itemNo' => $itemNo,
        ':productionNo' => $productionNo,
        ':serialNo' => $serialNo
    ];
    
    if (!$projectFilterActive && $dateRangeProvided) {
        $stageIdParams[':startDate'] = $startDate;
        $stageIdParams[':endDate'] = $endDate;
    }
    
    $stageIdResult = DbManager::fetchPDOQueryData('spectra_db', $stageIdSql, $stageIdParams);
    
    // Process stage_id results - this will be our primary data source
    if (!empty($stageIdResult['data'])) {
        foreach ($stageIdResult['data'] as $row) {
            $stationName = $row['station_name'];
            
            $stationData[$stationName] = [
                'station_id' => $row['station_id'],
                'status' => $row['status'],
                'remarks' => $row['remarks'],
                'user_id' => $row['user_id'],
                'first_up_date' => $row['first_up_date'],
                'last_up_date' => $row['last_up_date'],
                'first_user_id' => $row['first_user_id'],
                'last_user_id' => $row['last_user_id']
            ];
        }
    }
    
    // Fallback to combined results if stage_id query didn't return data
    if (empty($stationData) && !empty($combinedResult['data'])) {
        $transactionsByStation = [];
        
        foreach ($combinedResult['data'] as $row) {
            $stationName = $row['station_name'];
            
            if (!isset($transactionsByStation[$stationName])) {
                $transactionsByStation[$stationName] = [];
            }
            $transactionsByStation[$stationName][] = $row;
        }
        
        foreach ($transactionsByStation as $stationName => $transactions) {
            usort($transactions, function($a, $b) {
                if ($a['stage_id'] !== null && $b['stage_id'] !== null) {
                    return (int)$a['stage_id'] - (int)$b['stage_id'];
                }
                return strtotime($a['up_date']) - strtotime($b['up_date']);
            });
            
            $upDateStart = $transactions[0]['up_date'];
            $upDateEnd = $transactions[count($transactions) - 1]['up_date'];
            $latestTransaction = $transactions[count($transactions) - 1];
            
            $stationData[$stationName] = [
                'station_id' => $latestTransaction['station_id'],
                'status' => $latestTransaction['status'],
                'remarks' => $latestTransaction['remarks'],
                'user_id' => $latestTransaction['user_id'],
                'first_up_date' => $upDateStart,
                'last_up_date' => $upDateEnd,
                'first_user_id' => $transactions[0]['user_id'],
                'last_user_id' => $latestTransaction['user_id']
            ];
        }
    }
    
    // Map station data to stations array
    foreach ($stationData as $stationName => $row) {
        $stationId = null;
        
        if (strpos($stationName, 'Breaker Warehouse') !== false) {
            $stationId = 0;
        } elseif (strpos($stationName, 'Dropping') !== false) {
            $stationId = 1;
        } elseif (strpos($stationName, 'Stamping') !== false) {
            $stationId = 2;
        } elseif (strpos($stationName, 'Panel LV Box') !== false) {
            $stationId = 3;
        } elseif (strpos($stationName, 'Panel Structure') !== false) {
            $stationId = 4;
        } elseif (strpos($stationName, 'Panel Warehouse') !== false) {
            $stationId = 5;
        } elseif (strpos($stationName, 'Panel Assembly') !== false) {
            $stationId = 6;
        } elseif (strpos($stationName, 'Panel Testing') !== false) {
            $stationId = 7;
        }
        
        if ($stationId !== null) {
            $status = 'not_started';
            if ($row['status'] == 1) {
                $status = 'done';
            } elseif ($row['status'] == 0) {
                $status = 'in_progress';
            } elseif ($row['status'] == 2) {
                $status = 'skipped';
            }
            
            $startTime = $row['first_up_date'];
            $endDate = $row['last_up_date'];
            $userId = $row['first_user_id'] ?? $row['user_id'];
            
            $stations[$stationId]['status'] = $status;
            $stations[$stationId]['data'] = [
                'start_time' => $startTime,
                'end_date' => $endDate,
                'user_id' => $userId,
                'remarks' => isset($row['remarks']) ? $row['remarks'] : ''
            ];
        }
    }
    
    if (isset($stations[0]['data'])) {
        $stations[0]['data']['is_breaker_warehouse'] = true;
    }
    
    $lastCompletedStation = -1;
    for ($i = 0; $i < count($stations); $i++) {
        if ($stations[$i]['status'] === 'done') {
            $lastCompletedStation = $i;
        }
    }
    
    return $stations;
}

/**
 * Get status for all panel stations
 * OPTIMIZED: Combined 5 queries into 1 single query per panel
 */
function getPanelStationStatuses($orderNo, $itemNo, $productionOrderNo, $startDate, $endDate, $projectFilterActive, $dateRangeProvided) {
    
    $stations = [
        0 => ['id' => 0, 'name' => 'Breaker Warehouse', 'status' => 'not_started', 'data' => null],
        1 => ['id' => 1, 'name' => 'Breaker Dropping', 'status' => 'not_started', 'data' => null],
        2 => ['id' => 2, 'name' => 'Breaker Stamping', 'status' => 'not_started', 'data' => null],
        3 => ['id' => 3, 'name' => 'Panel LV Box', 'status' => 'not_started', 'data' => null],
        4 => ['id' => 4, 'name' => 'Panel Structure', 'status' => 'not_started', 'data' => null],
        5 => ['id' => 5, 'name' => 'Panel Warehouse', 'status' => 'not_started', 'data' => null],
        6 => ['id' => 6, 'name' => 'Panel Assembly', 'status' => 'not_started', 'data' => null],
        7 => ['id' => 7, 'name' => 'Final Testing', 'status' => 'not_started', 'data' => null]
    ];
    
    // OPTIMIZED: Single query to get all station data at once
    $sql = "
        WITH ranked_data AS (
            SELECT 
                order_no,
                item_no,
                production_order_no,
                checklist_name,
                create_date as start_time,
                updated_date as end_date,
                status,
                remark,
                user_name,
                CASE 
                    WHEN checklist_name LIKE '%LV%' THEN 'Panel LV Box'
                    WHEN checklist_name LIKE '%Structure%' THEN 'Panel Structure'
                    WHEN checklist_name LIKE '%Warehouse%' THEN 'Panel Warehouse'
                    WHEN checklist_name LIKE '%Assembly%' THEN 'Panel Assembly'
                    WHEN checklist_name LIKE '%Testing%' OR checklist_name LIKE '%Test%' THEN 'Final Testing'
                END as station_name,
                CASE 
                    WHEN checklist_name LIKE '%LV%' THEN 3
                    WHEN checklist_name LIKE '%Structure%' THEN 4
                    WHEN checklist_name LIKE '%Warehouse%' THEN 5
                    WHEN checklist_name LIKE '%Assembly%' THEN 6
                    WHEN checklist_name LIKE '%Testing%' OR checklist_name LIKE '%Test%' THEN 7
                END as station_id,
                ROW_NUMBER() OVER (
                    PARTITION BY 
                        CASE 
                            WHEN checklist_name LIKE '%LV%' THEN 'Panel LV Box'
                            WHEN checklist_name LIKE '%Structure%' THEN 'Panel Structure'
                            WHEN checklist_name LIKE '%Warehouse%' THEN 'Panel Warehouse'
                            WHEN checklist_name LIKE '%Assembly%' THEN 'Panel Assembly'
                            WHEN checklist_name LIKE '%Testing%' OR checklist_name LIKE '%Test%' THEN 'Final Testing'
                        END
                    ORDER BY create_date DESC
                ) as rn
            FROM 
                tbl_checklist_data
            WHERE 
                order_no = :orderNo
                AND item_no = :itemNo
                AND production_order_no = :productionOrderNo
                AND (
                    checklist_name LIKE '%LV%' 
                    OR checklist_name LIKE '%Structure%'
                    OR checklist_name LIKE '%Warehouse%'
                    OR checklist_name LIKE '%Assembly%'
                    OR checklist_name LIKE '%Testing%'
                    OR checklist_name LIKE '%Test%'
                )
    ";
    
    if (!$projectFilterActive && $dateRangeProvided) {
        $sql .= " AND DATE(create_date) BETWEEN :startDate AND :endDate";
    } else if (!$projectFilterActive && !$dateRangeProvided) {
        $sql .= " AND DATE(create_date) = CURDATE()";
    }
    
    $sql .= "
        )
        SELECT 
            order_no,
            item_no,
            production_order_no,
            checklist_name,
            start_time,
            end_date,
            status,
            remark,
            user_name,
            station_name,
            station_id
        FROM ranked_data
        WHERE rn = 1
        ORDER BY station_id ASC
    ";
    
    $params = [
        ':orderNo' => $orderNo,
        ':itemNo' => $itemNo,
        ':productionOrderNo' => $productionOrderNo
    ];
    
    if (!$projectFilterActive && $dateRangeProvided) {
        $params[':startDate'] = $startDate;
        $params[':endDate'] = $endDate;
    }
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);
    
    // Process results
    if (!empty($result['data'])) {
        foreach ($result['data'] as $row) {
            $stationId = $row['station_id'];
            
            if ($stationId !== null && isset($stations[$stationId])) {
                $status = 'not_started';
                if ($row['status'] == 1) {
                    $status = 'done';
                } elseif ($row['status'] == 0) {
                    $status = 'in_progress';
                }
                
                $stations[$stationId]['status'] = $status;
                $stations[$stationId]['data'] = [
                    'start_time' => $row['start_time'],
                    'end_date' => $row['end_date'],
                    'user_id' => $row['user_name'],
                    'remarks' => $row['remark'] ?? '',
                    'is_panel_lv_box' => ($stationId === 3)
                ];
            }
        }
    }
    
    $lastCompletedStation = 2;
    for ($i = 3; $i < count($stations); $i++) {
        if ($stations[$i]['status'] === 'done') {
            $lastCompletedStation = $i;
        }
    }
    
    return $stations;
}

/**
 * Get list of project numbers for autocomplete
 * OPTIMIZED: Uses generated columns instead of REGEXP_SUBSTR
 */
function getProjectNumbers() {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $tabType = isset($_GET['tabType']) ? $_GET['tabType'] : 'breaker';
    
    if ($tabType === 'breaker') {
        $sql = "
        SELECT DISTINCT 
            barcode_project_no AS project_no,
            barcode_project_no AS name
        FROM 
            (
                SELECT barcode_project_no FROM tbl_transactions
                UNION
                SELECT barcode_project_no FROM tbl_warehousebarcode
            ) t
        WHERE 
            barcode_project_no LIKE :search
        ORDER BY 
            barcode_project_no DESC
        LIMIT 10
        ";
    } else {
        $sql = "
        SELECT DISTINCT 
            order_no AS project_no,
            order_no AS name
        FROM 
            tbl_checklist_data
        WHERE 
            order_no LIKE :search
        ORDER BY 
            order_no DESC
        LIMIT 10
        ";
    }
    
    $params = [':search' => '%' . $search . '%'];
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);
    
    $projects = [];
    
    if (!empty($result['data'])) {
        foreach ($result['data'] as $row) {
            $projects[] = [
                'project_no' => $row['project_no'],
                'name' => $row['name']
            ];
        }
    }
    
    echo json_encode(['items' => $projects]);
}

/**
 * Get list of client names for autocomplete
 */
function getClientNames() {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    $sql = "
        SELECT DISTINCT TOP 10
            ProjectName,
            ProjectName AS name
        FROM 
            dbo.OneX_ProjectContacts
        WHERE 
            ProjectName IS NOT NULL
            AND ProjectName LIKE :search
        ORDER BY 
            ProjectName ASC
    ";
    
    $params = [':search' => '%' . $search . '%'];
    
    $result = DbManager::fetchPDOQueryData('MTool_INKWA', $sql, $params);
    
    $clientNames = [];
    
    if (!empty($result['data'])) {
        foreach ($result['data'] as $row) {
            $clientNames[] = [
                'project_name' => $row['ProjectName'],
                'name' => $row['name']
            ];
        }
    }
    
    echo json_encode(['items' => $clientNames]);
}

/**
 * Get SAP POS data for breaker/panel tab from MTool_INKWA database
 */
function getSapPosData($projectNo) {
    $sql = "
        SELECT 
            SapPosData.*,
            dbo.OneX_ProjectLeads.ProjectName,
            dbo.OneX_ProjectLeads.OrderManager,
            dbo.OneX_ProjectTrackingEE.Product
        FROM(
            SELECT 
                ProjectNo,
                RIGHT('000000' + CAST(REPLACE(PosNo, ',', '') AS VARCHAR(6)), 6) AS PosNo,
                LocationCode AS PanelName,
                TypicalCode AS TypicalName
            FROM 
                dbo.OneX_SapPosData 
            WHERE 
                ProjectNo = :projectNo AND
                TypicalCode IS NOT NULL
                AND LocationCode IS NOT NULL
        ) AS SapPosData
        JOIN
            dbo.OneX_ProjectLeads
        ON
            dbo.OneX_ProjectLeads.FactoryNumber = SapPosData.ProjectNo
        JOIN
            dbo.OneX_ProjectTrackingEE
        ON
            dbo.OneX_ProjectTrackingEE.FactoryNumber = SapPosData.ProjectNo
        ORDER BY 
            SapPosData.PosNo ASC
    ";
    
    $params = [':projectNo' => $projectNo];
    
    $result = DbManager::fetchPDOQueryData('MTool_INKWA', $sql, $params);
    
    if (!empty($result['data'])) {
        return $result['data'];
    }
    
    return [];
}
?>