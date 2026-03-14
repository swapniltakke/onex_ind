<?php

require_once __DIR__ . '/../shared/shared.php';
require_once __DIR__ . '/../shared/SharedManager.php';
require_once __DIR__ . '/./get_lv_informations.php';
require_once __DIR__ . '/./create_folder.php';

$projectNo = $_GET['projectNo'];
$nachbauFile = $_GET['nachbauFile'];
$omEmail = $_GET['omEmail'];
$lvFolder = '';

if (str_contains($_SERVER['REQUEST_URI'], 'searchProject.php')) {
    // If searchProject.php is directly called
    if (!is_numeric($projectNo) || strlen($projectNo) != 10)
        returnHttpResponse(500, 'Project Number is invalid');
    if (!$nachbauFile)
        returnHttpResponse(500, 'ProjectNo and NachbauFile are not defined');

    search_project($projectNo, $nachbauFile, $omEmail);
}

/**
 * Search and process project
 * 
 * @param $projectNo
 * @param $nachbauFile
 * @param string $omEmail
 * @return void
 */
function search_project($projectNo, $nachbauFile, $omEmail = '')
{
    $projectPath = SharedManager::getProjectFilePath(trim($projectNo, "'"));

    if (!$projectPath) {
        echo '<br>Creating folder...<br>';
        $projectPath = create_folder($nachbauFile, $omEmail, $projectNo);
    }

    // Check if Nachbau file exists in destination folder
    if (!file_exists("\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Digital_Transformation\\07_Nachbau\\$nachbauFile")) {
        $sourceFilePath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\SAP_Transfer\\$nachbauFile";
        $destinationFilePath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Digital_Transformation\\07_Nachbau\\$nachbauFile";
        
        $copySuccessful = copy($sourceFilePath, $destinationFilePath);
        
        if (!$copySuccessful)
            returnHttpResponse(500, "Nachbau file could not be transferred - SearchProject ($nachbauFile)");
        
        if (!file_exists($destinationFilePath))
            returnHttpResponse(500, "File not found - SearchProject: $nachbauFile");
    }

    copy_nachbau($projectPath, $nachbauFile);
    echo 'Process completed successfully';
}

/**
 * Copy Nachbau file to project folder
 * 
 * @param $projectPath
 * @param $nachbauFile
 * @return void
 */
function copy_nachbau($projectPath, $nachbauFile)
{
    try {
        if (!$projectPath)
            returnHttpResponse(500, 'Project Path is invalid');
        
        $folder = glob($projectPath . '\\*ekan*')[0];
        $destination = "$folder\\$nachbauFile";

        $copySuccessful = copy(
            "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\SAP_Transfer\\$nachbauFile",
            $destination
        );

        if (!$copySuccessful)
            returnHttpResponse(500, "Nachbau file could not be transferred ($nachbauFile)");
        
        echo "Nachbau file transferred ($nachbauFile)<BR>";
        lv_nachbau($folder, $nachbauFile);
        
    } catch (Exception $e) {
        MailManager::sendMail(
            '[Action Required] ERROR with Folder Name or Nachbau File', 
            'Folder name has been changed or removed from file server or Nachbau TXT file was not transferred. This email was sent from searchproject.php. Error: ' . $e, 
            7, 
            'general'
        );
        echo $e->getMessage();
    }
}

/**
 * Create LV (Low Voltage) Nachbau file
 * 
 * @param $lvFolder
 * @param $nachbauFile
 * @return void
 */
function lv_nachbau($lvFolder, $nachbauFile)
{
    try {
        if (!$lvFolder)
            returnHttpResponse(500, 'LV Folder path is invalid');
        
        $response = lvinformation($nachbauFile);
        
        // Create LV file name by replacing extension
        $nachbauFileForLv = substr($nachbauFile, 0, -4) . '_LV.txt';
        $nachbauLVPath = "$lvFolder\\$nachbauFileForLv";

        // Write LV information to file
        $fileHandle = fopen($nachbauLVPath, 'wb');
        $writeSuccessful = fwrite($fileHandle, $response);
        
        if (!$writeSuccessful)
            returnHttpResponse(500, "LV file could not be created ($nachbauLVPath)");
        
        fclose($fileHandle);
        echo "LV file created successfully ($nachbauFileForLv)<BR>";
        
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
?>