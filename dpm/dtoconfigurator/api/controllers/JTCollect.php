<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/dpm/dtoconfigurator/api/models/Journals.php';
ini_set('memory_limit', '1024M');

if (isset($_GET['project-no']) && isset($_GET['jt-numbers'])) {
    $projectNumber = $_GET['project-no'];
    $nachbauNumber = $_GET['nachbau-no'];
    $jtNumbers = explode('|', $_GET['jt-numbers']);
    $zipFileName = $projectNumber . '_' . date('dmYHis') . '.zip';
    $sourceRootPath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Product_Management\\04_Design_Documents\\02_MV_Docs\\NormResimlerListeler";
    $materials = [];
    $zip = new ZipArchive();
    if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
        foreach ($jtNumbers as $jtNumber) {
            list($type, $typicalNo, $ortzkz, $panel) = explode(',', $jtNumber);
            $materials = getNachbauData($projectNumber, $nachbauNumber, $typicalNo, $ortzkz, $panel);

            if ($type === 'dto') {
                $materials_dto = getDtoData($projectNumber, $nachbauNumber, $typicalNo, $ortzkz);

                foreach ($materials_dto as $item) {
                    $materialno = $item['materialnoadded'];
                    $materialnodeleted = $item['materialnodeleted'];

                    if ($item['operation'] === 'replace') {
                        // Replace
                        $materialnodeleted_index = array_search($materialnodeleted, $materials);
                        if ($materialnodeleted_index !== false) {
                            unset($materials[$materialnodeleted_index]);
                        }
                        $materials[] = $materialno;
                    } elseif ($item['operation'] === 'delete') {
                        // Delete
                        $materialnodeleted_index = array_search($materialnodeleted, $materials);
                        if ($materialnodeleted_index !== false) {
                            unset($materials[$materialnodeleted_index]);
                        }
                    } elseif ($item['operation'] === 'add') {
                        // Add
                        $materials[] = $materialno;
                    }
                }
                unset($materials_dto); // Free memory
            }

            // Handle file processing and adding to the ZIP
            $folderName = $type . '_' . $typicalNo . '_' . $ortzkz;
            $zip->addEmptyDir($folderName);

            foreach ($materials as $material) {
                $folderNamePart = substr($material, 0, 3);
                $pattern = "$sourceRootPath/$folderNamePart/JT/$material*.[jJ][tT]";
                $foundJTFiles = glob($pattern);

                if (!empty($foundJTFiles)) {
                    foreach ($foundJTFiles as $foundJTFile) {
                        $foundJTFileName = basename($foundJTFile);
                        $zip->addFile($foundJTFile, $folderName . '/' . $foundJTFileName);
                    }
                }
            }

            unset($materials); // Free memory
        }

        $zip->close();

        // Stream the file to the user
        downloadFile($zipFileName, $zipFileName);

    } else {
        SharedManager::saveLog('log_dtoconfigurator',"ERROR | Failed to Create a Zip File with JTs | ".implode(' | ', $_GET));
        Journals::saveJournal("ERROR | Failed to Create a Zip File with JTs | ".implode(' | ', $_GET), PAGE_PROJECTS, DESIGN_DETAIL_JT_COLLECTION, ACTION_ERROR, implode(' | ', $_GET), "JT Collection");
    }
}

function downloadFile($filePath, $fileName): void {
    SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | JT Download Request With Following Parameters | ".implode(' | ', $_GET));
    Journals::saveJournal("PROCESSING | JT Download Request With Following Parameters | " . implode(' | ', $_GET), PAGE_PROJECTS, DESIGN_DETAIL_JT_COLLECTION, ACTION_PROCESSING, implode(' | ', $_GET), "JT Collection");

    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        ob_clean();
        flush();
        readfile($filePath);
        unlink($filePath); // Delete the ZIP file after download

        SharedManager::saveLog('log_dtoconfigurator', "JT Download Request Successful! | ".implode(' | ', $_GET));
        Journals::saveJournal("RETURNED | JT Download Request Successful!", PAGE_PROJECTS,DESIGN_DETAIL_JT_COLLECTION,ACTION_VIEWED, implode(' | ', $_GET),"JT Collection");
        exit;
    }
}

function getDtoData($projectNo, $nachbauNo, $typicalNo, $ortzKz){
    $query = "SELECT material_added_number AS materialnoadded, material_deleted_number AS materialnodeleted, operation, release_items, accessory_release_items
              FROM project_work_view 
              WHERE 
                project_number = :projectNo 
                AND nachbau_number = :nachbauNo 
                AND (release_status = 'to be released' OR release_status = 'released');";

    $normalDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [
        ':projectNo' => $projectNo,
        ':nachbauNo' => $nachbauNo
    ])['data'] ?? [];

    $filteredNormalDtoWorks = [];

    foreach ($normalDtoWorks as $row) {
        $releaseItems = explode('|', $row['release_items'] ?? '');
        $accessoryReleaseItems = explode('|', $row['accessory_release_items'] ?? '');

        if (in_array($typicalNo, $releaseItems) || in_array($ortzKz, $releaseItems) ||
            in_array($typicalNo, $accessoryReleaseItems) || in_array($ortzKz, $accessoryReleaseItems)) {

            $filteredNormalDtoWorks[] = [
                'materialnoadded' => $row['materialnoadded'],
                'materialnodeleted' => $row['materialnodeleted'],
                'operation' => $row['operation']
            ];
        }
    }

    //Extension çalışmaları
    $query = "SELECT material_added_number AS materialnoadded, material_deleted_number AS materialnodeleted, operation
              FROM project_works_extensions
              WHERE project_number=:projectNo AND nachbau_number=:nachbauNo AND is_accessory = 0 AND typical_no = :typicalNo AND ortz_kz = :ortzKz AND deleted IS NULL;";

    $extensionDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query,
        [':projectNo' => $projectNo, ':nachbauNo' => $nachbauNo, ':typicalNo' => $typicalNo, ':ortzKz' => $ortzKz])['data'] ?? [];

    //Aktarım hataları
    $query = "SELECT material_added_number AS materialnoadded, material_deleted_number AS materialnodeleted, operation
              FROM project_works_nachbau_errors
              WHERE project_number=:projectNo AND nachbau_number=:nachbauNo AND typical_no = :typicalNo AND ortz_kz = :ortzKz AND deleted IS NULL;";

    $nachbauErrorDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query,
        [':projectNo' => $projectNo, ':nachbauNo' => $nachbauNo, ':typicalNo' => $typicalNo, ':ortzKz' => $ortzKz])['data'] ?? [];

    //Interchange Dtos
    $query = "SELECT material_added_number AS materialnoadded, material_deleted_number AS materialnodeleted, operation
              FROM project_works_interchange
              WHERE project_number=:projectNo AND nachbau_number=:nachbauNo AND typical_no = :typicalNo AND ortz_kz = :ortzKz AND deleted IS NULL;";

    $interchangeDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query,
        [':projectNo' => $projectNo, ':nachbauNo' => $nachbauNo, ':typicalNo' => $typicalNo, ':ortzKz' => $ortzKz])['data'] ?? [];

    //MIN pri
    $query = "SELECT material_deleted_number AS materialnodeleted, 'delete' AS operation
              FROM project_works_minus_price
              WHERE project_number=:projectNo AND nachbau_number=:nachbauNo AND dto_typical_number = :typicalNo AND ortz_kz = :ortzKz AND wont_be_produced = 1;";

    $minPriDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query,
        [':projectNo' => $projectNo, ':nachbauNo' => $nachbauNo, ':typicalNo' => $typicalNo, ':ortzKz' => $ortzKz])['data'] ?? [];

    //special dto
    $query = "SELECT 
                    CASE WHEN operation = 'delete' THEN material_number ELSE NULL END AS materialnodeleted,
                    CASE WHEN operation = 'add' THEN material_number ELSE NULL END AS materialnoadded,
                    operation
                FROM project_works_special_dtos
                WHERE project_number=:projectNo AND nachbau_number=:nachbauNo AND typical_no = :typicalNo AND ortz_kz = :ortzKz AND deleted IS NULL;";

    $specialDtoWorks = DbManager::fetchPDOQueryData('dto_configurator', $query,
        [':projectNo' => $projectNo, ':nachbauNo' => $nachbauNo, ':typicalNo' => $typicalNo, ':ortzKz' => $ortzKz])['data'] ?? [];

    return array_merge($filteredNormalDtoWorks, $extensionDtoWorks, $nachbauErrorDtoWorks, $interchangeDtoWorks, $minPriDtoWorks, $specialDtoWorks);
}

function getNachbauData($projectNo, $nachbauNumber, $typicalNo, $ortzKz, $panelNo): array {
    $query = "SELECT DISTINCT SUBSTRING(kmat, 3) AS kmat FROM nachbau_datas  
                WHERE project_no=:projectNo 
                AND nachbau_no=:nachbauNo
                AND typical_no=:typicalNo 
                AND ortz_kz=:ortzKz 
                AND panel_no=:panelNo 
                AND parent_kmat<>'' 
                AND kmat<>''
                AND kmat NOT LIKE '003003%'
                AND kmat NOT LIKE '003013%'
                AND description=''";

    $result = DbManager::fetchPDOQueryData('planning', $query,
        [':projectNo'=>trim($projectNo),
        ':nachbauNo'=>trim($nachbauNumber),
        ':typicalNo'=>trim($typicalNo),
        ':ortzKz'=>trim($ortzKz),
        ':panelNo'=>trim($panelNo)
        ])['data'] ?? [];

    return array_column($result, 'kmat');
}
