<?php
/*
 * Task Name: Process Nachbau JSON Files
 * Interval: As needed
 * Description: Get last 30 JSON file names from file server, check if they exist in log_nachbau,
 *              and call nachbau.php API for files that don't exist in the log table
 */
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronDbManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/snowflake/SnowflakeQuery.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/jobs/snowflake/spiridon/rpa_common.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/shared.php';

$processedFiles = [];
$skippedFiles = [];
$errorFiles = [];
$emailErrors = [];

try {
    // Step 1: Get last 30 JSON file names from file server
    $jsonFiles = getLastJsonFilesFromServer(30);

    if (empty($jsonFiles)) {
        $errorMsg = "No JSON files found on the server";
        SharedManager::print($errorMsg);
        returnHttpResponse(500, $errorMsg);
    }

    SharedManager::print("Found " . count($jsonFiles) . " JSON files on server");

    // Step 2: Check which files already exist in log_nachbau table
    $filesToProcess = [];

    foreach ($jsonFiles as $jsonFile) {
        $query = "SELECT FileName FROM log_nachbau WHERE FileName = :filename";
        $result = CronDbManager::fetchQueryData('logs', $query, [':filename' => $jsonFile])['data'];

        if (empty($result)) {
            // File doesn't exist in log_nachbau, add to processing array
            $filesToProcess[] = $jsonFile;
        } else {
            // File already exists in database, skip it
            $skippedFiles[] = $jsonFile;
        }
    }
    
    SharedManager::print("Files to process: " . count($filesToProcess));
    SharedManager::print("Files to skip: " . count($skippedFiles));
    
    // Step 3: Call nachbau.php API for each file that doesn't exist in log_nachbau
    if (!empty($filesToProcess)) {
        foreach ($filesToProcess as $nachbauFile) {
            try {
                SharedManager::print("");
                SharedManager::print("========================================");
                SharedManager::print("Processing file: " . $nachbauFile);
                SharedManager::print("========================================");
                
                $result = callNachbauJsonApi($nachbauFile);
                
                if ($result['status'] === 'success') {
                    $processedFiles[] = $nachbauFile;
                    SharedManager::print("✓ Successfully processed: " . $nachbauFile);
                    SharedManager::print("  Order: " . $result['orderNumber']);
                    SharedManager::print("  Project: " . $result['projectName']);
                } else {
                    $errorFiles[] = [
                        'file' => $nachbauFile,
                        'error' => $result['message']
                    ];
                    SharedManager::print("✗ Processing failed: " . $result['message']);
                }
                
                SharedManager::print("========================================");

                usleep(1000000); // 1 second delay between API calls to prevent conflicts
                
            } catch (Throwable $e) {
                $errorMsg = $e->getMessage();
                $errorFiles[] = [
                    'file' => $nachbauFile,
                    'error' => $errorMsg
                ];
                SharedManager::print("✗ ERROR processing file " . $nachbauFile . ": " . $errorMsg);
                SharedManager::print("========================================");
            }
        }
    } else {
        SharedManager::print("No files to process. All files are already in database.");
    }

    // Step 4: Send success email with processing report
    SharedManager::print("");
    SharedManager::print("Sending email report...");
    try {
        sendSuccessEmailJson($processedFiles, $skippedFiles, $errorFiles);
        SharedManager::print("✓ Email report sent successfully");
    } catch (Throwable $e) {
        $emailErrors[] = "Failed to send success email: " . $e->getMessage();
        SharedManager::print("✗ Email Error: " . $e->getMessage());
    }

    // Final summary
    SharedManager::print("");
    SharedManager::print("========== FINAL SUMMARY ==========");
    SharedManager::print("Total Files Found: " . count($jsonFiles));
    SharedManager::print("Files Processed: " . count($processedFiles));
    SharedManager::print("Files Skipped: " . count($skippedFiles));
    SharedManager::print("Files with Errors: " . count($errorFiles));
    SharedManager::print("===================================");

} catch (Throwable $e) {
    // Send error email if something goes wrong during execution
    $errorMsg = "Critical Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine();
    SharedManager::print("✗ CRITICAL ERROR: " . $errorMsg);
    
    try {
        sendErrorEmailJson($errorMsg);
    } catch (Throwable $emailException) {
        SharedManager::print("✗ Failed to send error email: " . $emailException->getMessage());
    }
}

exit();

/**
 * Retrieve last N JSON files from the file server
 * 
 * @param int $limit Number of files to retrieve (default: 30)
 * @return array Array of JSON file names sorted by modification time (newest first)
 */
function getLastJsonFilesFromServer($limit = 30)
{
    $jsonFiles = [];

    try {
        $nachbauDataFolder = "C:\\Users\\z0051erm\\Downloads\\Nachbau Files\\Example";

        if (!is_dir($nachbauDataFolder)) {
            $errorMsg = "Directory not found or not accessible: $nachbauDataFolder";
            SharedManager::print("✗ " . $errorMsg);
            returnHttpResponse(500, $errorMsg);
        }

        $directoryIterator = new DirectoryIterator($nachbauDataFolder);
        $files = [];

        // Iterate through all files in the directory
        foreach ($directoryIterator as $fileInfo) {
            if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'json') {
                // Store file with modification time as key for sorting
                $compositeKey = $fileInfo->getMTime() . '_' . $fileInfo->getFilename();
                $files[$compositeKey] = $fileInfo->getFilename();
            }
        }

        // Sort files by modification time in descending order (newest first)
        krsort($files);

        // Get only the specified limit of files
        $jsonFiles = array_slice(array_values($files), 0, $limit);

        SharedManager::print("Directory scanned: " . $nachbauDataFolder);
        SharedManager::print("Total JSON files found: " . count($jsonFiles));

    } catch (Throwable $e) {
        $errorMsg = "Error reading files from server: " . $e->getMessage();
        SharedManager::print("✗ " . $errorMsg);
        returnHttpResponse(500, $errorMsg);
    }

    return $jsonFiles;
}

/**
 * Call the Nachbau JSON API endpoint to process a file
 * Supports both local file path and HTTP URL methods
 * 
 * @param string $fileName Name of the Nachbau JSON file to process
 * @return array Result array with status and message
 * @throws Exception If request fails
 */
function callNachbauJsonApi($fileName)
{
    // Method 1: Direct file inclusion (recommended for local files)
    try {
        SharedManager::print("Attempting Method 1: Direct File Inclusion");
        $result = callNachbauJsonViaInclusion($fileName);
        SharedManager::print("✓ Method 1 successful");
        return $result;
    } catch (Throwable $e) {
        SharedManager::print("✗ Method 1 failed: " . $e->getMessage());
        
        // If inclusion fails, try HTTP method
        try {
            SharedManager::print("Attempting Method 2: HTTP cURL Request");
            $result = callNachbauJsonViaCurl($fileName);
            SharedManager::print("✓ Method 2 successful");
            return $result;
        } catch (Throwable $curlException) {
            $combinedError = "Both methods failed - Inclusion: " . $e->getMessage() . " | cURL: " . $curlException->getMessage();
            SharedManager::print("✗ Method 2 failed: " . $combinedError);
            throw new Exception($combinedError);
        }
    }
}

/**
 * Call Nachbau JSON API via direct file inclusion
 * 
 * @param string $fileName Name of the Nachbau JSON file to process
 * @return array Result array with status and message
 * @throws Exception If file doesn't exist or inclusion fails
 */
function callNachbauJsonViaInclusion($fileName)
{
    $nachbauFilePath = $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/jobs/snowflake/internal/nachbau.php";
    
    if (!file_exists($nachbauFilePath)) {
        throw new Exception("Nachbau file not found at: $nachbauFilePath");
    }
    
    // Create a clean environment for the included file
    $GLOBALS['_GET_BACKUP'] = $_GET;
    $_GET = [];
    
    // Set GET parameters for the included file
    $_GET['filename'] = $fileName;
    $_GET['backlog'] = 0;
    $_GET['is_included'] = 1; // Flag to indicate file is included
    
    SharedManager::print("Including file: " . $nachbauFilePath . " with filename: " . $fileName);
    
    // Include and execute the file
    ob_start();
    try {
        $result = include $nachbauFilePath;
        $output = ob_get_clean();
        
        // Restore GET parameters
        $_GET = $GLOBALS['_GET_BACKUP'];
        unset($GLOBALS['_GET_BACKUP']);
        
        // Check if result is an array (returned from included file)
        if (is_array($result)) {
            SharedManager::print("Output from nachbau.php: " . json_encode($result));
            return $result;
        }
        
        if (empty($output)) {
            throw new Exception("Nachbau file returned empty output for: $fileName");
        }
        
        // Try to parse JSON response
        $jsonResponse = json_decode($output, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($jsonResponse['code'])) {
            if ($jsonResponse['code'] == 200) {
                return [
                    'status' => 'success',
                    'message' => $jsonResponse['message'] ?? 'File processed successfully',
                    'fileName' => $fileName,
                    'orderNumber' => '',
                    'projectName' => ''
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => $jsonResponse['message'] ?? 'Unknown error'
                ];
            }
        }
        
        SharedManager::print("Output from nachbau.php: " . substr($output, 0, 200));
        
        return [
            'status' => 'success',
            'message' => 'File processed successfully',
            'fileName' => $fileName,
            'orderNumber' => '',
            'projectName' => ''
        ];
        
    } catch (Throwable $e) {
        ob_end_clean();
        // Restore GET parameters
        $_GET = $GLOBALS['_GET_BACKUP'];
        unset($GLOBALS['_GET_BACKUP']);
        throw $e;
    }
}

/**
 * Call Nachbau JSON API via HTTP cURL request
 * 
 * @param string $fileName Name of the Nachbau JSON file to process
 * @return array Result array with status and message
 * @throws Exception If cURL request fails
 */
function callNachbauJsonViaCurl($fileName)
{
    // Get the base URL from server
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . "://" . $host;
    
    $apiUrl = $baseUrl . "/cronjobs/jobs/snowflake/internal/nachbau.php?filename=" . urlencode($fileName) . "&backlog=0&is_included=0";
    
    SharedManager::print("cURL URL: " . $apiUrl);
    
    $curlHandle = curl_init($apiUrl);
    
    // Set cURL options
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curlHandle, CURLOPT_TIMEOUT, 300);
    curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curlHandle, CURLOPT_MAXREDIRS, 5);
    
    $response = curl_exec($curlHandle);
    $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
    $curlError = curl_errno($curlHandle);
    $curlErrorMsg = curl_error($curlHandle);
    
    if ($curlError) {
        curl_close($curlHandle);
        throw new Exception("cURL error ($curlError): $curlErrorMsg");
    }
    
    curl_close($curlHandle);
    
    SharedManager::print("cURL HTTP Code: " . $httpCode);
    SharedManager::print("cURL Response: " . substr($response, 0, 200));
    
    // Check HTTP response code
    if ($httpCode !== 200) {
        throw new Exception("HTTP error code: $httpCode - Response: " . substr($response, 0, 300));
    }
    
    if (empty($response)) {
        throw new Exception("Empty response from API for file: $fileName");
    }
    
    // Try to parse JSON response
    $jsonResponse = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($jsonResponse['code'])) {
        if ($jsonResponse['code'] == 200) {
            return [
                'status' => 'success',
                'message' => $jsonResponse['message'] ?? 'File processed successfully',
                'fileName' => $fileName,
                'orderNumber' => '',
                'projectName' => ''
            ];
        } else {
            return [
                'status' => 'error',
                'message' => $jsonResponse['message'] ?? 'Unknown error'
            ];
        }
    }
    
    return [
        'status' => 'success',
        'message' => 'File processed successfully',
        'fileName' => $fileName,
        'orderNumber' => '',
        'projectName' => ''
    ];
}

/**
 * Send success email with processing report for JSON files
 * 
 * @param array $processedFiles Array of successfully processed files
 * @param array $skippedFiles Array of files that were skipped (already in database)
 * @param array $errorFiles Array of files that encountered errors
 * @return void
 */
function sendSuccessEmailJson($processedFiles, $skippedFiles, $errorFiles)
{
    $emailBody = "<h2>Nachbau JSON Files Processing Report</h2>";
    $emailBody .= "<p><strong>Job Status:</strong> <span style='color: green;'>✓ Completed Successfully</span></p>";
    $emailBody .= "<p><strong>Execution Date & Time:</strong> " . date("d.m.Y H:i:s") . "</p>";
    $emailBody .= "<hr/>";
    
    // Summary statistics
    $emailBody .= "<h3>Processing Summary</h3>";
    $emailBody .= "<ul>";
    $emailBody .= "<li><strong>Total Files Found:</strong> " . (count($processedFiles) + count($skippedFiles) + count($errorFiles)) . "</li>";
    $emailBody .= "<li><strong style='color: green;'>✓ Successfully Processed:</strong> " . count($processedFiles) . "</li>";
    $emailBody .= "<li><strong style='color: orange;'>⊘ Skipped (Already in DB):</strong> " . count($skippedFiles) . "</li>";
    $emailBody .= "<li><strong style='color: red;'>✗ Failed with Errors:</strong> " . count($errorFiles) . "</li>";
    $emailBody .= "</ul>";
    $emailBody .= "<hr/>";

    // Successfully processed files section
    $emailBody .= "<h3 style='color: green;'>✓ Successfully Processed JSON Files (" . count($processedFiles) . ")</h3>";
    if (!empty($processedFiles)) {
        $emailBody .= "<ul>";
        foreach ($processedFiles as $file) {
            $emailBody .= "<li>" . htmlspecialchars($file) . "</li>";
        }
        $emailBody .= "</ul>";
    } else {
        $emailBody .= "<p><em>No new JSON files were processed.</em></p>";
    }

    // Skipped files section (already in database)
    if (!empty($skippedFiles)) {
        $emailBody .= "<h3 style='color: orange;'>⊘ Skipped JSON Files (Already in Database) (" . count($skippedFiles) . ")</h3>";
        $emailBody .= "<ul>";
        foreach ($skippedFiles as $file) {
            $emailBody .= "<li>" . htmlspecialchars($file) . "</li>";
        }
        $emailBody .= "</ul>";
    }

    // Files with errors section
    if (!empty($errorFiles)) {
        $emailBody .= "<h3 style='color: red;'>✗ JSON Files with Errors (" . count($errorFiles) . ")</h3>";
        $emailBody .= "<ul>";
        foreach ($errorFiles as $errorFile) {
            $emailBody .= "<li>";
            $emailBody .= "<strong>" . htmlspecialchars($errorFile['file']) . "</strong>";
            $emailBody .= "<br/><span style='color: red;'>Error: " . htmlspecialchars($errorFile['error']) . "</span>";
            $emailBody .= "</li>";
        }
        $emailBody .= "</ul>";
    }

    $emailBody .= "<hr/>";
    $emailBody .= "<p style='font-size: 12px; color: #666;'><em>This is an automated email from Nachbau JSON Files Processing Job</em></p>";
    $emailBody .= "<p style='font-size: 12px; color: #666;'><em>Source Path: C:\\Users\\z0051erm\\Downloads\\Nachbau Files\\Example</em></p>";

    try {
        $body = getOneXMailContent($emailBody, "Nachbau JSON Files Processing Job Success");
        CronMailManager::sendMail(
            "✓ Nachbau JSON Files Processing Job Successful - [" . date("d.m.Y H:i:s") . "]",
            $body,
            "dto_conf_nachbau_json_processing_job_success"
        );
        SharedManager::print("✓ Success email sent");
    } catch (Throwable $e) {
        SharedManager::print("⚠ Warning: Email sending failed but processing completed: " . $e->getMessage());
    }
}

/**
 * Send error email when the JSON processing job fails
 * 
 * @param string $errorMessage Error message to include in the email
 * @return void
 */
function sendErrorEmailJson($errorMessage)
{
    $emailBody = "<h2>Nachbau JSON Files Processing Job Failed</h2>";
    $emailBody .= "<p><strong>Job Status:</strong> <span style='color: red;'>✗ Failed</span></p>";
    $emailBody .= "<p><strong>Execution Date & Time:</strong> " . date("d.m.Y H:i:s") . "</p>";
    $emailBody .= "<hr/>";
    $emailBody .= "<h3 style='color: red;'>Error Details</h3>";
    $emailBody .= "<p><strong style='color: red;'>Error Message:</strong></p>";
    $emailBody .= "<pre style='background-color: #f5f5f5; padding: 10px; border-left: 4px solid red;'>" . htmlspecialchars($errorMessage) . "</pre>";
    $emailBody .= "<hr/>";
    $emailBody .= "<p style='font-size: 12px; color: #666;'><em>This is an automated email from Nachbau JSON Files Processing Job</em></p>";
    $emailBody .= "<p style='font-size: 12px; color: #666;'><em>Source Path: C:\\Users\\z0051erm\\Downloads\\Nachbau Files\\Example</em></p>";
    $emailBody .= "<p style='font-size: 12px; color: red;'><em>Please investigate and take appropriate action.</em></p>";

    try {
        $body = getOneXMailContent($emailBody, "Nachbau JSON Files Processing Job Error");
        CronMailManager::sendMail(
            "✗ Nachbau JSON Files Processing Job Failed - [" . date("d.m.Y H:i:s") . "]",
            $body,
            "dto_conf_nachbau_json_processing_job_error"
        );
        SharedManager::print("✓ Error email sent");
    } catch (Throwable $e) {
        SharedManager::print("⚠ Warning: Error email sending failed: " . $e->getMessage());
    }
}
?>