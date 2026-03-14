<?php
ini_set('memory_limit', '8192M');
ini_set('max_execution_time', 0);

require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronDbManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/snowflake/SnowflakeQuery.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/jobs/snowflake/spiridon/rpa_common.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";

function executeAndFetchResults($queryObject) {
    $queryId = null;
    $queryStatus = null;
    $maxRetries = 30; // 150 seconds total wait time (30 * 5s)
    
    flush(); // Ensure output is sent and received continuously
    try {
        $currentPartitionData = $queryObject->getCurrentPartitionData();

        // Check for async errors
        if (isset($currentPartitionData['code']) && $currentPartitionData['code'] === "333334") {
            $queryId = $currentPartitionData['statementHandle'];
            $retryCount = 0;
            
            do {
                sleep(10);
                $queryStatusUrl = $currentPartitionData['statementStatusUrl'];
                $queryStatusResponse = file_get_contents($queryStatusUrl);
                $queryStatusData = json_decode($queryStatusResponse, true);

                if ($queryStatusData['status'] === "SUCCEEDED") {
                    // Get successful result
                    $currentPartitionData = $queryObject->getCurrentPartitionData();
                    break;
                } elseif ($queryStatusData['status'] === "FAILED") {
                    handleQueryFailure($queryStatusData['message']);
                    return false;
                }

                $retryCount++;
            } while ($retryCount < $maxRetries && $queryStatusData['status'] === "RUNNING");

            if ($retryCount >= $maxRetries) {
                handleQueryFailure("Query timed out after " . ($maxRetries * 5) . " seconds");
                return false;
            }
        }
        
        // Store data in global variable instead of file
        if ($currentPartitionData) {
            $GLOBALS['fetchedData'] = $currentPartitionData;
            echo "DEBUG: Data fetched successfully\n";
        }
        unset($currentPartitionData);

    } catch (Exception $e) {
        handleQueryFailure($e->getMessage());
        return false;
    }
    
    return true;
}

// Define constants
$TABLE_NAME = "qa32_inspection_list";
$now = date("Ymd_His");
$LOG_FILE_NAME = "{$TABLE_NAME}_{$now}";

// Initialize global data array
$GLOBALS['fetchedData'] = [];

// Snowflake query for QA32 Inspection List
$qaInspectionQuery = "
    with 
        data as (
            select 
                ltrim(qals.prueflos, '0') as \"Inspection_Lot\",
                ltrim(qals.matnr, '0') as \"Material\",
                listagg(tj02t.txt04, ' ') as \"System_Status\",
                qals.bwart as \"Movement_Type\",
                qals.ktextmat as \"Short_Text_Inspection_Object\",
                ltrim(qals.lifnr, '0') as \"Supplier\",
                lfa1.name1 as \"Supplier_Name\",
                qals.mblnr as \"Material_Document\",
                qals.zeile as \"Mat_Doc_Item_No\",
                qals.lmengeist as \"Actual_Lot_Quantity\",
                to_char(try_to_date(qals.budat, 'YYYYMMDD'), 'DD/MM/YYYY') as \"Posting_Date\",
                to_char(try_to_date(qave.vdatum, 'YYYYMMDD'), 'DD/MM/YYYY') as \"UD_Code_Date\",
                qals.kzskiplot as \"Skip\",
                qals.lagortvorg as \"Storage_Location_Insp_Lot_Stock\",
                qals.lmenge01 as \"Unrestricted_Use_Stock\"
            from qals 
            join qave using (mandant, prueflos)
            join jest on jest.mandt = qals.mandant and jest.objnr = qals.objnr and jest.inact <> 'X'
            join tj02 on tj02.istat = jest.stat and tj02.nodis <> 'X'
            join tj02t on tj02t.istat = jest.stat and tj02t.spras = 'E'
            left join lfa1 on lfa1.mandt = qals.mandant and lfa1.lifnr = qals.lifnr
            where qals.werk = '9E62'
            group by all
        )

    select * from data where contains(\"System_Status\", 'UD') and try_to_date(\"UD_Code_Date\", 'DD/MM/YYYY') = DATEADD(day, -1, current_date());
";

try {
    // Initialize Snowflake query object
    $snowflakeQueryObject = new SnowflakeQuery(
        "ONEX", 
        "PRD_DISTRIBUTE", 
        "ERP_RAW_AP001", 
        $qaInspectionQuery, 
        [], 
        false, 
        ["batchSize" => 15]
    );

    // Execute and fetch results
    if (!executeAndFetchResults($snowflakeQueryObject)) {
        throw new Exception("Failed to execute Snowflake query");
    }

    // Process the fetched data directly
    $fetchedData = $GLOBALS['fetchedData'];
    // SharedManager::print($fetchedData);
    // exit("Swappy");
    if (empty($fetchedData)) {
        echo "No data fetched from Snowflake query\n";
        exit(0);
    }

    $totalRecordsProcessed = count($fetchedData);

    echo "=== QA32 Inspection List Data ===\n";
    echo "Total records fetched: $totalRecordsProcessed\n";
    echo "=================================\n\n";

    // Send email with Excel attachment
    sendQA32EmailWithExcel($fetchedData, $totalRecordsProcessed);

    echo "\nQA32 Inspection List process completed successfully!\n";
    echo "Total records processed: $totalRecordsProcessed\n";

} catch (Exception $e) {
    handleQueryFailure($e->getMessage());
    exit(1);
}

function handleQueryFailure($status) {
    error_log("QA32 Snowflake query failed: $status");
    echo "Error: $status\n";
}

function sendQA32EmailWithExcel($fetchedData, $totalRecordsProcessed) {
    $email = ['sandeep.wagh1@siemens.com'];
    $cc_email = ['pratik.parab@siemens.com','swapnil.takke@siemens.com'];
    
    $display_date = date('Y-m-d', strtotime('-1 day'));
    
    // Create Excel file
    $excelFilePath = createXlsxFileNative($fetchedData);
    
    // Create HTML content for email
    $htmlContent = "<p>Hello All,</p>";
    $htmlContent .= "<p>QA32 Inspection List Report for <strong>" . $display_date . "</strong></p>";
    $htmlContent .= "<p><strong>Total Records: " . $totalRecordsProcessed . "</strong></p>";
    $htmlContent .= "<p>Please find the attached Excel file with detailed inspection data.</p>";
    $htmlContent .= "<p><strong>Summary:</strong></p>";
    $htmlContent .= "<ul>";
    $htmlContent .= "<li>Total Inspection Lots: " . $totalRecordsProcessed . "</li>";
    $htmlContent .= "<li>Report Generated: " . date('Y-m-d H:i:s') . "</li>";
    $htmlContent .= "</ul>";
    $htmlContent .= "<br><br><br>";
    $htmlContent .= "With Best Regards,<br>";
    $htmlContent .= "SI EA O AIS THA";
    $htmlContent .= "</p>";
    
    // Send email with attachment
    $SELF_ORG_CODE = SharedManager::getFromSharedEnv('SELF_ORG_CODE');
    $mailSubject = 'QA32 Inspection List Report : ' . $display_date;
    
    MailManager::sendMail(
        $mailSubject, 
        $htmlContent, 
        'new_user', 
        [$excelFilePath],  // Attachments array
        $email,
        $cc_email
    );
    
    // Clean up temporary Excel file after sending
    if (file_exists($excelFilePath)) {
        unlink($excelFilePath);
    }
}

function createXlsxFileNative($fetchedData) {
    if (empty($fetchedData)) {
        throw new Exception("No data available to create Excel file");
    }
    
    // Create a temporary file path
    $tempDir = sys_get_temp_dir();
    $fileName = 'QA32_Inspection_List_' . date('Ymd_His') . '.xlsx';
    $filePath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
    
    // Get headers from the first row
    $headers = array_keys($fetchedData[0]);
    
    // Calculate column widths based on content
    $columnWidths = calculateColumnWidths($headers, $fetchedData);
    
    // Create ZIP archive (XLSX is a ZIP file)
    $zip = new ZipArchive();
    if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Could not create XLSX file");
    }
    
    // Create the workbook.xml
    $workbookXml = createWorkbookXml();
    $zip->addFromString('xl/workbook.xml', $workbookXml);
    
    // Create the worksheet with column widths
    $worksheetXml = createWorksheetXml($headers, $fetchedData, $columnWidths);
    $zip->addFromString('xl/worksheets/sheet1.xml', $worksheetXml);
    
    // Create the styles
    $stylesXml = createStylesXml();
    $zip->addFromString('xl/styles.xml', $stylesXml);
    
    // Create relationships
    $workbookRelsXml = createWorkbookRelsXml();
    $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsXml);
    
    $documentRelsXml = createDocumentRelsXml();
    $zip->addFromString('_rels/.rels', $documentRelsXml);
    
    // Create content types
    $contentTypesXml = createContentTypesXml();
    $zip->addFromString('[Content_Types].xml', $contentTypesXml);
    
    $zip->close();
    
    return $filePath;
}

function calculateColumnWidths($headers, $fetchedData) {
    $columnWidths = [];
    
    foreach ($headers as $index => $header) {
        // Start with header length
        $maxLength = strlen($header);
        
        // Check all data rows for this column
        foreach ($fetchedData as $row) {
            $value = isset($row[$header]) ? (string)$row[$header] : '';
            $length = strlen($value);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        
        // Add padding for better readability (multiply by 1.2 and add 2)
        $columnWidths[$index] = min(($maxLength * 1.2) + 2, 50); // Cap at 50 for very long content
    }
    
    return $columnWidths;
}

function createWorkbookXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <fileVersion appName="xl" lastEdited="4" lowestEdited="4" rupBuild="4505"/>
    <workbookPr defaultTheme="1"/>
    <bookViews>
        <workbookView xWindow="0" yWindow="0" windowWidth="19020" windowHeight="11010" tabRatio="500" activeTab="0"/>
    </bookViews>
    <sheets>
        <sheet name="QA32 Inspection List" sheetId="1" r:id="rId1"/>
    </sheets>
    <calcPr calcId="140080"/>
</workbook>';
}

function createWorksheetXml($headers, $fetchedData, $columnWidths) {
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheetPr filterOn="false">
        <pageSetUpPr fitToPage="false"/>
    </sheetPr>
    <sheetFormatPr baseColWidth="8.43" defaultRowHeight="15"/>
    
    <!-- Column Definitions with Dynamic Widths -->
    <cols>';
    
    foreach ($columnWidths as $index => $width) {
        $colNum = $index + 1;
        $xml .= '<col min="' . $colNum . '" max="' . $colNum . '" width="' . number_format($width, 2) . '" customWidth="1"/>';
    }
    
    $xml .= '</cols>
    
    <sheetData>';
    
    // Add header row
    $xml .= '<row r="1" spans="1:' . count($headers) . '" ht="30" customHeight="1">';
    $colNum = 1;
    foreach ($headers as $header) {
        $cellRef = columnLetter($colNum) . '1';
        $xml .= '<c r="' . $cellRef . '" s="1" t="inlineStr"><is><t>' . htmlspecialchars($header) . '</t></is></c>';
        $colNum++;
    }
    $xml .= '</row>';
    
    // Add data rows
    $rowNum = 2;
    foreach ($fetchedData as $row) {
        $xml .= '<row r="' . $rowNum . '" spans="1:' . count($headers) . '">';
        $colNum = 1;
        foreach ($headers as $header) {
            $cellRef = columnLetter($colNum) . $rowNum;
            $value = isset($row[$header]) ? $row[$header] : '';
            
            // Determine cell type
            if (is_numeric($value) && !is_string($value)) {
                $xml .= '<c r="' . $cellRef . '" s="2"><v>' . $value . '</v></c>';
            } else {
                $xml .= '<c r="' . $cellRef . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars((string)$value) . '</t></is></c>';
            }
            $colNum++;
        }
        $xml .= '</row>';
        $rowNum++;
    }
    
    $xml .= '</sheetData>
    <pageMargins left="0.7" top="0.75" right="0.7" bottom="0.75" header="0.3" footer="0.3"/>
</worksheet>';
    
    return $xml;
}

function createStylesXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font>
            <sz val="11"/>
            <color theme="1"/>
            <name val="Calibri"/>
            <family val="2"/>
            <scheme val="minor"/>
        </font>
        <font>
            <b/>
            <sz val="12"/>
            <color rgb="FFFFFFFF"/>
            <name val="Calibri"/>
            <family val="2"/>
            <scheme val="minor"/>
        </font>
    </fonts>
    <fills count="3">
        <fill>
            <patternFill patternType="none"/>
        </fill>
        <fill>
            <patternFill patternType="gray125"/>
        </fill>
        <fill>
            <patternFill patternType="solid">
                <fgColor rgb="FF003366"/>
            </patternFill>
        </fill>
    </fills>
    <borders count="2">
        <border>
            <left/>
            <right/>
            <top/>
            <bottom/>
            <diagonal/>
        </border>
        <border>
            <left style="thin">
                <color auto="1"/>
            </left>
            <right style="thin">
                <color auto="1"/>
            </right>
            <top style="thin">
                <color auto="1"/>
            </top>
            <bottom style="thin">
                <color auto="1"/>
            </bottom>
            <diagonal/>
        </border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="3">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
            <alignment horizontal="center" vertical="center" wrapText="1"/>
        </xf>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">
            <alignment horizontal="left" vertical="center" wrapText="0"/>
        </xf>
    </cellXfs>
    <cellStyles count="1">
        <cellStyle name="Normal" xfId="0" builtinId="0"/>
    </cellStyles>
    <dxfs count="0"/>
    <tableStyles count="0" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleMedium9"/>
</styleSheet>';
}

function createWorkbookRelsXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
}

function createDocumentRelsXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
}

function createContentTypesXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
}

function columnLetter($num) {
    $numeric = ($num - 1) % 26;
    $letter = chr(65 + $numeric);
    $num = intdiv(($num - 1), 26);
    if ($num > 0) {
        return columnLetter($num) . $letter;
    } else {
        return $letter;
    }
}

?>