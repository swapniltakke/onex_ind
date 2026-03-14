<?php
// CRON JOB FILE - Nachbau JSON File Processing and Notification System
// Reads files ONLY from local machine path
ini_set('memory_limit', '21264M');
ini_set('max_execution_time', 30000);
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronDbManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/snowflake/SnowflakeQuery.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/jobs/snowflake/spiridon/rpa_common.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/shared.php';

// Get input parameters
// $fileName = "1000032224.25357.JSON";
$fileName = $_GET['filename'] ?? '';
$backlog = $_GET['backlog'] ?? 0;
$isIncluded = $_GET['is_included'] ?? 0; // Flag to detect if file is included

// Define ONLY local source path for reading files
$localSourcePath = "C:\\Users\\z0051erm\\Downloads\\Nachbau Files\\Example";

// Complete file path
$localFilePath = $localSourcePath . "\\" . $fileName;

// Validate that the file exists in local source path
if (!file_exists($localFilePath)) {
    if ($isIncluded) {
        return ['status' => 'error', 'message' => "File not found at: $localFilePath"];
    }
    returnHttpResponse(500, "File not found at: $localFilePath");
}

// Read ENTIRE file contents from local path ONLY
$fileContents = file_get_contents($localFilePath);

if (empty($fileContents)) {
    if ($isIncluded) {
        return ['status' => 'error', 'message' => "File is empty or cannot be read: $localFilePath"];
    }
    returnHttpResponse(500, "File is empty or cannot be read: $localFilePath");
}

// ============================================
// Parse JSON file and extract data
// ============================================
$jsonData = json_decode($fileContents, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    if ($isIncluded) {
        return ['status' => 'error', 'message' => "Invalid JSON format: " . json_last_error_msg()];
    }
    returnHttpResponse(500, "Invalid JSON format: " . json_last_error_msg());
}

// ============================================
// Extract project name from JSON
// Pattern: Use projectName field from JSON structure
// ============================================
$projectNameNachbau = $jsonData['projectName'] ?? '';

// SharedManager::print("Project Name: " . $projectNameNachbau);

// ============================================
// Extract order number from JSON
// Pattern: Use orderNo field from JSON structure
// ============================================
$orderNumber = $jsonData['orderNo'] ?? '';

// Remove any non-numeric characters if present
$orderNumber = preg_replace('/\D/', '', $orderNumber);

// SharedManager::print("Order Number: " . $orderNumber);

// ============================================
// Extract Nachbau Number from JSON
// Pattern: Use nachbauNo field from JSON structure
// ============================================
$nachbauNumber = $jsonData['nachbauNo'] ?? '';

// Get file creation/modification date from local file
$fileStats = stat($localFilePath);
if (is_array($fileStats)) {
    $fileCreatedDate = date('Y-m-d H:i:s', $fileStats['mtime']);
} else {
    $fileCreatedDate = date('Y-m-d H:i:s');
}

// Validate order number format (must be 10 digits starting with 451)
$isValidOrderNumber = 0;
if (strlen($orderNumber) == 10 && str_starts_with($orderNumber, '451')) {
    $isValidOrderNumber = 1;
}

// Extract panel information and field names from JSON
$panelFieldNames = [];
if (isset($jsonData['panels']) && is_array($jsonData['panels'])) {
    foreach ($jsonData['panels'] as $panel) {
        if (isset($panel['feldName'])) {
            $panelFieldNames[] = $panel['feldName'];
        }
    }
}
$panelFieldNamesStr = implode(', ', $panelFieldNames);

// Database query to fetch project and stakeholder information
$query = "
    SELECT TOP 1
        dbo.OneX_ProjectDetails.ProjectID                                       AS 'ProjectId',
        dbo.OneX_ProjectDetails.PurchaseOrderNo                                 AS 'PurchaseOrderNo',
        dbo.OneX_ProjectDetails.FactoryNumber                                   AS 'FactoryNumber',
        concat(dbo.OneX_ProjectDetails.ProjectName,'(', dbo.OneX_ProjectDetails.Keyword, ')')         AS 'ProjectName',
        ee.ElectricalEngineer                                                   AS 'ElectricalEngineer',
        ee.EEGID                                                                AS 'EEGID',
        ee.EEEMail                                                              AS 'EEEMail',
        ee.OrderManager                                                         AS 'OrderManager',
        ee.OMGID                                                                AS 'OMGID',
        ee.OMEMail                                                              AS 'OMEMail',
        ee.OEX                                                                  AS 'OEX',
        ee.OEXGID                                                               AS 'OEXGID',
        ee.OEXEMail                                                             AS 'OEXEMail',
        ee.SBA                                                                  AS 'SBA',
        ee.SBAGID                                                               AS 'SBAGID',
        ee.MechanicalEngineer                                                   AS 'MechanicalEngineer',
        ee.MEGID                                                                AS 'MEGID',
        ee.MEEMail                                                              AS 'MEEMail'
    FROM dbo.OneX_ProjectDetails
    LEFT JOIN dbo.OneX_ProjectContacts AS ee ON dbo.OneX_ProjectDetails.ProjectId=ee.ProjectId
    WHERE dbo.OneX_ProjectDetails.PurchaseOrderNo = :p1
";

// Fetch project data from database
$projectDataResult = DbManager::fetchPDOQueryData('MTool_INKWA', $query, [':p1' => $orderNumber]);

if (empty($projectDataResult['data'])) {
    $projectData = [];
} else {
    $projectData = $projectDataResult['data'][0];
}

// SharedManager::print($projectData);
// exit;

// Extract stakeholder information with fallback values
$projectName = $projectData['ProjectName'] ?? $projectNameNachbau;
$factoryNumber = $projectData['FactoryNumber'] ?? '';
$purchaseOrderNumber = $projectData['PurchaseOrderNo'] ?? $orderNumber;
$electricalEngineerEmail = $projectData['EEEMail'] ?? '';
$orderManagerEmail = $projectData['OMEMail'] ?? '';
$orderedByEmail = $projectData['OEXEMail'] ?? '';
$mechanicalEngineerEmail = $projectData['MEEMail'] ?? '';

// Prepare email body for successful transfer notification
$emailBody = "Dear Sir/Madam,<br><br>
    SAP Works Transfer has been completed in NxTools+ and the Nachbau JSON file has been processed.<br><br>
    <b>Nachbau Number:</b> $nachbauNumber<br>
    <b>Project Name:</b> $projectName<br>
    <b>Factory Number:</b> $factoryNumber<br>
    <b>SAP Order Number:</b> $purchaseOrderNumber<br>
    <b>Nachbau File:</b> $fileName<br>
    <b>Panel Field Names:</b> $panelFieldNamesStr<br>
    <b>Source Location:</b> $localSourcePath<br>
    <b>Processed Date:</b> $fileCreatedDate<br><br>
    This is an automated email to notify the stakeholders of the project.<br><br>
    Best Regards,<br>
    SI EA O AIS THA DGT";

// Send notification email if not in backlog mode
if ($backlog != 1) {
    $emailSubject = '[New NxTools+ Transfer] SAP Works Transfer completed for ' . $projectName;
    
    // Build recipient list (filter out empty emails)
    $recipients = array_filter([
        $electricalEngineerEmail,
        $orderManagerEmail,
        $mechanicalEngineerEmail
    ]);
    
    if (!empty($recipients)) {
        MailManager::sendMail(
            $emailSubject, 
            $emailBody, 
            'nachbau_sap_works_transfer_done', 
            [], 
            array_values($recipients)
        );
    }
}

// Handle case where factory number is not found in database
if (!$factoryNumber && $isValidOrderNumber) {
    $errorEmailBody = "Hello,<br><br>
        The transfer for SAP Order Number: <b>$orderNumber</b> has been completed.<br>
        Project Name: <b>$projectNameNachbau</b><br>
        Nachbau Number: <b>$nachbauNumber</b><br>
        Nachbau File: <b>$fileName</b><br>
        Source Path: <b>$localSourcePath</b><br>
        <br>
        <b>The factory number record for this transfer was not found in MTool.</b><br><br>
        If you believe this is an error, please verify the JSON file name listed below and the corresponding order number.<br>
        <br>
        This email was sent <b>automatically</b>.<br>
        <br>
        Best Regards,<br>
        SI EA O AIS THA DGT";
    
    $errorEmailSubject = '[Action Required] Transfer completed, MTool record not found: ' . $projectNameNachbau . ' - ' . $fileName . ' - ' . $orderNumber;
    MailManager::sendMail($errorEmailSubject, $errorEmailBody, 'nachbau_mtool_not_found');
    
    if ($isIncluded) {
        return ['status' => 'error', 'message' => 'Factory number not found in MTool'];
    }
    exit;
} elseif (!$isValidOrderNumber) {
    $errorEmailBody = "Hello,<br><br>
        The transfer for SAP Order Number: <b>$orderNumber</b> has been completed.<br>
        Project Name: <b>$projectNameNachbau</b><br>
        Nachbau Number: <b>$nachbauNumber</b><br>
        Nachbau File: <b>$fileName</b><br>
        Source Path: <b>$localSourcePath</b><br>
        <br>
        <b>The order number format is invalid. Expected format: 10 digits starting with 451.</b><br><br>
        Please verify the JSON file and the order number format.<br>
        <br>
        This email was sent <b>automatically</b>.<br>
        <br>
        Best Regards,<br>
        SI EA O AIS THA DGT";
    
    $errorEmailSubject = '[Invalid Order Number] Transfer completed with invalid format: ' . $projectNameNachbau . ' - ' . $fileName . ' - ' . $orderNumber;
    MailManager::sendMail($errorEmailSubject, $errorEmailBody, 'nachbau_notvalid_order_number');
    
    if ($isIncluded) {
        return ['status' => 'error', 'message' => 'Invalid order number format'];
    }
    exit;
}

// Log the transfer operation to database
if ($backlog != 1) {
    $logQuery = 'INSERT INTO log_nachbau (FileName, SAPNumber, FactoryNumber, ProjectName, EE, OM, OEX, ME, Created, SourcePath) 
                 VALUES (:p1, :p2, :p3, :p4, :p5, :p6, :p7, :p8, :p9, :p10);';
    
    DbManager::fetchPDOQuery('logs', $logQuery, [
        ':p1' => $fileName,
        ':p2' => $purchaseOrderNumber,
        ':p3' => $factoryNumber,
        ':p4' => $projectName,
        ':p5' => $electricalEngineerEmail,
        ':p6' => $orderManagerEmail,
        ':p7' => $orderedByEmail,
        ':p8' => $mechanicalEngineerEmail,
        ':p9' => $fileCreatedDate,
        ':p10' => $localSourcePath
    ]);
}

// Return success response based on context
if ($isIncluded) {
    return [
        'status' => 'success',
        'message' => "JSON Nachbau file processed successfully. Order: $orderNumber, Project: $projectName",
        'fileName' => $fileName,
        'orderNumber' => $orderNumber,
        'projectName' => $projectName,
        'nachbauNumber' => $nachbauNumber
    ];
}

returnHttpResponse(200, "JSON Nachbau file processed successfully. Order: $orderNumber, Project: $projectName");
?>