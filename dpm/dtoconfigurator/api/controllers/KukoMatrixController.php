<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/shared.php';
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class KukoMatrixController extends BaseController {

    public function getKukoMatrix(): void {
        SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | Retrieving KUKO Matrix Data | " . implode(' | ', $_GET));
        Journals::saveJournal("PROCESSING | Retrieving KUKO Matrix Data | " . implode(' | ', $_GET),PAGE_PROJECTS,DESIGN_DETAIL_KUKO_MATRIX,ACTION_PROCESSING,implode(' | ', $_GET),"Get KUKO Matrix");

        $isAdminPage = ($_GET['page'] === 'admin');

        if ($isAdminPage) {
            $releasedProjectId = $_GET['releasedProjectId'];
            $query = "SELECT project_number, nachbau_number FROM view_released_projects WHERE released_project_id = :releasedProjectId";
            $releasedProjectDetails = DbManager::fetchPDOQueryData('dto_configurator', $query,[':releasedProjectId' => $releasedProjectId])['data'][0];

            $projectNo = $releasedProjectDetails['project_number'];
            $nachbauNo = $releasedProjectDetails['nachbau_number'];
            $accessoryTypicalCode = $this->getAccessoryTypicalOfProject($projectNo);
            $type = '';
            $typeNumber = '';
            $dtoNumber = '';
        } else {
            $projectNo = $_GET['projectNo'];
            $nachbauNo = $_GET['nachbauNo'];
            $accessoryTypicalCode = $_GET['accessoryTypicalCode'];
            $type = $_GET['type'];
            $typeNumber = $_GET['typeNumber'];
            $dtoNumber = $_GET['dtoNumber'];
        }

        $data['rows'] = [];
        $data['colors'] = [];
        $data['panelCounts'] = [];

        $dtos_with_descriptions = $this->getDtos($projectNo, $nachbauNo); // DTOs RELATED TO NACHBAU, RETURNS DTONUMBER AND DESCRIPTION
        $typicalNumber = $this->getDtosWithTypicals($projectNo, $nachbauNo, 'typical_no', $accessoryTypicalCode); // RETURNS ARRAY OF [TYPICAL, DTOS[]] -> Example: Array["=LZ00"] = ["NX18", "NX130", ...]
        $typicalsWithPanels = $this->getTypicals($projectNo,$nachbauNo); // TYPICALS WITH PANEL NUMBERS LZ01 > 002000, 003000 ...
        $kukoColors = $this->getKuKoColors($projectNo, $nachbauNo);
        $kukoNotes = $this->getKuKoNotes($projectNo, $nachbauNo);


        if($type === 'Typical' || $type === 'Panel')
        {
            // IF TYPICAL OR PANEL LIST TYPE SELECTED
            $typicals = array_keys($typicalsWithPanels);
            sort($typicals);

            // Move $accessoryTypicalCode to the first position in $typicals
            if(($key = array_search($accessoryTypicalCode, $typicals)) !== false) {
                unset($typicals[$key]);
                array_unshift($typicals, $accessoryTypicalCode);
            }

            // IF TYPICAL NUMBER AND DTO FILTER NULL
            if(!$typeNumber && !$dtoNumber)
            {
                $data['filter'] = 'no_filter';
                $data['columns'] = $typicals;

                $accessory_dtos_with_kuko = $typicalNumber[$accessoryTypicalCode] ?? []; //LZ00 or MZ00 or KZ00 or ...
                $accessory_dtos = array_map(function($dto) {
                    return $this->formatKukoDtoNumber($dto);
                }, $accessory_dtos_with_kuko);

                foreach ($dtos_with_descriptions as $dto) {
                    if(in_array($dto['DtoNumber'], $accessory_dtos_with_kuko))
                        continue;

                    $row_data = [
                        "DtoNumber" => $dto['DtoNumber'],
                        "Description" => $dto['description'],
                        "IsDtoDeleted" => $this->isDtoDeletedCheck($projectNo,$nachbauNo,$dto['DtoNumber'])
                    ];

                    $trimmed_dto_number = substr($dto['DtoNumber'], 0, -1);

                    if (in_array($trimmed_dto_number, $accessory_dtos)) {
                        foreach ($typicals as $typical) {
                            if($typical == $accessoryTypicalCode)
                                $row_data[$typical] = 'X';
                            else
                                $row_data[$typical] = in_array($dto['DtoNumber'] ?? '', $typicalNumber[$typical] ?? []) ? 'X' : '';

                            $data['colors'][$typical][$dto['DtoNumber']] = $kukoColors[$typical][$dto['DtoNumber']]['color'] ?? 'default';
                        }
                    }
                    else
                    {
                        foreach ($typicals as $typical) {
                            $row_data[$typical] = in_array($dto['DtoNumber'] ?? '', $typicalNumber[$typical] ?? []) ? 'X' : '';
                            $data['colors'][$typical][$dto['DtoNumber']] = $kukoColors[$typical][$dto['DtoNumber']]['color'] ?? 'default';
                        }
                    }

                    //Check if only accessory typical has X data or not
                    $onlyAccessory = true;
                    foreach ($row_data as $key => $value) {
                        if ($key != $accessoryTypicalCode && $value === 'X') {
                            $onlyAccessory = false;  // If any other typical has 'X', set it to false
                            break;
                        }
                    }

                    if(!$onlyAccessory)
                        $row_data[$accessoryTypicalCode] = '';

                    $data['rows'][$dto['DtoNumber']] = $row_data;
                }

                foreach ($typicals as $typical) {
                    $data['panelCounts'][$typical] = count($typicalsWithPanels[$typical]);
                }

                $data['notes'] = $kukoNotes;

                SharedManager::saveLog('log_dtoconfigurator', "RETURNED | KUKO Matrix Retrieved With Following Parameters | ".implode(' | ', $_GET));
                Journals::saveJournal("RETURNED | KUKO Matrix Retrieved With Following Parameters | ".implode(' | ', $_GET), PAGE_PROJECTS,DESIGN_DETAIL_KUKO_MATRIX,ACTION_VIEWED, implode(' | ', $_GET),"Get KUKO Matrix");

                ob_start("ob_gzhandler");

                header("Content-Encoding: gzip");
                header("Content-Type: application/json");

                echo(json_encode($data));exit();
            }
            else
            {
                // IF ONLY TYPICAL OR PANEL NUMBER SELECTED
                if($typeNumber && !$dtoNumber)
                {
                    $data['filter'] = ($type == 'Typical') ? 'typical_filter' : 'panel_filter';
                    // Filter by Typical Number or Filter By Ortz_Kz
                    $targetTypical = ($type == 'Typical') ? $typeNumber : $this->getTypicalNumberByOrtzKz($projectNo, $nachbauNo, $typeNumber);
                    $data['columns'] = [$targetTypical];

                    foreach ($dtos_with_descriptions as $dto) {
                        if (in_array($dto["DtoNumber"], $typicalNumber[$targetTypical]) && !str_starts_with($dto['DtoNumber'], ":: KUKO" )) {
                            $data['rows'][$dto['DtoNumber']]["DtoNumber"] = $dto["DtoNumber"];
                            $data['rows'][$dto['DtoNumber']]["Description"] = $dto["description"];
                            $data['rows'][$dto['DtoNumber']]["IsDtoDeleted"] = $this->isDtoDeletedCheck($projectNo,$nachbauNo,$dto['DtoNumber']);
                            $data['rows'][$dto['DtoNumber']][$targetTypical] = in_array($dto['DtoNumber'] ?? '', $typicalNumber[$targetTypical] ?? []) ? 'X' : '';
                            $data['colors'][$targetTypical][$dto['DtoNumber']] = $kukoColors[$targetTypical][$dto['DtoNumber']]['color'] ?? 'default';
                        }
                    }

                    $data['panelCounts'][$targetTypical] = count($typicalsWithPanels[$targetTypical]);
                    $data['notes'] = $kukoNotes;

                    SharedManager::saveLog('log_dtoconfigurator',"RETURNED | KuKo Matrix for Typical : " . $targetTypical . " Filtered Retrieved With Following Parameters | ".implode(' | ', $_POST));
                    Journals::saveJournal("RETURNED | KuKo Matrix for Typical : " . $targetTypical . " Filtered Retrieved With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS,DESIGN_DETAIL_KUKO_MATRIX,ACTION_VIEWED,implode(' | ', $_POST),"KuKo Matrix");

                    ob_start("ob_gzhandler");

                    header("Content-Encoding: gzip");
                    header("Content-Type: application/json");

                    echo(json_encode($data)); exit();
                }
                else
                {
                    //IF DTO NUMBER SELECTED
                    $data['filter'] = 'dto_filter';
                    $data['columns'] = $typicals;
                    $data['rows'][$dtoNumber]['DtoNumber'] = $dtoNumber;
                    $data['rows'][$dtoNumber]['Description'] = $this->getNachbauDescriptionByDtoNumber($dtoNumber, $projectNo, $nachbauNo, false);
                    $data['rows'][$dtoNumber]['IsDtoDeleted'] = $this->isDtoDeletedCheck($projectNo,$nachbauNo,$dtoNumber);

                    foreach ($typicals as $typical) {
                        if ($typical == $accessoryTypicalCode) {
                            $acc_dto_to_check = substr(str_replace(":: ", ":: KUKO_CON_CST_", $dtoNumber), 0, -1);
                            if (in_array($acc_dto_to_check, $typicalNumber[$typical] ?? []))
                                $data['rows'][$dtoNumber][$typical] = "X";
                            else
                                $data['rows'][$dtoNumber][$typical] = "";
                        }
                        else
                            $data['rows'][$dtoNumber][$typical] = in_array($dtoNumber ?? '', $typicalNumber[$typical] ?? []) ? 'X' : '';

                        //Check if only accessory typical has X data or not
                        $onlyAccessory = true;
                        foreach ($data['rows'][$dtoNumber] as $key => $value) {
                            if ($key != $accessoryTypicalCode && $value === 'X') {
                                $onlyAccessory = false;  // If any other typical has 'X', set it to false
                                break;
                            }
                        }

                        if(!$onlyAccessory)
                            $data['rows'][$dtoNumber][$accessoryTypicalCode] = '';

                        $data['colors'][$typical][$dtoNumber] = $kukoColors[$typical][$dtoNumber]['color'] ?? 'default';
                        $data['panelCounts'][$typical] = count($typicalsWithPanels[$typical]);
                    }
                    $data['notes'] = $kukoNotes;

                    SharedManager::saveLog('log_dtoconfigurator',"RETURNED | KuKo Matrix for DTO Number " . $dtoNumber . " Retrieved With Following Parameters | ".implode(' | ', $_GET));
                    Journals::saveJournal("RETURNED | KuKo Matrix for DTO Number " . $dtoNumber . " Retrieved With Following Parameters | ".implode(' | ', $_GET), PAGE_PROJECTS,DESIGN_DETAIL_KUKO_MATRIX,ACTION_VIEWED,implode(' | ', $_GET),"KuKo Matrix");

                    ob_start("ob_gzhandler");

                    header("Content-Encoding: gzip");
                    header("Content-Type: application/json");

                    echo(json_encode($data));exit();
                }
            }
        }
        else if ($type === 'Accessories') {
            // IF ACCESSORY LIST SELECTED
            if($dtoNumber)
            {
                //IF ACCESSORY LIST AND DTO NUMBER SELECTED
                $data['filter'] = 'dto_filter';
                $data['columns'] = [$accessoryTypicalCode];
                $data['rows'][$dtoNumber] = array(
                    'DtoNumber' => $dtoNumber,
                    'Description' => $this->getNachbauDescriptionByDtoNumber($dtoNumber, $projectNo, $nachbauNo, false),
                    'IsDtoDeleted' => $this->isDtoDeletedCheck($projectNo,$nachbauNo,$dtoNumber),
                    $accessoryTypicalCode => 'X'
                );
                $data['colors'][$accessoryTypicalCode][$data['rows']['DtoNumber']] = $kukoColors[$accessoryTypicalCode][$data['rows']['DtoNumber']]['color'] ?? 'default';
                $data['panelCounts'][$accessoryTypicalCode] = count($typicalsWithPanels[$accessoryTypicalCode]);
                $data['notes'] = $kukoNotes;

                SharedManager::saveLog('log_dtoconfigurator', "RETURNED | KuKo Matrix Accessory Type List Retrieved With Following Parameters | ".implode(' | ', $_GET));
                Journals::saveJournal("RETURNED | KuKo Matrix Accessory Type List Retrieved With Following Parameters | ".implode(' | ', $_GET), PAGE_PROJECTS,DESIGN_DETAIL_KUKO_MATRIX,ACTION_VIEWED,implode(' | ', $_GET),"KuKo Matrix");

                ob_start("ob_gzhandler");

                header("Content-Encoding: gzip");
                header("Content-Type: application/json");

                echo (json_encode($data));exit();
            }
            else
            {
                $data['filter'] = 'type_accessory_filter';
                $data['columns'] = [$accessoryTypicalCode];

                $result_array = [];
                $accessory_dtos = $typicalNumber[$accessoryTypicalCode];

                foreach ($accessory_dtos as $dto1) {
                    $currentDtoNumber = str_replace(":: KUKO_CON_CST_", "", $dto1);
                    $count = 0;


                    foreach ($dtos_with_descriptions as $dto2)
                    {
                        if (str_contains($dto2['DtoNumber'], $currentDtoNumber))
                            $count++;
                    }

                    // If the DTO is only exist once and its typical is .Z00, that means its accessory not typical.
                    if ($count === 1)
                    {
                        $description = $this->getNachbauDescriptionByDtoNumber($currentDtoNumber, $projectNo, $nachbauNo, false);

                        $result_array[] = array(
                            "DtoNumber" => $currentDtoNumber,
                            "Description" => ($description !== null) ? $description : "",
                            "IsDtoDeleted" => $this->isDtoDeletedCheck($projectNo,$nachbauNo,$currentDtoNumber),
                        );
                    }
                }

                foreach($result_array as $dto_desc)
                {
                    $row_data = array(
                        "DtoNumber" => $dto_desc['DtoNumber'],
                        "Description" => $dto_desc['Description'],
                        "IsDtoDeleted" => $this->isDtoDeletedCheck($projectNo,$nachbauNo,$dto_desc['DtoNumber']),
                        $accessoryTypicalCode => 'X'
                    );

                    $data['rows'][$dto_desc['DtoNumber']] = $row_data;
                    $data['colors'][$accessoryTypicalCode][$dto_desc['DtoNumber']] = $kukoColors[$accessoryTypicalCode][$dto_desc['DtoNumber']]['color'] ?? 'default';
                }
                $data['panelCounts'][$accessoryTypicalCode] = count($typicalsWithPanels[$accessoryTypicalCode]);
                $data['notes'] = $kukoNotes;

                SharedManager::saveLog('log_dtoconfigurator',"RETURNED | KuKo Matrix Accessory Type List Retrieved With Following Parameters | ".implode(' | ', $_GET));
                Journals::saveJournal("RETURNED | KuKo Matrix Accessory Type List Retrieved With Following Parameters | ".implode(' | ', $_GET), PAGE_PROJECTS,DESIGN_DETAIL_KUKO_MATRIX,ACTION_VIEWED,implode(' | ', $_GET),"KuKo Matrix");

                ob_start("ob_gzhandler");

                header("Content-Encoding: gzip");
                header("Content-Type: application/json");

                echo(json_encode($data));exit();
            }
        }
        else
        {
            // IF TYPE IS NOT SELECTED
            $typicals = array_keys($typicalsWithPanels);
            sort($typicals);

            // Move $accessoryTypicalCode to the first position in $typicals
            if(($key = array_search($accessoryTypicalCode, $typicals)) !== false) {
                unset($typicals[$key]);
                array_unshift($typicals, $accessoryTypicalCode);
            }

            $data['filter'] = 'no_filter';
            $data['columns'] = $typicals;

            $accessory_dtos_with_kuko = $typicalNumber[$accessoryTypicalCode] ?? [];
            $accessory_dtos = array_map(function($dto) {
                return $this->formatKukoDtoNumber($dto);
            }, $accessory_dtos_with_kuko);

            foreach ($dtos_with_descriptions as $dto) {
                if(in_array($dto['DtoNumber'], $accessory_dtos_with_kuko) && str_starts_with($dto['DtoNumber'], ":: KUKO" ))
                    continue;

                $row_data = [
                    "DtoNumber" => $dto['DtoNumber'],
                    "Description" => $dto['description'],
                    "IsDtoDeleted" => $this->isDtoDeletedCheck($projectNo,$nachbauNo,$dto['DtoNumber'])
                ];

                $trimmed_dto_number = substr($dto['DtoNumber'], 0, -1);

                if (in_array($trimmed_dto_number, $accessory_dtos)) {
                    foreach ($typicals as $typical) {
                        if($typical == $accessoryTypicalCode)
                            $row_data[$typical] = 'X';
                        else
                            $row_data[$typical] = in_array($dto['DtoNumber'] ?? '', $typicalNumber[$typical] ?? []) ? 'X' : '';

                        $data['colors'][$typical][$dto['DtoNumber']] = $kukoColors[$typical][$dto['DtoNumber']]['color'] ?? 'default';

                    }
                }
                else
                {
                    foreach ($typicals as $typical) {
                        $row_data[$typical] = in_array($dto['DtoNumber'] ?? '', $typicalNumber[$typical] ?? []) ? 'X' : '';
                        $data['colors'][$typical][$dto['DtoNumber']] = $kukoColors[$typical][$dto['DtoNumber']]['color'] ?? 'default';
                    }
                }

                //Check if only accessory typical has X data or not
                $onlyAccessory = true;
                foreach ($row_data as $key => $value) {
                    if ($key != $accessoryTypicalCode && $value === 'X') {
                        $onlyAccessory = false;  // If any other typical has 'X', set it to false
                        break;
                    }
                }

                if(!$onlyAccessory)
                    $row_data[$accessoryTypicalCode] = '';

                $data['rows'][$dto['DtoNumber']] = $row_data;
            }

            foreach ($typicals as $typical) {
                $data['panelCounts'][$typical] = count($typicalsWithPanels[$typical]);
            }
            $data['notes'] = $kukoNotes;

            ob_start("ob_gzhandler");

            header("Content-Encoding: gzip");
            header("Content-Type: application/json");

            echo (json_encode($data));exit();
        }
    }

    public function getKuKoNotes($projectNo, $nachbauNo):array {
        $query = "SELECT * FROM kuko_notes WHERE project_number=:pNumber AND nachbau_number=:nNumber AND deleted IS NULL";
        return DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNumber'=>$projectNo, ':nNumber'=>$nachbauNo])['data'] ?? [];
    }

    public function getKuKoColors($projectNo, $nachbauNo): array {
        $query = "SELECT * FROM kuko_colors WHERE project_no=:pNumber AND nachbau_no=:nNumber";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNumber'=>$projectNo, ':nNumber'=>$nachbauNo])['data'] ?? [];
        $data = [];
        foreach ($result as $item) {
            $data[$item['typical_number']][$item['dto_number']] = $item;
        }
        return $data;
    }

    public function isDtoDeletedCheck($projectNo,$nachbauNo,$dtoNumber): bool {
        if (str_starts_with($dtoNumber, ':: KUKO'))
            $dtoNumber = $this->formatKukoDtoNumber($dtoNumber);
        else if (str_starts_with($dtoNumber, ':: '))
            $dtoNumber = $this->formatDtoNumber($dtoNumber);

        $query = "SELECT id FROM project_work_view WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNumber AND is_dto_deleted = 1";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => $dtoNumber])['data'][0];

        if(!empty($result))
            return true;

        return false;
    }

    public function createKukoMatrixNote(): void
    {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | KUKO Note Create Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | KUKO Note Create Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_KUKO_MATRIX, ACTION_PROCESSING, implode(' | ', $_POST), "Create KUKO Note");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $dtoNumber = $_POST['dtoNumber'];
        $kukoNote = $_POST['kukoNote'];
        $radioButtonOption = $_POST['radioButtonOption'];

        if ($radioButtonOption === 'excludeAllTeams' || $radioButtonOption === 'excludeMechanicalTeam') {
            $isDtoDeleted = 1;
            $query = "SELECT id FROM project_work_view WHERE project_number = :pNo AND nachbau_number = :nNo AND nachbau_dto_number = :dtoNo";
            $parameters = [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNo' => $dtoNumber];
            $projectWorkData = DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'] ?? [];

            if (!empty($projectWorkData)) {
                $projectWorkIds = array_column($projectWorkData, 'id');
                $query = "UPDATE project_works SET is_dto_deleted = 1 WHERE id IN (:pwIds)";
                DbManager::fetchPDOQueryData('dto_configurator', $query, [':pwIds' => $projectWorkIds])['data'] ?? [];
            }
        } else {
            //DAHA ONCEDEN DTO SİLİNECEK OLARAK İŞARETLEYİP PROJECT WORKDE GİZLENEN DTOLARI SONRADAN GERİ ÇEKME DURUMU
            $isDtoDeleted = 0;
            $query = "SELECT id FROM project_work_view 
                      WHERE project_number = :pNo AND nachbau_number = :nNo AND nachbau_dto_number = :dtoNo AND is_dto_deleted = 1";
            $parameters = [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNo' => $dtoNumber];
            $projectWorkData = DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'] ?? [];

            if (!empty($projectWorkData)) {
                $projectWorkIds = array_column($projectWorkData, 'id');
                $query = "UPDATE project_works SET is_dto_deleted = 0 WHERE id IN (:pwIds)";
                DbManager::fetchPDOQueryData('dto_configurator', $query, [':pwIds' => $projectWorkIds])['data'] ?? [];
            }
        }

        $query = "INSERT INTO kuko_notes(project_number, nachbau_number, dto_number, kuko_note, is_dto_deleted, dto_exclude_option, created_by, updated_by)";
        $params[] = [$projectNo, $nachbauNo, $dtoNumber, $kukoNote, $isDtoDeleted, $radioButtonOption, SharedManager::$fullname, SharedManager::$fullname];
        DbManager::fetchInsert('dto_configurator', $query, $params);

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | KUKO Note Successfully Created");
        Journals::saveJournal("RETURNED | KUKO Note Successfully Created", PAGE_PROJECTS, DESIGN_DETAIL_KUKO_MATRIX, ACTION_CREATED, implode(' | ', $_POST), "Create KUKO Note");
    }

    public function getKukoNoteDetailsByDtoNumber():void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $dtoNumber = $_GET['dtoNumber'];

        $query = "SELECT id, kuko_note, is_dto_deleted, dto_exclude_option, updated_by, DATE_FORMAT(created, '%d.%m.%Y') AS created
                  FROM kuko_notes
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNo AND deleted IS NULL";

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNo' => $dtoNumber])['data'] ?? [];
        echo(json_encode($data));exit();
    }

    public function updateKukoMatrixNote(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | KUKO Note Update Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | KUKO Note Update Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_KUKO_MATRIX, ACTION_PROCESSING, implode(' | ', $_POST), "Update KUKO Note");

        $noteId = $_POST['noteId'];
        $dtoNoteUpdated = $_POST['dtoNoteUpdated'];
        $radioButtonOption = $_POST['radioButtonOption'];

        $query = "SELECT project_number, nachbau_number, dto_number, is_dto_deleted FROM kuko_notes WHERE id = :noteId";
        $oldKukoNoteData = DbManager::fetchPDOQueryData('dto_configurator', $query, [':noteId' => $noteId])['data'][0] ?? [];
        $oldIsDtoDeleted = intval($oldKukoNoteData['is_dto_deleted']);

        if ($radioButtonOption === 'excludeAllTeams' || $radioButtonOption === 'excludeMechanicalTeam')
            $newIsDtoDeleted = 1;
        else
            $newIsDtoDeleted = 0;

        if ($newIsDtoDeleted !== $oldIsDtoDeleted) {
            if ($radioButtonOption === 'excludeAllTeams' || $radioButtonOption === 'excludeMechanicalTeam') {
                $query = "SELECT id FROM project_work_view WHERE project_number = :pNo AND nachbau_number = :nNo AND nachbau_dto_number = :dtoNo";
                $parameters = [':pNo' => $oldKukoNoteData['project_number'], ':nNo' => $oldKukoNoteData['nachbau_number'], ':dtoNo' => $oldKukoNoteData['dto_number']];
                $projectWorkData = DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'] ?? [];

                if (!empty($projectWorkData)) {
                    $projectWorkIds = array_column($projectWorkData, 'id');
                    $query = "UPDATE project_works SET is_dto_deleted = 1 WHERE id IN (:pwIds)";
                    DbManager::fetchPDOQueryData('dto_configurator', $query, [':pwIds' => $projectWorkIds])['data'] ?? [];
                }
            } else {
                //DAHA ONCEDEN DTO SİLİNECEK OLARAK İŞARETLEYİP PROJECT WORKDE GİZLENEN DTOLARI SONRADAN GERİ ÇEKME DURUMU
                $query = "SELECT id FROM project_work_view 
                          WHERE project_number = :pNo AND nachbau_number = :nNo AND nachbau_dto_number = :dtoNo AND is_dto_deleted = 1";
                $parameters = [':pNo' => $oldKukoNoteData['project_number'], ':nNo' => $oldKukoNoteData['nachbau_number'], ':dtoNo' => $oldKukoNoteData['dto_number']];
                $projectWorkData = DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'] ?? [];

                if (!empty($projectWorkData)) {
                    $projectWorkIds = array_column($projectWorkData, 'id');
                    $query = "UPDATE project_works SET is_dto_deleted = 0 WHERE id IN (:pwIds)";
                        DbManager::fetchPDOQueryData('dto_configurator', $query, [':pwIds' => $projectWorkIds])['data'] ?? [];
                }
            }
        }

        $query = "UPDATE kuko_notes SET kuko_note = :uptNote, is_dto_deleted = :isDtoDeleted, dto_exclude_option = :dtoExcludeOption, updated_by = :upt WHERE id=:noteId";
        DbManager::fetchPDOQueryData('dto_configurator', $query,
            [':uptNote' => $dtoNoteUpdated, ':isDtoDeleted' => $newIsDtoDeleted, ':dtoExcludeOption' => $radioButtonOption, ':upt' => SharedManager::$fullname, ':noteId' => $noteId]);

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | KUKO Note Successfully Updated!");
        Journals::saveJournal("RETURNED | KUKO Note Successfully Updated!", PAGE_PROJECTS, DESIGN_DETAIL_KUKO_MATRIX, ACTION_MODIFIED, implode(' | ', $_POST), "Update KUKO Note");
    }

    public function updateKukoCellColor(): void {
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $rawDtoNumber = $_POST['rawDtoNumber'];
        $typicalNo = $_POST['typicalNo'];
        $color = $_POST['color'];

        $query = "INSERT INTO kuko_colors (uniq, color, project_no, nachbau_no, dto_number, typical_number, created_by, updated_by)
                  VALUES (:unq, :clr, :pNo, :nachbau, :dto, :typical, :cr, :up)
                  ON DUPLICATE KEY UPDATE color = VALUES(color), updated_by = VALUES(updated_by)";

        $parameters = [
            ':unq' => $projectNo . $nachbauNo . $rawDtoNumber . $typicalNo,
            ':clr' => $color,
            ':pNo' => $projectNo,
            ':nachbau' => $nachbauNo,
            ':dto' => $rawDtoNumber,
            ':typical' => $typicalNo,
            ':cr' => SharedManager::$email,
            ':up' => SharedManager::$email
        ];

        DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters);

        SharedManager::saveLog('log_dtoconfigurator', "UPDATED | KuKo Matrix Color Changed To '" . $_POST['color'] . "' With Following Parameters | " . implode(' | ', $_POST));
        Journals::saveJournal("UPDATED | KuKo Matrix Color Changed To '" . $_POST['color'] . "' With Following Parameters | " . implode(' | ', $_POST), PAGE_PROJECTS,DESIGN_DETAIL_KUKO_MATRIX,ACTION_MODIFIED, implode(' | ', $_POST),"KuKo Matrix");
    }

    public function deleteKukoMatrixNote(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | KUKO Note Delete Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | KUKO Note Delete Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_KUKO_MATRIX, ACTION_PROCESSING, implode(' | ', $_POST), "Delete KUKO Note");

        $noteId = $_POST['noteId'];

        $query = "SELECT project_number, nachbau_number, dto_number, is_dto_deleted FROM kuko_notes WHERE id = :noteId";
        $deletedKukoNoteData = DbManager::fetchPDOQueryData('dto_configurator', $query, [':noteId' => $noteId])['data'][0] ?? [];
        $deletedIsDtoDeleted = $deletedKukoNoteData['is_dto_deleted'];

        // is dto deleted 1 olan notu silersek, bu dto proejct work de görünmesi için bu dtonun is dto deleted çalışmalarını 0 a çekeriz.
        if ($deletedIsDtoDeleted === "1") {
            $query = "SELECT id FROM project_work_view WHERE project_number = :pNo AND nachbau_number = :nNo AND nachbau_dto_number = :dtoNo";
            $parameters = [':pNo' => $deletedKukoNoteData['project_number'], ':nNo' => $deletedKukoNoteData['nachbau_number'], ':dtoNo' => $deletedKukoNoteData['dto_number']];
            $projectWorkData = DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'] ?? [];

            if (!empty($projectWorkData)) {
                $projectWorkIds = array_column($projectWorkData, 'id');
                $query = "UPDATE project_works SET is_dto_deleted = 0 WHERE id IN (:pwIds)";
                DbManager::fetchPDOQueryData('dto_configurator', $query, [':pwIds' => $projectWorkIds])['data'] ?? [];
            }
        }

        $query = "UPDATE kuko_notes SET deleted_user = :delUser, deleted = :deletedAt, updated_by = :upt WHERE id=:noteId";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':upt' => SharedManager::$fullname,':noteId' => $noteId]);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | KUKO Note Successfully Deleted!");
        Journals::saveJournal("DELETED | KUKO Note Successfully Deleted!", PAGE_PROJECTS, DESIGN_DETAIL_KUKO_MATRIX, ACTION_DELETED, implode(' | ', $_POST), "Delete KUKO Note");
    }

    public function getKukoMatrixDataForExcelSheet($projectNo, $nachbauNo) {
        $accessoryTypicalCode = $this->getAccessoryTypicalOfProject($projectNo);

        $dtos_with_descriptions = $this->getDtos($projectNo, $nachbauNo);
        $typicalNumber = $this->getDtosWithTypicals($projectNo, $nachbauNo, 'typical_no', $accessoryTypicalCode);
        $typicalsWithPanels = $this->getTypicals($projectNo,$nachbauNo);
        $kukoColors = $this->getKuKoColors($projectNo, $nachbauNo);
        $kukoNotes = $this->getKuKoNotes($projectNo, $nachbauNo);

        // IF TYPE IS NOT SELECTED
        $typicals = array_keys($typicalsWithPanels);
        sort($typicals);

        // Move $accessoryTypicalCode to the first position in $typicals
        if(($key = array_search($accessoryTypicalCode, $typicals)) !== false) {
            unset($typicals[$key]);
            array_unshift($typicals, $accessoryTypicalCode);
        }

        $data['filter'] = 'no_filter';
        $data['columns'] = $typicals;

        $accessory_dtos_with_kuko = $typicalNumber[$accessoryTypicalCode] ?? [];
        $accessory_dtos = array_map(function($dto) {
            return $this->formatKukoDtoNumber($dto);
        }, $accessory_dtos_with_kuko);

        foreach ($dtos_with_descriptions as $dto) {
            if(in_array($dto['DtoNumber'], $accessory_dtos_with_kuko) && str_starts_with($dto['DtoNumber'], ":: KUKO" ))
                continue;

            $row_data = [
                "DtoNumber" => $dto['DtoNumber'],
                "Description" => $dto['description'],
                "DescriptionTr" => html_entity_decode($this->getTurkishDescriptionOfDto($dto['DtoNumber'])),
                "IsDtoDeleted" => $this->isDtoDeletedCheck($projectNo,$nachbauNo,$dto['DtoNumber'])
            ];

            $trimmed_dto_number = substr($dto['DtoNumber'], 0, -1);

            if (in_array($trimmed_dto_number, $accessory_dtos)) {
                foreach ($typicals as $typical) {
                    if($typical == $accessoryTypicalCode)
                        $row_data[$typical] = 'X';
                    else
                        $row_data[$typical] = in_array($dto['DtoNumber'] ?? '', $typicalNumber[$typical] ?? []) ? 'X' : '';

                    $data['colors'][$typical][$dto['DtoNumber']] = $kukoColors[$typical][$dto['DtoNumber']]['color'] ?? 'default';

                }
            }
            else
            {
                foreach ($typicals as $typical) {
                    $row_data[$typical] = in_array($dto['DtoNumber'] ?? '', $typicalNumber[$typical] ?? []) ? 'X' : '';
                    $data['colors'][$typical][$dto['DtoNumber']] = $kukoColors[$typical][$dto['DtoNumber']]['color'] ?? 'default';
                }
            }

            //Check if only accessory typical has X data or not
            $onlyAccessory = true;
            foreach ($row_data as $key => $value) {
                if ($key != $accessoryTypicalCode && $value === 'X') {
                    $onlyAccessory = false;  // If any other typical has 'X', set it to false
                    break;
                }
            }

            if(!$onlyAccessory)
                $row_data[$accessoryTypicalCode] = '';

            $data['rows'][$dto['DtoNumber']] = $row_data;
        }

        foreach ($typicals as $typical) {
            $data['panelCounts'][$typical] = count($typicalsWithPanels[$typical]);
        }
        $data['notes'] = $kukoNotes;

        ob_start("ob_gzhandler");
        header("Content-Encoding: gzip");
        header("Content-Type: application/json");

        return $data;
    }

    public function getTurkishDescriptionOfDto($dtoNumber) {
        $query = "SELECT description_tr FROM tkforms WHERE dto_number LIKE :dtoNumber";
        $descriptionTr = DbManager::fetchPDOQueryData('dto_configurator', $query, [':dtoNumber' => '%'.$this->formatDtoNumber($dtoNumber).'%'])['data'][0]['description_tr'] ?? '';

        // If not exists in DTO configurator db, check for planning
        if (empty($descriptionTr)) {
            $query = "SELECT Description_TR FROM nachbau_dtos_description WHERE DtoNumber LIKE :dtoNumber";
            $descriptionTr = DbManager::fetchPDOQueryData('planning', $query, [':dtoNumber' => '%'.$dtoNumber.'%'])['data'][0]['Description_TR'] ?? '';
        }

        return $descriptionTr;
    }
}

$controller = new KukoMatrixController($_POST);

$response = match ($_GET['action']) {
    'getKukoMatrix' => $controller->getKukoMatrix(),
    'getKukoNoteDetailsByDtoNumber' => $controller->getKukoNoteDetailsByDtoNumber(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    'createKukoMatrixNote' => $controller->createKukoMatrixNote(),
    'updateKukoMatrixNote' => $controller->updateKukoMatrixNote(),
    'deleteKukoMatrixNote' => $controller->deleteKukoMatrixNote(),
    'updateKukoCellColor' => $controller->updateKukoCellColor(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};