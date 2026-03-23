<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/checklogin.php";
SharedManager::checkAuthToModule(35);
$mode = $_GET["mode"] ?? "";

try {
    $orderNo = $_GET['project'] ?? '';
    $dataType = $_GET['dataType'] ?? 'html';
    if ($orderNo) {
        $filePath = SharedManager::getProjectFilePath($orderNo);
        if (strtolower($dataType) === 'json')
            echo json_encode($filePath, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        else {
            if ($mode == "download") {
                // Generate the VBScript code
                $vbsCode = 'Set objShell = CreateObject("WScript.Shell")' . "\n";
                $vbsCode .= 'objShell.Run "explorer.exe ' . $filePath . '"';

                // Set appropriate headers for downloading
                header('Content-Disposition: attachment; filename="'. $orderNo . '".vbs');
                header('Content-Type: text/plain');

                // Output the VBScript code
                echo $vbsCode;
            }
            else {
                echo $filePath;
            }
        }
        exit();
    }
} catch (JsonException $e) {
    error_reporting(E_ERROR | E_PARSE);
    throw new Error("vfz/projectPathFinder Error {$e->getMessage()}");
}