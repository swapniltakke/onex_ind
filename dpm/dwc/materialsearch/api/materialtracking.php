<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
ini_set('memory_limit', '1024M');

$project = trim($_GET["project"]);
$panel = trim($_GET["panel"]);
$source_type = trim($_GET["source_type"]);
$shortText = trim($_GET["shortText"]);
$materialNumbers = trim($_GET["materialNumbers"]);
$materialNumbers2 = trim($_GET["materialNumbers2"]);
$mrp = trim($_GET["mrp"]);
$type = trim($_GET["type"]);

$type = $_GET["type"] ?? $_POST["type"];
switch ($type) {
    case "get_suggestions":
        getSuggestions();
        break;   
    case "get_material_tracking":
        getMaterialTracking();     
        break;
    case "get_all_data":
        exportAll();    
        break;    
    default:
        break;
}
exit;

function getSuggestions() {
    $search = $_POST['search'] ?? '';
    $field = $_POST['field'] ?? '';
    
    if (empty($search) || empty($field)) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }

    try {
        $params = [];
        $columnName = '';
        
        // Map field types to column names
        switch ($field) {
            case 'tracking':
                $columnName = 'tracking_number';
                break;
            case 'vendor':
                $columnName = 'vendor_name';
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid field type']);
                return;
        }

        // Build query with exact sequence matching
        $sql = "SELECT DISTINCT $columnName as value 
                FROM sap_material_tracking 
                WHERE $columnName LIKE :search 
                ORDER BY $columnName 
                LIMIT 10";
        
        $params[':search'] = "%$search%";

        // Execute query
        $result = DbManager::fetchPDOQuery('rpa', $sql, $params);

        if (isset($result['data'])) {
            // Filter results to ensure exact sequence match
            $filteredData = array_filter($result['data'], function($item) use ($search) {
                return stripos($item['value'], $search) !== false;
            });

            echo json_encode([
                'success' => true,
                'data' => array_values($filteredData) // Reset array keys
            ]);
        } else {
            echo json_encode(['success' => true, 'data' => []]);
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching suggestions: ' . $e->getMessage()
        ]);
    }
}

function getMaterialTracking() {
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    
    // Get and sanitize filter values
    $trackingFilter = isset($_POST['trackingFilter']) ? sanitizeFilterInput($_POST['trackingFilter']) : '';
    $vendorFilter = isset($_POST['vendorFilter']) ? sanitizeFilterInput($_POST['vendorFilter']) : '';
    $stillToBeDeliveredFilter = isset($_POST['stillToBeDeliveredFilter']) && $_POST['stillToBeDeliveredFilter'] === 'true';
    $applyingFilters = isset($_POST['applyingFilters']) && $_POST['applyingFilters'] === 'true';
    $isInitialLoad = isset($_POST['isInitialLoad']) && $_POST['isInitialLoad'] === 'true';
    
    // Get and sanitize search value
    $search = isset($_POST['search']['value']) ? sanitizeFilterInput($_POST['search']['value']) : '';

    // Define searchable columns
    $searchableColumns = [
        'tracking_number',
        'sales_order_no',
        'production_order_no',
        'purchase_order_no',
        'purchase_item',
        'vendor_name',
        'vendor_code',
        'po_created_by',
        'our_reference',
        'po_date',
        'material',
        'short_text',
        'po_quantity',
        'quantity_delivered',
        'still_to_be_delivered',
        'delivery_date',
        'delivery_date_vendor_confirmation',
        'account_assignment_category',
        'net_value_po_currency',
        'currency',
        'purch_grp',
        'deletion_ind',
        'storage_no',
        'storage_location_desc'
    ];

    // Base query
    $baseQuery = "
        FROM sap_material_tracking
        WHERE 1=1";

    $params = [];

    // Add filters with improved handling for special characters
    if ($applyingFilters || $isInitialLoad) {
        if (!empty($trackingFilter)) {
            $baseQuery .= " AND LOWER(tracking_number) LIKE LOWER(:trackingFilter)";
            $params[':trackingFilter'] = "%" . $trackingFilter . "%";
        }
        if (!empty($vendorFilter)) {
            // Use multiple conditions for better matching
            $baseQuery .= " AND (
                LOWER(vendor_name) LIKE LOWER(:vendorFilter)
                OR LOWER(REPLACE(vendor_name, '.', '')) LIKE LOWER(:vendorFilterNoDot)
                OR LOWER(REPLACE(REPLACE(vendor_name, '.', ''), ' ', '')) LIKE LOWER(:vendorFilterNoSpace)
            )";
            $params[':vendorFilter'] = "%" . $vendorFilter . "%";
            $params[':vendorFilterNoDot'] = "%" . str_replace('.', '', $vendorFilter) . "%";
            $params[':vendorFilterNoSpace'] = "%" . str_replace(['.', ' '], '', $vendorFilter) . "%";
        }
        if ($stillToBeDeliveredFilter) {
            $baseQuery .= " AND CAST(NULLIF(still_to_be_delivered, '') AS DECIMAL(10,2)) > 1";
        }
    }

    // Add search condition with improved handling
    if (!empty($search)) {
        $searchConditions = [];
        foreach ($searchableColumns as $index => $column) {
            $paramName = ":search_" . $index;
            $searchConditions[] = "LOWER($column) LIKE LOWER($paramName)";
            $params[$paramName] = "%" . $search . "%";
        }
        $baseQuery .= " AND (" . implode(" OR ", $searchConditions) . ")";
    }

    // Count total records
    $countQuery = "SELECT COUNT(*) as total FROM sap_material_tracking";
    $totalResult = DbManager::fetchPDOQuery('rpa', $countQuery);
    $totalRecords = $totalResult['data'][0]['total'];

    // Count filtered records
    $filteredCountQuery = "SELECT COUNT(*) as total " . $baseQuery;
    $filteredResult = DbManager::fetchPDOQuery('rpa', $filteredCountQuery, $params);
    $filteredRecords = $filteredResult['data'][0]['total'];

    // Sorting
    $order = '';
    if (isset($_POST['order'])) {
        $orderColumn = intval($_POST['order'][0]['column']);
        $orderDir = in_array(strtoupper($_POST['order'][0]['dir']), ['ASC', 'DESC']) ? 
                   strtoupper($_POST['order'][0]['dir']) : 'DESC';
        
        if (isset($searchableColumns[$orderColumn])) {
            $order = " ORDER BY " . $searchableColumns[$orderColumn] . " " . $orderDir;
        }
    } else {
        $order = " ORDER BY tracking_number DESC";
    }

    // Final query with pagination
    $query = "
        SELECT 
            " . implode(",\n            ", $searchableColumns) . "
        " . $baseQuery . $order . "
        LIMIT :start, :length";

    $params[':start'] = $start;
    $params[':length'] = $length;

    try {
        $result = DbManager::fetchPDOQuery('rpa', $query, $params);
        
        if ($result && isset($result['data'])) {
            // Sanitize output data
            $sanitizedData = array_map(function($row) {
                return array_map(function($value) {
                    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }, $row);
            }, $result['data']);

            $response = [
                'draw' => $draw,
                'recordsTotal' => (int)$totalRecords,
                'recordsFiltered' => (int)$filteredRecords,
                'data' => $sanitizedData
            ];
            
            echo json_encode($response, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        } else {
            echo json_encode([
                "draw" => $draw,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "No data found"
            ]);
        }
    } catch (Exception $e) {
        error_log("Material Tracking Error: " . $e->getMessage());
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => [],
            "error" => 'Error executing query: ' . $e->getMessage()
        ]);
    }
}

// Add this helper function at the top of your file
function sanitizeFilterInput($input) {
    if (empty($input)) return '';
    
    // Remove any potential SQL injection attempts
    $input = strip_tags($input);
    $input = str_replace(['\\', '"', "'", ';'], '', $input);
    
    // Convert HTML entities to their corresponding characters
    $input = html_entity_decode($input, ENT_QUOTES, 'UTF-8');
    
    return $input;
}

function exportAll() {
    // Get filter parameters
    $trackingFilter = $_POST['trackingFilter'] ?? '';
    $vendorFilter = $_POST['vendorFilter'] ?? '';
    $applyingFilters = $_POST['applyingFilters'] ?? 'false';

    // Initialize parameters array
    $params = [];

    // Base query
    $sql = "SELECT 
        mt.tracking_number,
        mt.sales_order_no,
        mt.production_order_no,
        mt.purchase_order_no,
        mt.purchase_item,
        mt.vendor_name,
        mt.vendor_code,
        mt.po_created_by,
        mt.our_reference,
        mt.po_date,
        mt.material,
        mt.short_text,
        mt.po_quantity,
        mt.quantity_delivered,
        mt.still_to_be_delivered,
        mt.delivery_date,
        mt.delivery_date_vendor_confirmation,
        mt.account_assignment_category,
        mt.net_value_po_currency,
        mt.currency,
        mt.purch_grp,
        mt.deletion_ind,
        mt.storage_no,
        mt.storage_location_desc
        FROM sap_material_tracking mt
        WHERE 1=1";

    // Only apply filters if they're being submitted
    if ($applyingFilters === 'true') {
        if (!empty($trackingFilter)) {
            $sql .= " AND mt.tracking_number LIKE :trackingFilter";
            $params[':trackingFilter'] = "%$trackingFilter%";
        }
        
        if (!empty($vendorFilter)) {
            $sql .= " AND mt.vendor_name LIKE :vendorFilter";
            $params[':vendorFilter'] = "%$vendorFilter%";
        }
    }

    // Add ordering
    $sql .= " ORDER BY mt.po_date DESC";

    try {
        // Get filtered data
        $result = DbManager::fetchPDOQuery('rpa', $sql, $params);

        // Process the data
        if (isset($result['data']) && is_array($result['data'])) {
            $result['data'] = array_map(function($row) {
                // Format dates
                if (!empty($row['po_date'])) {
                    $row['po_date'] = date('Y-m-d', strtotime($row['po_date']));
                }
                if (!empty($row['delivery_date'])) {
                    $row['delivery_date'] = date('Y-m-d', strtotime($row['delivery_date']));
                }
                if (!empty($row['delivery_date_vendor_confirmation'])) {
                    $row['delivery_date_vendor_confirmation'] = date('Y-m-d', strtotime($row['delivery_date_vendor_confirmation']));
                }

                // Format numbers
                if (isset($row['po_quantity'])) {
                    $row['po_quantity'] = number_format((float)$row['po_quantity'], 2);
                }
                if (isset($row['quantity_delivered'])) {
                    $row['quantity_delivered'] = number_format((float)$row['quantity_delivered'], 2);
                }
                if (isset($row['still_to_be_delivered'])) {
                    $row['still_to_be_delivered'] = number_format((float)$row['still_to_be_delivered'], 2);
                }

                return $row;
            }, $result['data']);
        }

        // Log the export action
        SharedManager::saveLog("log_material_tracking", "Export data request - Total records: " . count($result['data'] ?? []));

        echo json_encode([
            'success' => true,
            'data' => $result['data'] ?? []
        ]);
    } catch (Exception $e) {
        // Log the error
        SharedManager::saveLog("log_material_tracking", "Export data error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching data: ' . $e->getMessage()
        ]);
    }
    exit;
}
?>