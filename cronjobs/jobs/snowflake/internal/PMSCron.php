<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '8192M');
ini_set('max_execution_time', 0);

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";

// Configuration
$excelFilePath = $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/jobs/snowflake/internal/PMS Supervisor data new.xlsx";
$hardcodedUserId = "Z0051ERM";
$hardcodedUsername = "SWAPNIL TAKKE";
$currentDateTime = date('Y-m-d H:i:s');

// Validate file exists
if (!file_exists($excelFilePath)) {
    die("Error: Excel file not found at " . $excelFilePath);
}

// Function to read XLSX file without external libraries
function readExcelFile($filePath, $sheetName = 'Sheet3') {
    $zip = new ZipArchive();
    
    if ($zip->open($filePath) !== true) {
        throw new Exception("Cannot open Excel file");
    }

    // Read workbook.xml to find sheet relationship
    $workbookXml = $zip->getFromName('xl/workbook.xml');
    if ($workbookXml === false) {
        throw new Exception("Cannot read workbook.xml");
    }

    // Parse workbook to find sheet ID
    $dom = new DOMDocument();
    $dom->loadXML($workbookXml);
    $sheets = $dom->getElementsByTagName('sheet');
    
    $sheetId = null;
    foreach ($sheets as $sheet) {
        if ($sheet->getAttribute('name') === $sheetName) {
            $sheetId = $sheet->getAttribute('r:id');
            break;
        }
    }

    if ($sheetId === null) {
        throw new Exception("Sheet '$sheetName' not found in workbook");
    }

    // Read relationships to find sheet file
    $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
    $relsDom = new DOMDocument();
    $relsDom->loadXML($relsXml);
    
    $relationships = $relsDom->getElementsByTagName('Relationship');
    $sheetFile = null;
    foreach ($relationships as $rel) {
        if ($rel->getAttribute('Id') === $sheetId) {
            $sheetFile = $rel->getAttribute('Target');
            break;
        }
    }

    if ($sheetFile === null) {
        throw new Exception("Cannot find sheet file for '$sheetName'");
    }

    // Read the sheet XML
    $xml = $zip->getFromName('xl/' . $sheetFile);
    if ($xml === false) {
        throw new Exception("Cannot read worksheet data from " . $sheetFile);
    }

    $zip->close();

    // Parse XML and extract data
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    
    // Get shared strings if they exist
    $sharedStrings = [];
    $zip2 = new ZipArchive();
    if ($zip2->open($filePath) === true) {
        $stringsXml = $zip2->getFromName('xl/sharedStrings.xml');
        if ($stringsXml !== false) {
            $stringsDom = new DOMDocument();
            $stringsDom->loadXML($stringsXml);
            $stringItems = $stringsDom->getElementsByTagName('si');
            foreach ($stringItems as $index => $si) {
                $t = $si->getElementsByTagName('t')->item(0);
                if ($t !== null) {
                    $sharedStrings[$index] = $t->nodeValue;
                }
            }
        }
        $zip2->close();
    }

    // Extract rows
    $rows = $dom->getElementsByTagName('row');
    $data = [];
    
    foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('c');
        $rowData = [];
        
        foreach ($cells as $cell) {
            $cellValue = null;
            $cellType = $cell->getAttribute('t');
            $value = $cell->getElementsByTagName('v')->item(0);
            
            if ($value !== null) {
                $cellValue = $value->nodeValue;
                
                // If it's a shared string reference, get the actual value
                if ($cellType === 's' && isset($sharedStrings[(int)$cellValue])) {
                    $cellValue = $sharedStrings[(int)$cellValue];
                }
            }
            
            $rowData[] = $cellValue;
        }
        
        if (!empty(array_filter($rowData))) {
            $data[] = $rowData;
        }
    }

    return $data;
}

try {
    echo "Reading Excel file from Sheet3...\n";
    $excelData = readExcelFile($excelFilePath, 'Sheet3');

    if (empty($excelData)) {
        die("Error: Excel file is empty or no data found");
    }

    // Extract headers from first row
    $headers = array_shift($excelData);
    
    echo "Headers found: " . implode(", ", $headers) . "\n\n";

    // Map Excel headers to database columns
    $columnMapping = [
        'GID' => 'gid',
        'Name' => 'name',
        'Department' => 'department',
        'Sub Department' => 'sub_department',
        'Role' => 'role',
        'Group Type' => 'group_type',
        'In Company Manager' => 'in_company_manager',
        'Line Manager' => 'line_manager',
        'Supervisor' => 'supervisor',
        'Sponsor' => 'sponsor',
        'Type of Employment' => 'employment_type',
        'Joined 01.01.2005' => 'joined'
    ];

    // Get column indices
    $columnIndices = [];
    foreach ($columnMapping as $excelHeader => $dbColumn) {
        $index = array_search($excelHeader, $headers, true);
        if ($index !== false) {
            $columnIndices[$dbColumn] = $index;
            echo "✓ Found column: $excelHeader at index $index\n";
        } else {
            echo "⚠ Warning: Column '$excelHeader' not found\n";
        }
    }

    if (empty($columnIndices)) {
        die("Error: No matching headers found between Excel and mapping");
    }

    echo "\n";

    // Prepare insert query
    $insertQuery = "INSERT INTO employee_registration (
        gid, name, department, sub_department, role, group_type, 
        in_company_manager, line_manager, supervisor, sponsor, 
        employment_type, joined, user_id, username, updated_at, created_at, status
    ) VALUES (
        :gid, :name, :department, :sub_department, :role, :group_type,
        :in_company_manager, :line_manager, :supervisor, :sponsor,
        :employment_type, :joined, :user_id, :username, :updated_at, :created_at, :status
    )";

    $insertedCount = 0;
    $failedCount = 0;
    $errors = [];

    echo "Processing " . count($excelData) . " rows...\n";
    echo str_repeat("=", 70) . "\n";

    // Process each data row
    foreach ($excelData as $rowIndex => $row) {
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }

        try {
            // Extract data from row using column indices
            $data = [
                ':gid' => isset($row[$columnIndices['gid']]) ? trim((string)$row[$columnIndices['gid']]) : null,
                ':name' => isset($row[$columnIndices['name']]) ? trim((string)$row[$columnIndices['name']]) : null,
                ':department' => isset($row[$columnIndices['department']]) ? trim((string)$row[$columnIndices['department']]) : null,
                ':sub_department' => isset($row[$columnIndices['sub_department']]) ? trim((string)$row[$columnIndices['sub_department']]) : null,
                ':role' => isset($row[$columnIndices['role']]) ? trim((string)$row[$columnIndices['role']]) : null,
                ':group_type' => isset($row[$columnIndices['group_type']]) && !empty($row[$columnIndices['group_type']]) ? trim((string)$row[$columnIndices['group_type']]) : null,
                ':in_company_manager' => isset($row[$columnIndices['in_company_manager']]) ? trim((string)$row[$columnIndices['in_company_manager']]) : null,
                ':line_manager' => isset($row[$columnIndices['line_manager']]) ? trim((string)$row[$columnIndices['line_manager']]) : null,
                ':supervisor' => isset($row[$columnIndices['supervisor']]) ? trim((string)$row[$columnIndices['supervisor']]) : null,
                ':sponsor' => isset($row[$columnIndices['sponsor']]) && !empty($row[$columnIndices['sponsor']]) ? trim((string)$row[$columnIndices['sponsor']]) : null,
                ':employment_type' => isset($row[$columnIndices['employment_type']]) ? trim((string)$row[$columnIndices['employment_type']]) : null,
                ':joined' => isset($row[$columnIndices['joined']]) ? trim((string)$row[$columnIndices['joined']]) : null,
                ':user_id' => $hardcodedUserId,
                ':username' => $hardcodedUsername,
                ':updated_at' => $currentDateTime,
                ':created_at' => $currentDateTime,
                ':status' => 'A'
            ];

            // Validate required fields (GID and Name)
            if (empty($data[':gid']) || empty($data[':name'])) {
                $errors[] = "Row " . ($rowIndex + 2) . ": Missing GID or Name - GID: '" . $data[':gid'] . "' | Name: '" . $data[':name'] . "'";
                $failedCount++;
                continue;
            }

            // Insert into database
            $result = DbManager::fetchPDOQuery('spectra_db', $insertQuery, $data);

            if ($result) {
                $insertedCount++;
                echo "✓ Row " . ($rowIndex + 2) . ": GID=" . $data[':gid'] . " | Name=" . $data[':name'] . "\n";
            } else {
                $failedCount++;
                $errors[] = "Row " . ($rowIndex + 2) . ": Database insert failed - GID: " . $data[':gid'];
                echo "✗ Row " . ($rowIndex + 2) . ": Insert failed\n";
            }

        } catch (Exception $e) {
            $failedCount++;
            $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
            echo "✗ Row " . ($rowIndex + 2) . ": Error - " . $e->getMessage() . "\n";
        }
    }

    // Summary report
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "IMPORT SUMMARY\n";
    echo str_repeat("=", 70) . "\n";
    echo "Total Records Processed: " . ($insertedCount + $failedCount) . "\n";
    echo "Successfully Inserted: " . $insertedCount . "\n";
    echo "Failed: " . $failedCount . "\n";

    if (!empty($errors)) {
        echo "\nERRORS ENCOUNTERED:\n";
        echo str_repeat("-", 70) . "\n";
        foreach ($errors as $error) {
            echo "  ✗ " . $error . "\n";
        }
    }

    echo str_repeat("=", 70) . "\n";
    echo "Import process completed!\n";

} catch (Exception $e) {
    die("Fatal Error: " . $e->getMessage());
}
?>