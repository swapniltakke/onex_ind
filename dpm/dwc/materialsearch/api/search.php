<?php
// error_reporting(E_ERROR | E_PARSE);
$scannedPanelValue = $_GET['scannedPanelValue'] ?? '';

// Extract sales order number from the scanned value
function extractSalesOrderFromBarcode($barcodeValue) {
    if (empty($barcodeValue)) {
        return '';
    }

    $barcodeValue = trim($barcodeValue);

    // Check if it contains delimiters
    $hasDelimiter = (strpos($barcodeValue, '*') !== false) || 
                    (strpos($barcodeValue, '|') !== false) || 
                    (strpos($barcodeValue, ',') !== false);

    if ($hasDelimiter) {
        // Split by common delimiters
        $parts = [];
        
        if (strpos($barcodeValue, '*') !== false) {
            $parts = explode('*', $barcodeValue);
        } elseif (strpos($barcodeValue, '|') !== false) {
            $parts = explode('|', $barcodeValue);
        } elseif (strpos($barcodeValue, ',') !== false) {
            $parts = explode(',', $barcodeValue);
        }

        // The first part should be the sales order number
        if (!empty($parts)) {
            $firstPart = trim($parts[0]);
            
            // Validate that it's numeric and has reasonable length
            if (is_numeric($firstPart) && strlen($firstPart) >= 8) {
                return $firstPart;
            }
        }
    } else {
        // No delimiter found, check if it's a valid sales order number
        if (is_numeric($barcodeValue) && strlen($barcodeValue) >= 8) {
            return $barcodeValue;
        }
    }

    return '';
}

// Extract panel information from barcode
// Format: salesOrder*panelNumber*description*name*company*code1*code2
// Returns: panelNumber|code2|code1
function extractPanelFromBarcode($barcodeValue) {
    if (empty($barcodeValue)) {
        return '';
    }

    $barcodeValue = trim($barcodeValue);

    // Check if it contains delimiters
    $hasDelimiter = (strpos($barcodeValue, '*') !== false) || 
                    (strpos($barcodeValue, '|') !== false) || 
                    (strpos($barcodeValue, ',') !== false);

    if ($hasDelimiter) {
        $parts = [];
        
        if (strpos($barcodeValue, '*') !== false) {
            $parts = explode('*', $barcodeValue);
        } elseif (strpos($barcodeValue, '|') !== false) {
            $parts = explode('|', $barcodeValue);
        } elseif (strpos($barcodeValue, ',') !== false) {
            $parts = explode(',', $barcodeValue);
        }

        // Extract panel information from the barcode parts
        // Format: [0]=salesOrder, [1]=panelNumber, [2]=description, [3]=name, [4]=company, [5]=code1, [6]=code2
        if (count($parts) > 1) {
            $panelNumber = trim($parts[1]);
            
            // Pad with leading zeros to make it 6 digits
            if (!empty($panelNumber)) {
                $panelNumber = str_pad($panelNumber, 6, '0', STR_PAD_LEFT);
                
                // Extract code1 (index 5) and code2 (index 6)
                $code1 = '';
                $code2 = '';
                
                if (count($parts) > 5) {
                    $code1 = trim($parts[5]);
                }
                if (count($parts) > 6) {
                    $code2 = trim($parts[6]);
                }
                
                // Construct the panel value in format: panelNumber|code2|code1
                $panelValue = $panelNumber . '|' . $code2 . '|' . $code1;
                return $panelValue;
            }
        }
    }

    return '';
}

// Pad with leading zeros to make it 6 digits
if (!empty($scannedPanelValue) || (isset($_SESSION['is_scan']) && $_SESSION['is_scan'] == 1)) {
    $scannedPanelValue = ltrim($scannedPanelValue, '0');
    $scannedPanelValue = str_pad($scannedPanelValue, 6, '0', STR_PAD_LEFT);
} else {
    unset($_SESSION['selected_panel']);
}

$project = trim($_GET["project"] ?? '');

// Extract sales order number from project parameter if it contains delimiters
$extractedProject = extractSalesOrderFromBarcode($project);
if (!empty($extractedProject)) {
    $project = $extractedProject;
}

if (strlen($project) < 3) {
    returnHttpResponse(400, "Incorrect project number");
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
$result = MToolManager::searchProject($project);

$found = false;
foreach ($result as $row) {
    $factoryNumber = $row['FactoryNumber'];
    $project_name = $row['ProjectName'];
    $product = $row['Product'];
    $panel_qty = (int) $row['Qty'];
    $found = $project;
    if ($_REQUEST['file']) {

    }
    // Construct the URL with both project and scannedPanelValue if present
    $redirectUrl = "/dpm/dwc/".$_REQUEST['file'].".php?project=$factoryNumber";
    if (!empty($scannedPanelValue)) {
        // $redirectUrl .= "&panel=" . urlencode($scannedPanelValue);
    }
    
    $arr['items'][] = array(
        "project_no" => $factoryNumber,
        "name" => "$factoryNumber - $project_name - $product [Qty: $panel_qty]",
        "html_url" => $redirectUrl,
    );
}

$isFound = ($found) ? "Found" : "No Found";
SharedManager::saveLog("log_material_search", "Search Keyword: $project ($isFound)");

$object = (object)$arr;
echo json_encode($object);
exit;