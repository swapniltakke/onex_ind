<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/shared.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/shared/api/MtoolManager.php';
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class ProjectController extends BaseController {

    public function getAllProjects(): void {
        // The project list shows the result that contains the most recently updated transfer among the transfers being worked on for the project.
        $query = "SELECT pj.project_number AS ProjectNumber,
                    pj.nachbau_number AS NachbauNumber,
                    po.product_type AS ProductType,
                    po.package,
                    pj.working_user AS WorkingUser,
                    ps.status AS Status,
                    pj.last_updated_by AS LastUpdatedBy,
                    pj.last_updated_date
                  FROM projects pj
                  LEFT JOIN products po ON pj.product_type = po.id
                  LEFT JOIN status ps ON pj.project_status = ps.id
                  WHERE po.product_type IS NOT NULL
                  AND (pj.last_updated_by IS NOT NULL OR pj.last_updated_by != '')
                  ORDER BY pj.last_updated_date DESC
                  LIMIT 100";

        $result = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'] ?? [];

        if (empty($result)) {
            echo json_encode([]);
            exit();
        }

        $projects = array_column($result, 'ProjectNumber');

        $projectDetails = MtoolManager::getProjectDetailsByProjectNos($projects);
        $projectDetails = array_combine(array_column($projectDetails, 'FactoryNumber'), $projectDetails);
        $projectContacts = MtoolManager::getProjectContacts($projects);
        $projectContacts = array_combine(array_column($projectContacts, 'FactoryNumber'), $projectContacts);

        $lastNachbauDates = $this->getLastNachbauDateOfProjects($projects);

        $data = [];
        foreach($result as $row){
            $row['OMGID'] = $projectContacts[$row['ProjectNumber']]['OMGID'];
            $row['OMName'] = $projectContacts[$row['ProjectNumber']]['OrderManager'];
            $row['OMMail'] =  $projectContacts[$row['ProjectNumber']]['OMEmail'];
            $row['MEGID'] =  $projectContacts[$row['ProjectNumber']]['MEGID'];
            $row['MEName'] =  $projectContacts[$row['ProjectNumber']]['MechanicalEngineer'];
            $row['MEMail'] =  $projectContacts[$row['ProjectNumber']]['MEEmail'];
            $row['EEGID'] = $projectContacts[$row['ProjectNumber']]['EEGID'];
            $row['EEMail'] =  $projectContacts[$row['ProjectNumber']]['EEEmail'];
            $row['EEName'] =  $projectContacts[$row['ProjectNumber']]['ElectricalEngineer'];
            $row['ProjectName'] =  $projectDetails[$row['ProjectNumber']]['ProjectName'];
            $row['PanelQuantity'] = $projectDetails[$row['ProjectNumber']]['Qty'];
            $row['UpdatedDate'] = array(
                "ProjectLastUpdatedDate" => !empty($row["last_updated_date"]) ? date("d.m.Y H:i:s", strtotime($row["last_updated_date"])) : "",
                "ProjectLastUpdatedTimeValue" => !empty($row["last_updated_date"]) ? strtotime($row["last_updated_date"]) : 0
            );
            $row['LastNachbauDate'] = array(
                "ProjectLastNachbauDate" => !empty($lastNachbauDates[$row['ProjectNumber']]) ? date("d.m.Y", strtotime($lastNachbauDates[$row['ProjectNumber']])) : "",
                "ProjectLastNachbauTimeValue" => !empty($lastNachbauDates[$row['ProjectNumber']]) ? strtotime($lastNachbauDates[$row['ProjectNumber']]) : 0
            );

            $data[] = $row;
        }

        Journals::saveJournal("RETURNED | Project List is loaded successfully", PAGE_PROJECTS,DESIGN_LAST_WORKED_PROJECTS_LIST,ACTION_VIEWED,implode(' | ', $_POST),"Last Worked Project List");
        SharedManager::saveLog('log_dtoconfigurator', "Project List is is loaded successfully | ".implode(' | ', $_GET));

        echo(json_encode($data));
    }

    public function getLastNachbauDateOfProjects($projects): array
    {
        $query = "SELECT DATE_FORMAT(MAX(LastUpdated), '%d.%m.%Y %H:%i') AS LastUpdated, FactoryNumber 
                  FROM log_nachbau WHERE FactoryNumber IN(:projects) 
                  GROUP BY FactoryNumber";
        $result = DbManager::fetchPDOQueryData('logs', $query, [':projects' => $projects])['data'] ?? [];

        $dates = [];
        foreach ($result as $row) {
            $dates[$row['FactoryNumber']] = $row['LastUpdated'];
        }

        return $dates;
    }

    public function getProjectInfo(): void {
        $projectNo = $_GET['projectNo'] ?? null;
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Project Info Request | " . $projectNo);
        Journals::saveJournal("PROCESSING | Get Project Info Request | " . $projectNo, PAGE_PROJECTS, DESIGN_PROJECT_INFO, ACTION_PROCESSING, implode(' | ', $_GET), "Project Info");

        if (!$projectNo)
            returnHttpResponse(400, "Project number is required.");

        // mTool Kaydı yok
        $projectInfo = MtoolManager::getProjectDetailsByProjectNos([$projectNo])[0] ?? null;


        if (empty($projectInfo["FactoryNumber"]))
        {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Project With Number " . $projectNo . " Could Not Find in MTool");
            Journals::saveJournal("ERROR | Project With Number " . $projectNo . " Could Not Find in MTool", PAGE_PROJECTS, DESIGN_PROJECT_INFO, ACTION_ERROR, implode(' | ', $_GET), "Get Project Info");
            returnHttpResponse(400, "There is no MTool record for the project: " . $projectNo);
        }

        $accessoryTypicalCode = $this->getAccessoryTypicalOfProject($projectNo);
        $projectInfo['AccessoryTypicalCode'] = $accessoryTypicalCode;

        $isProjectPlanned = $this->checkIfProjectPlannedOrNot($projectNo);
        $projectInfo['isProjectPlanned'] = $isProjectPlanned;

        $isNachbauExists = $this->checkIfNachbauExistsInProject($projectNo);
        $projectInfo['isNachbauExists'] = $isNachbauExists;

//        $isProjectHasNachbauData = $this->checkIfProjectHasNachbauData($projectNo);
//        $projectInfo['isProjectHasNachbauData'] = $isProjectHasNachbauData;

        $projectCharacteristics = $this->getProjectCharacteristics($projectNo, $projectInfo['Product']);
        $projectInfo['ratedVoltage'] = ltrim($projectCharacteristics['rated_voltage'],'0');
        $projectInfo['ratedShortCircuit'] = ltrim($projectCharacteristics['rated_short_circuit'], '0');
        $projectInfo['ratedCurrent'] = ltrim($projectCharacteristics['rated_current'], '0');

        $assemblyStartDate = $this->getProjectAssemblyStartDateOfProject($projectNo);
        $projectInfo['assemblyStartDate'] = $assemblyStartDate;

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Project Info for " . $projectNo . " is Successfully Returned");
        Journals::saveJournal("RETURNED | Project Info for " . $projectNo . " is Successfully Returned", PAGE_PROJECTS, DESIGN_PROJECT_INFO, ACTION_VIEWED, implode(' | ', $_GET), "Get Project Info");
        echo json_encode($projectInfo);
        exit();
    }

    public function getProjectAssemblyStartDateOfProject($projectNo) {

        $query = "SELECT DATE_FORMAT(MIN(productionday), '%d.%m.%Y') AS ProductionDay FROM (
                    SELECT projectNo, productionday
                    FROM assembly_plan_mv 
                    WHERE revisionNr IN (SELECT MAX(revisionNr) FROM assembly_plan_mv WHERE projectNo = :pNo)
                        AND ProjectNo IN (:pNo)
                        ) AS t
                  GROUP BY projectNo";

        return DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo])['data'][0]['ProductionDay'];
    }

    public function checkIfNachbauExistsInProject($projectNo): bool {
        $query = "SELECT FileName FROM log_nachbau WHERE FactoryNumber = :pNo ORDER BY ID DESC LIMIT 1";
        $data = DbManager::fetchPDOQueryData('logs', $query, [':pNo' => $projectNo])['data'] ?? null;

        return !empty($data);
    }

    public function checkIfProjectPlannedOrNot($projectNo): bool {
        $query = "SELECT id FROM assembly_plan_mv WHERE projectNo = :pNo";
        $data = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo])['data'] ?? [];

        return !empty($data);
    }

    function getProjectCharacteristics($projectNo, $panelType): array {
        $query = "SELECT 
                    RatedVoltage AS rated_voltage,
                    SCCurTime AS rated_short_circuit,
                    BusbarCurrent AS rated_current
                FROM OneX_ProjectTechDataEE 
                WHERE FactoryNumber = :pNo";

        $result = DbManager::fetchPDOQueryData('MTool_INKWA', $query, [
            ':pNo' => $projectNo
        ])['data'] ?? [];

        return !empty($result) ? [
            'rated_voltage' => $result[0]['rated_voltage'] ?? '',
            'rated_short_circuit' => $result[0]['rated_short_circuit'] ?? '',
            'rated_current' => $result[0]['rated_current'] ?? ''
        ] : [
            'rated_voltage' => '',
            'rated_short_circuit' => '',
            'rated_current' => ''
        ];
    }


    public function getProjectData(): void {
        ini_set('memory_limit', '512M');
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Retrieving Project Work Data With Following Parameters | " . implode(' | ', $_GET));
        Journals::saveJournal("PROCESSING | Retrieving Project Work Data With Following Parameters | " . implode(' | ', $_GET),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_GET), "Get Project Work Data");

        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $type = $_GET['type'] ?? '';
        $typeNumber = $_GET['typeNumber'] ?? '';
        $dtoNumber = $_GET['dtoNumber'] ?? '';
        $accessoryTypicalCode = $_GET['accessoryTypicalCode'];

        $query = "SELECT id FROM project_work_view WHERE project_number = :pNo AND nachbau_number = :nNo";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];
        if(empty($result))
            returnHttpResponse(204, "Project with Nachbau " . $nachbauNo . " has not been worked before.");

        $conditions = [];
        $parameters = [];

        if ($projectNo) {
            $conditions[] = 'project_number = :pNo';
            $parameters[':pNo'] = $projectNo;
        }
        if ($nachbauNo) {
            $conditions[] = 'nachbau_number = :nNo';
            $parameters[':nNo'] = $nachbauNo;
        }
        if ($type) {
            if ($type === 'Accessories') {
                $accessory_dtos = $this->getAccessoryDtosOfProject($projectNo, $nachbauNo, $accessoryTypicalCode);
                if (!empty($accessory_dtos)) {
                    $acc_dtos = [];

                    foreach ($accessory_dtos as $nachbau_dto) {
                        $dto_number = str_replace(":: ", "", $nachbau_dto);
                        $acc_dtos[] = $dto_number;
                    }

                    $dto_numbers_str = implode(',', array_map(function($dto) {return "'" . $dto . "'";}, $acc_dtos));

                    $conditions[] = "type = :type AND dto_number IN ($dto_numbers_str)";
                } else {
                    $conditions[] = "type = :type AND dto_number IN ('')";
                }

                $parameters[':type'] = $type;
            }
            else if ($dtoNumber) {
                $conditions[] = "(type = 'Typical' OR type = 'Panel' OR type = '' OR type = 'Accessories')";
            }
            else {
                $conditions[] = "(type = :type OR type = '' OR type = 'Accessories')";
                $parameters[':type'] = $type;
            }
        }
        if($typeNumber) {
            if($type === 'Typical') {
                $conditions[] = 'nachbau_typicals LIKE :typical';
                $parameters[':typical'] = '%' . $typeNumber . '%';
            }
            else if($type === 'Panel') {
                $conditions[] = 'nachbau_panels LIKE :panel';
                $parameters[':panel'] = '%'. $typeNumber . '%';
            }
        }
        if ($dtoNumber) {
            $conditions[] = 'nachbau_dto_number = :dto';
            $parameters[':dto'] = $dtoNumber;
        }

        $whereClause = implode(" AND ", $conditions);

        $query = "SELECT * FROM project_work_view WHERE $whereClause AND error_message_id IN (0, 10) AND is_dto_deleted = 0 ORDER BY updated DESC";
        $workData = DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'] ?? [];

        $query = "SELECT * FROM project_work_view WHERE $whereClause AND error_message_id = 1 AND is_dto_deleted = 0 ORDER BY updated DESC";
        $workDataOfNotMatches = DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'] ?? [];

        if (!empty($workDataOfNotMatches))
        {
            foreach ($workDataOfNotMatches as &$work) {
                $parentKmatNos = explode('|', $work['nachbau_kmats']);

                $query = "SELECT DISTINCT work_content, sub_kmat_name 
                          FROM material_kmat_subkmats
                          WHERE parent_kmat IN (:parentKmatNos) OR sub_kmat IN (:parentKmatNos)";
                $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':parentKmatNos' => $parentKmatNos])['data'][0] ?? [];

                $work['parent_kmat_in_nachbau'] = $result['sub_kmat_name'] ? $result['work_content'] . ' / ' . $result['sub_kmat_name'] : $result['work_content'];
            }
        }

        $workList = array_merge($workData, $workDataOfNotMatches);

        $notFoundList = $this->getProjectWorkDataWithErrors($whereClause, $parameters);

        $tkformsNotExist = $this->getTkFormsNotExistInProject($projectNo, $nachbauNo);

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Project Work Data With Following Parameters is Retrieved | ".implode(' | ', $_GET));
        Journals::saveJournal("RETURNED | Project Work Data With Following Parameters is Retrieved | ".implode(' | ', $_GET), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_VIEWED, implode(' | ', $_GET), "Get Project Work Data");

        $data = ['workList' => $workList, 'notDefinedWorkCenter' => $notFoundList['notDefinedWorkCenter'],
                 'notFoundNachbau' => $notFoundList['notFoundNachbau'], 'tkformsNotExist' => $tkformsNotExist];

        ob_start("ob_gzhandler");

        header("Content-Encoding: gzip");
        header("Content-Type: application/json");

        echo (json_encode($data));exit;
    }

    public function updateProjectWork(): void {
        ini_set('memory_limit', '1024M');
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Update Project Work Button Clicked With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Update Project Work Button Clicked With Following Parameters | ".implode(' | ', $_POST),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Update Project Work");

        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized Designer for Project Work | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized Designer for Project Work | ".implode(' | ', $_POST),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Update Project Work");
            returnHttpResponse(400, "Unauthorized user to start working to order.");
        }

        $projectWorks = [];
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $productId = $this->getProductIdOfProject($projectNo);
        $accessoryTypicalNumber = $_POST['accessoryTypicalNumber'];
        $accessoryParentKmat = $_POST['accessoryParentKmat'];

        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT DISTINCT kmat_name FROM nachbau_datas 
              WHERE project_no = :pNo AND nachbau_no=:nNo
              AND kmat_name REGEXP :pattern
              AND (kmat_name LIKE '%::%' OR kmat_name LIKE '%,:%')
              ORDER BY LENGTH(kmat_name)";
        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':pattern' => $nachbauDtoPattern])['data'] ?? [];

        if (empty($result)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | DTO Numbers Not Found In Project | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | DTO Numbers Not Found In Project | ".implode(' | ', $_POST),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Update Project Work");
            returnHttpResponse(400, "There is no DTO Numbers found for this nachbau!");
        }

        $tempDtoNumbers = [];
        $allKmatNames = [];

        foreach ($result as $row) {
            $allKmatNames[] = $row['kmat_name']; // Nachbau DTO map için sakla

            if (str_starts_with($row['kmat_name'], ':: KUKO'))
                $dtoNumberTrimmed = $this->formatKukoDtoNumber($row['kmat_name']);
            else
                $dtoNumberTrimmed = $this->formatDtoNumber($row['kmat_name']);

            $tempDtoNumbers[$dtoNumberTrimmed] = true;
        }

        // planning nachbau_datas'dan dönen bütün DTO'ların trimlenmiş hali. Ex: NX_00108 şeklinde
        $dtoNumbers = array_keys($tempDtoNumbers);

        // nachbau_datas'dan gelen TK'ların veritabanında kayıtlı olanlarıyla olmayanların ayrıldığı bölüm.
        $placeholders = implode(',', array_fill(0, count($dtoNumbers), '?'));
        $query = "SELECT id, dto_number FROM tkforms WHERE dto_number IN ($placeholders) AND deleted IS NULL";
        $tkforms = DbManager::fetchPDOQueryData('dto_configurator', $query, $dtoNumbers)['data'];
        $tkforms_exists = array_column($tkforms, 'dto_number'); // Oluşturulmuş TK lar

        // OPTIMIZASYON: NACHBAU DTO VERILERINI TOPLU ÇEK (N+1 QUERY PROBLEMI ÇÖZÜMÜ)

        $nachbauDtoMap = [];
        foreach ($allKmatNames as $kmatName) {
            foreach ($dtoNumbers as $dto) {
                if (str_contains($kmatName, $dto)) {
                    // En kısa olanı al (ORDER BY LENGTH sayesinde ilk gelen en kısa)
                    if (!isset($nachbauDtoMap[$dto])) {
                        $nachbauDtoMap[$dto] = $kmatName;
                    }
                }
            }
        }

        foreach($dtoNumbers as $dtoNumber) {
            $nachbauDtoNumber = $nachbauDtoMap[$dtoNumber] ?? null;

            $query = "INSERT INTO project_works(project_number, nachbau_number, nachbau_dto_number, dto_number, product_id, tkform_id, tkform_materials_id, 
                work_center_id, release_status, last_updated_by, error_message_id, tk_kmats, common_kmats, nachbau_kmats, nachbau_panels, nachbau_typicals)";

            $parameters[] = [ $projectNo, $nachbauNo, $nachbauDtoNumber, $dtoNumber, $productId, NULL, NULL, NULL, NULL, SharedManager::$fullname, 9, NULL,NULL,NULL,NULL,NULL ];
        }
        DbManager::fetchInsert('dto_configurator', $query, $parameters);

        if (empty($tkforms_exists)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | No created TK Form found. | " . implode(' | ', $_POST));
            Journals::saveJournal("ERROR | No created TK Form found. | " . implode(' | ', $_POST),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Update Project Work");
            returnHttpResponse(400, "No created TK Form found.");
        }

        $query = "SELECT id, tkform_id, dto_number, material_added_starts_by, material_added_number, material_added_work_center_id, affected_dto_numbers, type,
                  material_deleted_starts_by, material_deleted_number, material_deleted_work_center_id, tk_work_center_id, tk_kmats, operation, effective
                  FROM tkform_materials_view
                  WHERE dto_number IN (:dto_numbers)";

        $tkform_materials = DbManager::fetchPDOQueryData('dto_configurator', $query, [':dto_numbers' => $tkforms_exists])['data'];

        if (empty($tkform_materials))
        {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | There are no TK Form Materials for related DTO's in the project | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | There are no TK Form Materials for related DTO's in the project | ".implode(' | ', $_POST),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Update Project Work");
            returnHttpResponse(400, "There are no TK Form Materials for related DTO's in the project " . $projectNo);
        }

        // OPTIMIZASYON: AFFECTED DTO VERİLERİNİ TOPLU ÇEK
        // Tüm affected DTO'ları topla
        $allAffectedDtos = [];
        foreach($tkform_materials as $row) {
            if (!empty($row['affected_dto_numbers'])) {
                $dtos = array_filter(explode('|', $row['affected_dto_numbers']));
                $allAffectedDtos = array_merge($allAffectedDtos, array_map('trim', $dtos));
            }
        }
        $allAffectedDtos = array_unique($allAffectedDtos);

        // Affected DTO cache'i
        $affectedDtoCache = [];

        if (!empty($allAffectedDtos)) {
            // Önce hangi affected DTO'ların projede var olduğunu kontrol et
            $likeConditions = implode(' OR ', array_fill(0, count($allAffectedDtos), 'kmat_name LIKE ?'));
            $params = [$projectNo, $nachbauNo];
            foreach ($allAffectedDtos as $dto) {
                $params[] = '%' . $dto . '%';
            }

            $checkQuery = "SELECT DISTINCT kmat_name
                      FROM nachbau_datas 
                      WHERE project_no = ? AND nachbau_no = ? 
                      AND ($likeConditions)";

            $existingDtos = DbManager::fetchPDOQueryData('planning', $checkQuery, $params)['data'] ?? [];

            // Hangi affected DTO'lar gerçekten var?
            $validAffectedDtos = [];
            foreach ($existingDtos as $row) {
                foreach ($allAffectedDtos as $dto) {
                    if (str_contains($row['kmat_name'], $dto) && !in_array($dto, $validAffectedDtos)) {
                        $validAffectedDtos[] = $dto;
                    }
                }
            }

            // Şimdi tüm affected DTO'ların typical ve panel verilerini çek
            if (!empty($validAffectedDtos)) {
                $likeConditions = implode(' OR ', array_fill(0, count($validAffectedDtos), 'kmat_name LIKE ?'));
                $params = [$projectNo, $nachbauNo];
                foreach ($validAffectedDtos as $dto) {
                    $params[] = '%' . $dto . '%';
                }

                $query = "SELECT kmat_name, typical_no, ortz_kz
                      FROM nachbau_datas 
                      WHERE project_no = ? AND nachbau_no = ? 
                      AND ($likeConditions)
                      AND ortz_kz <> ''
                      GROUP BY kmat_name, typical_no, ortz_kz";

                $affectedDtoData = DbManager::fetchPDOQueryData('planning', $query, $params)['data'] ?? [];

                // Cache'e dönüştür
                foreach ($affectedDtoData as $row) {
                    foreach ($validAffectedDtos as $dto) {
                        if (strpos($row['kmat_name'], $dto) !== false) {
                            if (!isset($affectedDtoCache[$dto])) {
                                $affectedDtoCache[$dto] = ['typicals' => [], 'panels' => []];
                            }
                            $affectedDtoCache[$dto]['typicals'][] = $row['typical_no'];
                            $affectedDtoCache[$dto]['panels'][] = $row['ortz_kz'];
                        }
                    }
                }

                // Her DTO için unique yap
                foreach ($affectedDtoCache as $dto => &$data) {
                    $data['typicals'] = array_unique($data['typicals']);
                    $data['panels'] = array_unique($data['panels']);
                }
            }
        }


        // OPTIMIZASYON 4: MATERIAL DELETED VERİLERİNİ TOPLU ÇEK
        $allMaterialDeletedNumbers = array_unique(array_column($tkform_materials, 'material_deleted_number'));
        $allMaterialDeletedNumbers = array_filter($allMaterialDeletedNumbers);

        $materialDeletedCache = [];

        if (!empty($allMaterialDeletedNumbers)) {
            $likeConditions = implode(' OR ', array_fill(0, count($allMaterialDeletedNumbers), 'kmat LIKE ?'));
            $params = [$projectNo, $nachbauNo];
            foreach ($allMaterialDeletedNumbers as $num) {
                $params[] = '%' . $num . '%';
            }

            $query = "SELECT kmat, typical_no, ortz_kz 
                  FROM nachbau_datas 
                  WHERE project_no = ? AND nachbau_no = ? 
                  AND ($likeConditions)
                  AND ortz_kz <> ''
                  GROUP BY kmat, typical_no, ortz_kz";

            $materialDeletedData = DbManager::fetchPDOQueryData('planning', $query, $params)['data'] ?? [];

            // Cache oluştur
            foreach ($materialDeletedData as $row) {
                foreach ($allMaterialDeletedNumbers as $num) {
                    if (str_contains($row['kmat'], $num)) {
                        if (!isset($materialDeletedCache[$num])) {
                            $materialDeletedCache[$num] = ['typicals' => [], 'panels' => []];
                        }
                        $materialDeletedCache[$num]['typicals'][] = $row['typical_no'];
                        $materialDeletedCache[$num]['panels'][] = $row['ortz_kz'];
                    }
                }
            }

            // Her material için unique yap
            foreach ($materialDeletedCache as $num => &$data) {
                $data['typicals'] = array_unique($data['typicals']);
                $data['panels'] = array_unique($data['panels']);
            }
        }

        $query = "INSERT INTO project_works(project_number, nachbau_number, nachbau_dto_number, dto_number, product_id, tkform_id, tkform_materials_id, work_center_id,
                  release_status, last_updated_by, error_message_id, tk_kmats, common_kmats, nachbau_kmats, nachbau_panels, nachbau_typicals) VALUES ";

        $index = 0;
        foreach($tkform_materials as $row) {
            $nachbauResultItems = $this->checkMaterialStatusInNachbau($projectNo, $nachbauNo, $row, $accessoryTypicalNumber, $accessoryParentKmat);
            $nachbauDtoNumber = $nachbauDtoMap[$row['dto_number']] ?? null;

            // OPTIMIZASYON 3 & 4 KULLANIMI: Cache'den typical ve panel verilerini al
            if (!empty($row['affected_dto_numbers'])) {
                $affectedDtoNumbers = array_filter(array_map('trim', explode('|', $row['affected_dto_numbers'])));

                // Material deleted verileri
                $materialDeletedTypicals = [];
                $materialDeletedPanels = [];
                if (!empty($row['material_deleted_number']) && isset($materialDeletedCache[$row['material_deleted_number']])) {
                    $materialDeletedTypicals = $materialDeletedCache[$row['material_deleted_number']]['typicals'];
                    $materialDeletedPanels = $materialDeletedCache[$row['material_deleted_number']]['panels'];
                }

                // Common typicals ve panels hesapla
                $commonTypicals = null;
                $commonPanels = null;

                foreach ($affectedDtoNumbers as $affectedDto) {
                    if (isset($affectedDtoCache[$affectedDto])) {
                        $dtoTypicals = $affectedDtoCache[$affectedDto]['typicals'];
                        $dtoPanels = $affectedDtoCache[$affectedDto]['panels'];

                        if (!empty($dtoTypicals)) {
                            $commonTypicals = ($commonTypicals === null) ? $dtoTypicals : array_intersect($commonTypicals, $dtoTypicals);
                        } else {
                            $commonTypicals = [];
                        }

                        if (!empty($dtoPanels)) {
                            $commonPanels = ($commonPanels === null) ? $dtoPanels : array_intersect($commonPanels, $dtoPanels);
                        } else {
                            $commonPanels = [];
                        }
                    }
                }

                // Final typicals ve panels
                $finalTypicals = [];
                $finalPanels = [];

                if (!empty($commonTypicals) && !empty($materialDeletedTypicals)) {
                    $finalTypicals = array_intersect($materialDeletedTypicals, $commonTypicals);
                    $finalTypicals = array_unique($finalTypicals);
                    sort($finalTypicals);
                }

                if (!empty($commonPanels) && !empty($materialDeletedPanels)) {
                    $finalPanels = array_intersect($materialDeletedPanels, $commonPanels);
                    $finalPanels = array_unique($finalPanels);
                    sort($finalPanels);
                }

                $nachbauTypicals = implode('|', $finalTypicals);
                $nachbauPanels = implode('|', $finalPanels);
            } else {
                $nachbauTypicals = $nachbauResultItems['nachbau_typicals'];
                $nachbauPanels = $nachbauResultItems['nachbau_panels'];
            }

            $projectNumberKey = ":project_number$index";
            $nachbauNumberKey = ":nachbau_number$index";
            $nachbauDtoNumberKey = ":nachbau_dto_number$index";
            $dtoNumberKey = ":dto_number$index";
            $productIdKey = ":product_id$index";
            $tkformIdKey = ":tkform_id$index";
            $tkformMaterialsIdKey = ":tkform_materials_id$index";
            $workCenterIdKey = ":work_center_id$index";
            $tkKmatsKey = ":tk_kmats$index";
            $commonKmatsKey = ":common_kmats$index";
            $nachbauKmatsKey = ":nachbau_kmats$index";
            $releaseStatusKey = ":release_status$index";
            $lastUpdatedByKey = ":last_updated_by$index";
            $errorMessageKey = ":error_message_id$index";
            $nachbauPanelsKey = ":nachbau_panels$index";
            $nachbauTypicalsKey = ":nachbau_typicals$index";

            $projectWorks[] = [
                $projectNumberKey => $projectNo,
                $nachbauNumberKey => $nachbauNo,
                $nachbauDtoNumberKey => $nachbauDtoNumber,
                $dtoNumberKey => $row['dto_number'],
                $productIdKey => $productId,
                $tkformIdKey => $row['tkform_id'],
                $tkformMaterialsIdKey => $row['id'],
                $workCenterIdKey => $row['tk_work_center_id'],
                $tkKmatsKey => $row['tk_kmats'],
                $commonKmatsKey => $nachbauResultItems['common_kmats'],
                $nachbauKmatsKey => $nachbauResultItems['nachbau_kmats'],
                $releaseStatusKey => 'initial',
                $lastUpdatedByKey => SharedManager::$fullname,
                $errorMessageKey => intval($nachbauResultItems['error_message_id']),
                $nachbauPanelsKey => $nachbauPanels,
                $nachbauTypicalsKey => $nachbauTypicals,
            ];

            $query .= "($projectNumberKey, $nachbauNumberKey, $nachbauDtoNumberKey, $dtoNumberKey, $productIdKey, $tkformIdKey, $tkformMaterialsIdKey, $workCenterIdKey, $releaseStatusKey,
                        $lastUpdatedByKey, $errorMessageKey, $tkKmatsKey, $commonKmatsKey, $nachbauKmatsKey, $nachbauPanelsKey, $nachbauTypicalsKey),";

            $index++;
        }

        $query = rtrim($query, ',');
        DbManager::fetchPDOQuery('dto_configurator', $query, $projectWorks, [], false);

        // Proje güncellenme tarihini updatele.
        $query = "UPDATE projects SET working_user=:whom, project_status = 1, last_updated_by=:whom, last_updated_date=:uptDate WHERE project_number=:pNo AND nachbau_number=:nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':whom' => SharedManager::$fullname, ':uptDate' => (new DateTime())->format('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Project Work Data initialized successfully | " . implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Project Work Data initialized successfully | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_CREATED, implode(' | ', $_POST), "Update Project Work");
    }

    public function getProjectWorkDataWithErrors($whereClause, $parameters): array {
        $errorMsgOrStatement = "error_message_id IN (2, 3, 4, 5, 6, 7, 11, 12)";
        $query = "SELECT * FROM project_work_view WHERE $whereClause AND ($errorMsgOrStatement)";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'];

        $data = ['notFoundNachbau' => [], 'notDefinedWorkCenter' => []];
        foreach ($result as $row) {
            if ($row['error_message_id'] === '3') {
                $nachbauKmats = explode('|', $row['nachbau_kmats']);
                $nachbauWC = $this->getWorkCenterByNachbauKmat($nachbauKmats);

                $row['nachbau_work_center'] = $nachbauWC['work_center'];
                $row['nachbau_work_content'] = $nachbauWC['work_content'];

                $data['notFoundNachbau'][] = $row;
            }
            if($row['error_message_id'] === '2' || $row['error_message_id']=== '5' || $row['error_message_id']=== '11' || $row['error_message_id']=== '12') {
                $data['notFoundNachbau'][] = $row;
            }
            if(empty($row['tk_work_center_id']) || $row['error_message_id'] === '4' || $row['error_message_id'] === '6' || $row['error_message_id'] === '7') {
                $data['notDefinedWorkCenter'][] = $row;
            }
        }
        return $data;
    }

    public function getWorkCenterByNachbauKmat($nachbauKmatArray): array {

        foreach ($nachbauKmatArray as $kmat) {
            $query = "SELECT work_center, work_content FROM material_kmat_subkmats 
                      WHERE parent_kmat=:kmat OR sub_kmat=:kmat
                      GROUP BY work_center";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':kmat' => $kmat])['data'][0] ?? [];

            if (!empty($result)){
                return $result;
            }
        }

        return [];
    }

    public function getTkFormsNotExistInProject($projectNo, $nachbauNo): array {
        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='product_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        // Step 1: Fetch relevant `kmat_name` and `description` values from `nachbau_datas`
        $query = "SELECT DISTINCT kmat_name, description
              FROM nachbau_datas
              WHERE project_no = :pNo 
              AND nachbau_no = :nNo
              AND kmat_name REGEXP :pattern
              AND (kmat_name LIKE '%::%' OR kmat_name LIKE '%,:%')";
        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':pattern' => $nachbauDtoPattern])['data'];

        if (empty($result)) {
            return []; // No results, return early
        }

        // Step 2: Process and format DTO numbers with descriptions
        $dtoData = [];
        foreach ($result as $row) {
            $kmatName = $row['kmat_name'];
            $description = $row['description'];

            if (str_starts_with($kmatName, ':: KUKO'))
                $dtoNumber = $this->formatKukoDtoNumber($kmatName);
            else
                $dtoNumber = $this->formatDtoNumber($kmatName);

            if (!$description)
                $description = $this->getNachbauDescriptionByDtoNumber($dtoNumber, $projectNo, $nachbauNo, true);

            $dtoData[$dtoNumber] = [
                'dto_number' => $dtoNumber,
                'description' => $description
            ];
        }

        // Remove duplicates by DTO number
        $dtoData = array_values($dtoData);


        // Step 3: Check which DTO numbers do not have TK forms
        if (empty($dtoData)) {
            return []; // No DTO numbers to check
        }

        $dtoNumbers = array_column($dtoData, 'dto_number');
        $placeholders = implode(',', array_fill(0, count($dtoNumbers), '?'));

        // Query existing TK forms for the given DTO numbers
        $query = "SELECT DISTINCT dto_number
              FROM tkforms
              WHERE dto_number IN ($placeholders) 
              AND deleted IS NULL";
        $tkforms = DbManager::fetchPDOQueryData('dto_configurator', $query, $dtoNumbers)['data'];

        $existingTkForms = array_column($tkforms, 'dto_number');

        // Step 4: Calculate non-existing TK forms with descriptions
        $tkformsNotExists = array_filter($dtoData, function ($dto) use ($existingTkForms) {
            return !in_array($dto['dto_number'], $existingTkForms);
        });

        return array_values($tkformsNotExists); // Return as a zero-indexed array
    }

    public function removeAllProjectWorkData(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Removing All Released Project Work Data (" . $_POST['nachbauNo'] . ") With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Removing All Released Project Work Data (" . $_POST['nachbauNo'] . ") With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Remove Project Work Data");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];

        $query = "SELECT id FROM project_work_view WHERE project_number = :pNo AND nachbau_number = :nNo";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];
        $projectWorkIds = array_column($result, 'id');

        if(!empty($projectWorkIds)) {
            //Bom'a eklenenleri sil.
            $query = "UPDATE bom_change SET active = 0, deleted_user = :delUser, deleted = :deletedAt WHERE project_work_id IN (:ids)";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $projectWorkIds]);

            //Çalışmadakileri sil.
            $query = "UPDATE project_works SET deleted = :deletedAt, deleted_user = :delUser WHERE id IN (:ids)";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $projectWorkIds]);

            //Spare DTO çalışmalarını temizle.
            $query = "UPDATE project_works_spare SET deleted = :deletedAt, deleted_user = :delUser WHERE project_number = :pNo AND nachbau_number = :nNo";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

            //Extension DTO çalışmalarını temizle.
            $query = "UPDATE project_works_extensions SET deleted = :deletedAt, deleted_user = :delUser WHERE project_number = :pNo AND nachbau_number = :nNo";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

            //Aktarım hatası değişikliklerini temizle.
            $query = "UPDATE project_works_nachbau_errors SET deleted = :deletedAt, deleted_user = :delUser WHERE project_number = :pNo AND nachbau_number = :nNo";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

            //MIN PRI çalışması varsa temizle.
            $query = "UPDATE project_works_minus_price SET wont_be_produced = 0 WHERE project_number = :pNo AND nachbau_number = :nNo";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':pNo' => $projectNo, ':nNo' => $nachbauNo]);

            //Interchange çalışması varsa temizle.
            $query = "UPDATE project_works_interchange SET deleted = :deletedAt, deleted_user = :delUser WHERE project_number = :pNo AND nachbau_number = :nNo";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

            $query = "SELECT id, transferred_from_nachbau FROM projects WHERE project_number = :pNo AND nachbau_number = :nNo";
            $project = DbManager::fetchPDOQueryData('dto_configurator', $query,[':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'][0];

            // Eğer bu bir transfer aktarımıysa, skipped workslerini temizle.
            if (!empty($project['transferred_from_nachbau'])) {
                $query = "UPDATE nachbau_transfer_skipped_works SET active = 0 WHERE project_number = :pNo AND transfer_to_nachbau = :nNo";
                DbManager::fetchPDOQuery('dto_configurator', $query,[':pNo' => $projectNo, ':nNo' => $nachbauNo]);
            }

            // Proje güncellenme tarihini updatele.
            $query = "UPDATE projects SET last_updated_by=:whom, last_updated_date=:uptDate, working_user = NULL, project_status = 1, transferred_from_nachbau = NULL, revision_start_date = NULL WHERE id = :projectId";
            DbManager::fetchPDOQuery('dto_configurator', $query, [':whom' => SharedManager::$fullname, ':uptDate' => (new DateTime())->format('Y-m-d H:i:s'), ':projectId' => $project['id']]);
        }

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Released All Nachbau Data (" . $nachbauNo . ") Is Removed");
        Journals::saveJournal("DELETED | Released All Nachbau Data (" . $nachbauNo . ") Is Removed", PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_DELETED, implode(' | ', $_POST), "Remove Project Work");
    }

    public function updateNotDefinedWorkCenters(): void {
        ini_set('memory_limit', '512M');

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Update Not Defined Work Centers Request");
        Journals::saveJournal("PROCESSING | Update All Not Defined Work Centers Request", PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Update Not Defined Work Centers");

        $projectWorkUpdates = $_POST['projectWorkUpdates'];
        $accessoryParentKmat = $_POST['accessoryParentKmat'];
        $accessoryTypicalNumber = $_POST['accessoryTypicalNumber'];

        foreach($projectWorkUpdates as $row) {
            $query = "SELECT material_added_id, material_deleted_id FROM project_work_view WHERE id = :id";
            $projectWorkData = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $row['rowId']])['data'][0] ?? [];

            //1. update material possible kmats  ':material_kmats' => $this->getMaterialPossibleKmats($workCenterId, $subWorkCenterName),
            $materialKmats = $this->getMaterialPossibleKmats($row['work_center_id'], $row['sub_kmat_name']);
            $query = "UPDATE materials SET work_center_id = :wcId, material_kmats = :materialKmats, updated_by = :upt, updated = :updated
                      WHERE id IN (:materialAddedId, :materialDeletedId)";
            $parameters = [
                ':wcId' => $row['work_center_id'],
                ':materialKmats' => $materialKmats,
                ':upt' => SharedManager::$fullname,
                ':updated' => (new DateTime())->format('Y-m-d H:i:s'),
                ':materialAddedId' => $projectWorkData['material_added_id'],
                ':materialDeletedId' => $projectWorkData['material_deleted_id']
            ];
            DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'];

            // 2. Malzemeyi güncelledikten sonra, bu malzemenin hangi TK'larda geçtiğini sorgula ve id'yi getir.
            $query = "SELECT id FROM tkform_materials_view WHERE material_deleted_id = :materialDeletedId";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':materialDeletedId' => $projectWorkData['material_deleted_id']])['data'] ?? [];
            $deletedIds = array_column($result, 'id');

            $query = "SELECT id FROM tkform_materials_view WHERE material_added_id = :materialAddedId AND material_deleted_number IS NULL";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':materialAddedId' => $projectWorkData['material_added_id']])['data'] ?? [];
            $addedIds = array_column($result, 'id');

            $tkformMaterialsIds = array_unique(array_merge($deletedIds, $addedIds));
            if(!empty($tkformMaterialsIds)) {
                // 3. Geçtiği TK Form materyallerinin idlerini gönderip, bu malzemeye daha önceden çalışılan projelerin id'lerini arrayde toplarız,
                //    çünkü project_works ve bom changede de istasyonun değişmesi gerekiyor.
                $query = "SELECT id, project_number, nachbau_number, dto_number, material_added_starts_by, type, material_added_number, material_deleted_starts_by, material_deleted_number, product_id, operation, tk_kmats, nachbau_kmats, effective, affected_dto_numbers
                          FROM project_work_view
                          WHERE tkform_materials_id IN (:ids) AND release_status = 'initial'";
                $projectWorksResult = DbManager::fetchPDOQuery('dto_configurator', $query, [':ids' =>  $tkformMaterialsIds])['data'] ?? [];

                // 4. Malzemenin güncellendiği TK daha önce bir çalışmada bulunduysa if'e girsin.
                if (!empty($projectWorksResult))
                    $this->updateMaterialStatusOfProjectWork($projectWorksResult, $row['work_center_id'], $accessoryTypicalNumber, $accessoryParentKmat);
            }

            SharedManager::saveLog('log_dtoconfigurator',"UPDATED | Update Not Defined Work Centers Request Successful");
            Journals::saveJournal("UPDATED | Update Not Defined Work Centers Request Successful", PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_CREATED, implode(' | ', $_POST), "Update Not Defined Work Centers");
        }
    }

    public function removeTypeNumberFromWork(): void
    {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Removing Project Work Release Item From Bom Change With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Removing Project Work Release Item From Bom Change With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Remove Project Work Item");

        $id = $_POST['id'];
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $deletedItem = $_POST['releaseItem'];
        $currentlyWorkingUser = $_POST['currentlyWorkingUser'];

        if($currentlyWorkingUser['name'] !== SharedManager::$fullname || in_array($currentlyWorkingUser['projectStatusId'], ['3', '5'])) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Operation failed. User not authorized. | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Operation failed. User not authorized. | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Remove All Project Work Items");
            returnHttpResponse(404, 'Operation failed. User not authorized.!');
        }


        $query = "SELECT id, release_items, accessory_release_items FROM project_work_view WHERE id = :id";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $id])['data'][0] ?? [];

        $releaseItemsArr = explode('|', $result['release_items']);
        $accessoryReleaseItemsArr = explode('|', $result['accessory_release_items']);

        // Search release_items array
        $releaseItemKey = array_search($deletedItem, $releaseItemsArr, true);
        $itemFoundInReleaseItems = $releaseItemKey !== false;

        // Search accessory_release_items array
        $accessoryItemKey = array_search($deletedItem, $accessoryReleaseItemsArr, true);
        $itemFoundInAccessoryReleaseItems = $accessoryItemKey !== false;

        if ($itemFoundInReleaseItems) {
            unset($releaseItemsArr[$releaseItemKey]);
            $releaseItemsArr = array_values($releaseItemsArr);

            $query = "UPDATE project_works SET release_items = :items, last_updated_by = :uby WHERE id = :id";
            DbManager::fetchPDOQuery('dto_configurator', $query, [
                ':id' => $id,
                ':uby' => SharedManager::$fullname,
                ':items' => implode('|', $releaseItemsArr)
            ]);

            if (empty($releaseItemsArr)) {
                $query = "UPDATE project_works SET release_status = 'initial', last_updated_by = :uby WHERE id = :id AND (release_items = '' OR release_items IS NULL)";
                DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id, ':uby' => SharedManager::$fullname]);
            }

            $query = "UPDATE bom_change SET active = 0 WHERE project_work_id = :id AND release_item = :release_item";
            DbManager::fetchPDOQuery('dto_configurator', $query, [
                ':id' => $id,
                ':release_item' => $deletedItem
            ]);
        } elseif ($itemFoundInAccessoryReleaseItems) {
            // Remove item from accessory_release_items
            unset($accessoryReleaseItemsArr[$accessoryItemKey]);

            $accessoryReleaseItemsArr = array_values($accessoryReleaseItemsArr);

            if (empty($accessoryReleaseItemsArr))
                $accessoryReleaseType = 0;
            else
                $accessoryReleaseType = 1;

            $items = implode('|', $accessoryReleaseItemsArr);
            $query = "UPDATE project_works SET accessory_release_items = :items, accessory_release_type = :accReleaseType, last_updated_by = :uby WHERE id = :id";
            DbManager::fetchPDOQuery('dto_configurator', $query, [
                ':id' => $id,
                ':accReleaseType' => $accessoryReleaseType,
                ':uby' => SharedManager::$fullname,
                ':items' => $items
            ]);

            if (empty($accessoryReleaseItemsArr)) {
                $query = "UPDATE project_works SET release_status = 'initial', accessory_release_type = :accReleaseType, last_updated_by = :uby
                          WHERE id = :id AND (accessory_release_items = '' OR accessory_release_items IS NULL)";
                DbManager::fetchPDOQuery('dto_configurator', $query, [
                    ':id' => $id,
                    ':accReleaseType' => $accessoryReleaseType,
                    ':uby' => SharedManager::$fullname
                ]);
            }

            $query = "UPDATE bom_change SET active = 0, deleted_user = :delUser, deleted = :deletedAt
                      WHERE project_work_id = :id AND release_item = :release_item";
            DbManager::fetchPDOQuery('dto_configurator', $query, [
                ':delUser' => SharedManager::$fullname,
                ':deletedAt' => date('Y-m-d H:i:s'),
                ':id' => $id,
                ':release_item' => $deletedItem
            ]);
        }

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Released Item " . $deletedItem . " is Removed | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Released Item " . $deletedItem . " is Removed | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_DELETED, implode(' | ', $_POST), "Remove Project Work Item");

        // Proje güncellenme tarihini updatele.
        $query = "UPDATE projects SET last_updated_by=:whom, last_updated_date=:uptDate WHERE project_number=:pNo AND nachbau_number=:nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':whom' => SharedManager::$fullname, ':uptDate' => (new DateTime())->format('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

        // Updated Result Data
        $query = "SELECT id, release_items, accessory_release_items FROM project_work_view WHERE id = :id";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $id])['data'][0] ?? [];;
        echo(json_encode($data));
    }

    public function removeAllSelectionsFromProject(): void
    {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Removing All Selections From Project Works With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Removing All Selections From Project Works With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Remove All Project Work Items");

        $data = $_POST['data'];
        $currentlyWorkingUser = $_POST['currentlyWorkingUser'];

        if($currentlyWorkingUser['name'] !== SharedManager::$fullname || in_array($currentlyWorkingUser['projectStatusId'], ['3', '5'])) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Operation failed. User not authorized. | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Operation failed. User not authorized. | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Remove All Project Work Items");
            returnHttpResponse(404, 'Operation failed. User not authorized.!');
        }

        //Set bom change items status to 0
        $query = "UPDATE bom_change SET active = 0, deleted_user = :delUser, deleted = :deletedAt WHERE project_work_id = :projectWorkId AND active = 1";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':projectWorkId'=>$data['id']])['data'];

        //Set released items of released projects to empty
        $query = "UPDATE project_works SET release_status = 'initial', release_items = '', accessory_release_items = '', accessory_release_type = 0 WHERE id = :id";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':id'=>$data['id']])['data'];

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Project Work Selections Are Removed With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Project Work Selections Are Removed With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_DELETED, implode(' | ', $_POST), "Remove All Project Work Items");

        if ($data['material_added_is_device'] === '1') {
            $nachbauResultItems = $this->checkMaterialStatusInNachbau($data['project_number'],$data['nachbau_number'], $data, $accessoryTypicalNumber ?? '', $accessoryParentKmat ?? '');

            $query = "UPDATE project_works SET 
                        error_message_id = :errMsgId, common_kmats = :commonKmats, nachbau_kmats = :nachbauKmats, nachbau_panels = :nachbauPanels, 
                        nachbau_typicals = :nachbauTypicals, added_on_work_center_id = NULL
                      WHERE id = :id";

            DbManager::fetchPDOQuery('dto_configurator', $query,[
                ':id' => $data['id'],
                ':errMsgId' => $nachbauResultItems['error_message_id'],
                ':commonKmats' => $nachbauResultItems['common_kmats'],
                ':nachbauKmats' => $nachbauResultItems['nachbau_kmats'],
                ':nachbauPanels' => $nachbauResultItems['nachbau_panels'],
                ':nachbauTypicals' => $nachbauResultItems['nachbau_typicals']
            ]);
        }

        $query = "SELECT id, release_items, accessory_release_items, common_kmats, nachbau_kmats, nachbau_panels, nachbau_typicals, added_on_work_center_id FROM project_work_view WHERE id = :id";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $data['id']])['data'][0] ?? [];

        echo(json_encode($data));
        exit();
    }

    public function addMaterialToProject(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Adding Material To Project Work Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Adding Material To Project Work Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Add Material To Project Work");

        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized Designer for Add Material | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized Designer for Add Material | ".implode(' | ', $_POST),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Add Material To Project Work");
            returnHttpResponse(400, "Unauthorized user to add material list.");
        }

        $data = $_POST['data'];
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $projectWorkId = $data['id'];
        $listType = $_POST['listType'];
        $releaseQuantity = $_POST['releaseQuantity'];
        $operation = $data['operation'];
        $accessoryTypicalNumber = $_POST['accessoryTypicalCode'];
        $accessoryParentKmat = $_POST['accessoryParentKmat'];
        $isAccessoryTypicalChecked = $_POST['isAccessoryTypicalChecked'] === 'true';
        $selectedAddedOnWorkCenterId = $_POST['selectedAddedOnWorkCenterId'];
        $currentlyWorkingUser = $_POST['currentlyWorkingUser'];
        $selectedItems = $_POST['selectedItems'];
        $selectedItemsStr = implode('|', $_POST['selectedItems']);

        // Biri dev toolsdan disabled classı kaldırıp butonu triggerlarsa diye extra kontrol
        if($currentlyWorkingUser['name'] !== SharedManager::$fullname || in_array($currentlyWorkingUser['projectStatusId'], ['3', '5'])) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Adding Material To Project Work Failed. User not authorized. | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Adding Material To Project Work Failed. User not authorized. | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Add Material To Project Work");
            returnHttpResponse(404, 'Logged user and working user of project are not the same!');
        }

        $this->checkIfMaterialsSapDefinedApiRequest($operation, $data);

        $query = "SELECT release_items, accessory_release_items, accessory_release_type, affected_dto_numbers, common_kmats, material_added_number, material_added_is_device, material_deleted_starts_by, material_deleted_number FROM project_work_view WHERE id=:id";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $projectWorkId])['data'][0] ?? [];
        $rItems = $result['release_items'];
        $accReleaseItems = $result['accessory_release_items'];


        if ($listType === 'Accessories') {
            $accessoryReleaseType = intval($result['accessory_release_type']);
            $accessoryReleaseItemsArr = !empty($accReleaseItems) ? explode('|', $accReleaseItems) : [];

            if ($accessoryReleaseType === 0)
                $accessoryReleaseType = $isAccessoryTypicalChecked ? 2 : 1;
            else if ($accessoryReleaseType === 1 && $isAccessoryTypicalChecked) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | This item cannot be added under {$accessoryTypicalNumber} because it has already been added as an accessory under the typicals {$accReleaseItems}! | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | This item cannot be added under {$accessoryTypicalNumber} because it has already been added as an accessory under the typicals {$accReleaseItems}! | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Add Material To Project Work");
                returnHttpResponse(400, "This item cannot be added under {$accessoryTypicalNumber} because it has already been added as an accessory under the typicals {$accReleaseItems}!");
            }
            else if ($accessoryReleaseType === 2 && !$isAccessoryTypicalChecked) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | This item cannot be added under {$selectedItemsStr} because it has already been added under {$accessoryTypicalNumber}! | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | This item cannot be added under {$selectedItemsStr} because it has already been added under {$accessoryTypicalNumber}! | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Add Material To Project Work");
                returnHttpResponse(400, "This item cannot be added under {$selectedItemsStr} because it has already been added under {$accessoryTypicalNumber}!");
            }

            if ($accessoryReleaseType !== 1 && $operation !== 'add') {
                $isMaterialDeletedNumberWithTypicalExistsInNachbau = $this->checkIfMaterialDeletedNumberWithTypicalExistsInNachbau($projectNo, $nachbauNo, $result['material_deleted_starts_by'], $result['material_deleted_number'], $accessoryTypicalNumber);
                if(!$isMaterialDeletedNumberWithTypicalExistsInNachbau)
                    returnHttpResponse(400, "Deleted material number is not found under the typical ${$accessoryTypicalNumber} in nachbau.");
            }

            if ($selectedItemsStr === $accessoryTypicalNumber) {
                if (in_array($accessoryTypicalNumber, $accessoryReleaseItemsArr)) {
                    SharedManager::saveLog('log_dtoconfigurator',"ERROR | Adding Material To Project Work Failed. This item has already been added under ' . $accessoryTypicalNumber . ' accessory. | ".implode(' | ', $_POST));
                    Journals::saveJournal("ERROR | Adding Material To Project Work Failed. This item has already been added under ' . $accessoryTypicalNumber . ' accessory. | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Add Material To Project Work");
                    returnHttpResponse(400, 'This item has already been added under ' . $accessoryTypicalNumber . ' accessory!');
                }
            }

            if(!empty($accessoryReleaseItemsArr))
                $accReleaseItems = array_merge($accessoryReleaseItemsArr, $selectedItems);
            else
                $accReleaseItems = $selectedItems;

             // OPTIMIZASYON 1: ACCESSORY REPLACE KONTROLÜ BATCH HALİNE GETİRİLDİ
            if ($operation === 'replace') {
                // Batch query: Tüm accReleaseItems için kontrol
                $query = "SELECT release_items, accessory_release_items 
                      FROM project_work_view 
                      WHERE project_number = :pNo AND nachbau_number = :nNo 
                      AND material_deleted_number = :materialNo AND id != :id";
                $alreadyDeletedResult = DbManager::fetchPDOQueryData('dto_configurator', $query, [
                    ':pNo' => $projectNo,
                    ':nNo' => $nachbauNo,
                    ':materialNo' => $result['material_deleted_number'],
                    ':id' => $projectWorkId
                ])['data'][0] ?? [];

                if (!empty($alreadyDeletedResult)) {
                    $existingReleaseItems = !empty($alreadyDeletedResult['release_items']) ? explode('|', $alreadyDeletedResult['release_items']) : [];
                    $existingAccReleaseItems = !empty($alreadyDeletedResult['accessory_release_items']) ? explode('|', $alreadyDeletedResult['accessory_release_items']) : [];
                    $allExistingItems = array_merge($existingReleaseItems, $existingAccReleaseItems);

                    foreach ($accReleaseItems as $item) {
                        if (in_array($item, $allExistingItems)) {
                            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Deleted material number has already been deleted by another list at ' . $item . '! | ".implode(' | ', $_POST));
                            Journals::saveJournal("ERROR | Deleted material number has already been deleted by another list at ' . $item . '! | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Add Material To Project Work");
                            returnHttpResponse(400, "Deleted material number has already been deleted by another list at ' . $item . '!");
                        }
                    }
                }
            }
            // OPTIMIZASYON 1 SONU

            $accReleaseItems = implode('|', $accReleaseItems);

            $query = "UPDATE project_works SET accessory_release_items=:accReleaseItems, accessory_release_type=:accReleaseType, release_status='to be released' WHERE id=:id";
            DbManager::fetchPDOQuery('dto_configurator', $query, [':accReleaseItems' => $accReleaseItems, ':accReleaseType' => $accessoryReleaseType, ':id' => $projectWorkId]);

            SharedManager::saveLog('log_dtoconfigurator',"CREATED | Adding Material To Project Work Successful | ".implode(' | ', $_POST));
            Journals::saveJournal("CREATED | Adding Material To Project Work Successful | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_CREATED, implode(' | ', $_POST), "Add Material To Project Work");

            // Bom tablosuna da ekle.
            $this->insertIntoBomChangeBatch($projectWorkId, $listType, $selectedItems, $releaseQuantity, $result['common_kmats'], $accessoryReleaseType, $accessoryParentKmat, $currentlyWorkingUser);
        }
        else {
            //Eğer eklenen tipik veya pano bazlı liste ise
            $projectWorkReleaseItemsArr = !empty($result['release_items']) ? explode('|', $result['release_items']) : [];

            // OPTIMIZASYON 2: BATCH VALIDATION QUERY'LERİ

            // Already added check - PHP tarafında kontrol
            foreach ($selectedItems as $item) {
                if (in_array($item, $projectWorkReleaseItemsArr))
                    returnHttpResponse(400, "Material list has already been added under ${item}");
            }

            if ($operation === 'replace' || $operation === 'delete') {
                if ($operation === 'replace') {
                    $isReplaceMaterialsHaveSameWorkCenter = $this->compareWorkCentersOfReplaceList($result['material_added_number'], $result['material_deleted_number']);
                    if (!$isReplaceMaterialsHaveSameWorkCenter)
                        returnHttpResponse(400, "Replace Lists have different work center numbers. Please update them on Material Define.");
                }

                // Batch query: Tüm selectedItems için already deleted check
                $query = "SELECT release_items, accessory_release_items 
                      FROM project_work_view 
                      WHERE project_number = :pNo AND nachbau_number = :nNo 
                      AND material_deleted_number = :materialNo AND id != :id";
                $alreadyDeletedResult = DbManager::fetchPDOQueryData('dto_configurator', $query, [
                    ':pNo' => $projectNo,
                    ':nNo' => $nachbauNo,
                    ':materialNo' => $result['material_deleted_number'],
                    ':id' => $projectWorkId
                ])['data'][0] ?? [];

                $existingReleaseItems = [];
                $existingAccReleaseItems = [];
                if (!empty($alreadyDeletedResult)) {
                    $existingReleaseItems = !empty($alreadyDeletedResult['release_items']) ? explode('|', $alreadyDeletedResult['release_items']) : [];
                    $existingAccReleaseItems = !empty($alreadyDeletedResult['accessory_release_items']) ? explode('|', $alreadyDeletedResult['accessory_release_items']) : [];
                }
                $allExistingItems = array_merge($existingReleaseItems, $existingAccReleaseItems);

                // Batch query: Tüm selectedItems için nachbau existence check
                if ($listType === 'Typical') {
                    $typicalPlaceholders = implode(',', array_fill(0, count($selectedItems), '?'));

                    if ($result['material_deleted_starts_by'] === ':: VTH:' || $result['material_deleted_starts_by'] === ':: CTH:') {
                        $searchCableData = $result['material_deleted_starts_by'] . $result['material_deleted_number'];
                        $searchPattern = str_ireplace(['x', '?'], '_', $searchCableData);

                        $query = "SELECT DISTINCT typical_no FROM nachbau_datas 
                              WHERE project_no = ? AND nachbau_no = ? 
                              AND kmat_name LIKE ? 
                              AND typical_no IN ($typicalPlaceholders)";
                        $params = [$projectNo, $nachbauNo, '%' . $searchPattern . '%'];
                        $params = array_merge($params, $selectedItems);
                    } else {
                        $query = "SELECT DISTINCT typical_no FROM nachbau_datas 
                              WHERE project_no = ? AND nachbau_no = ? 
                              AND kmat LIKE ? 
                              AND typical_no IN ($typicalPlaceholders)";
                        $params = [$projectNo, $nachbauNo, '%' . $result['material_deleted_number'] . '%'];
                        $params = array_merge($params, $selectedItems);

                        $existingTypicals = array_column(DbManager::fetchPDOQueryData('planning', $query, $params)['data'] ?? [], 'typical_no');

                        // Eğer nachbau'da bulunamadıysa, error tablosunda ara
                        $missingTypicals = array_diff($selectedItems, $existingTypicals);
                        if (!empty($missingTypicals)) {
                            $errorPlaceholders = implode(',', array_fill(0, count($missingTypicals), '?'));
                            $errorQuery = "SELECT DISTINCT typical_no FROM project_works_nachbau_errors 
                                      WHERE project_number = ? AND nachbau_number = ? 
                                      AND material_added_number LIKE ? 
                                      AND typical_no IN ($errorPlaceholders)";
                            $errorParams = [$projectNo, $nachbauNo, '%' . $result['material_deleted_number'] . '%'];
                            $errorParams = array_merge($errorParams, $missingTypicals);
                            $existingInErrors = array_column(DbManager::fetchPDOQueryData('dto_configurator', $errorQuery, $errorParams)['data'] ?? [], 'typical_no');
                            $existingTypicals = array_merge($existingTypicals, $existingInErrors);
                        }
                    }

                    if (!isset($existingTypicals)) {
                        $existingTypicals = array_column(DbManager::fetchPDOQueryData('planning', $query, $params)['data'] ?? [], 'typical_no');
                    }
                }
                elseif ($listType === 'Panel') {
                    $panelPlaceholders = implode(',', array_fill(0, count($selectedItems), '?'));

                    if ($result['material_deleted_starts_by'] === ':: VTH:' || $result['material_deleted_starts_by'] === ':: CTH:') {
                        $searchCableData = $result['material_deleted_starts_by'] . $result['material_deleted_number'];
                        $searchPattern = str_ireplace(['x', '?'], '_', $searchCableData);

                        $query = "SELECT DISTINCT ortz_kz FROM nachbau_datas 
                              WHERE project_no = ? AND nachbau_no = ? 
                              AND kmat_name LIKE ? 
                              AND ortz_kz IN ($panelPlaceholders)";
                        $params = [$projectNo, $nachbauNo, '%' . $searchPattern . '%'];
                    } else {
                        $query = "SELECT DISTINCT ortz_kz FROM nachbau_datas 
                              WHERE project_no = ? AND nachbau_no = ? 
                              AND kmat LIKE ? 
                              AND ortz_kz IN ($panelPlaceholders)";
                        $params = [$projectNo, $nachbauNo, '%' . $result['material_deleted_number'] . '%'];
                    }
                    $params = array_merge($params, $selectedItems);
                    $existingPanels = array_column(DbManager::fetchPDOQueryData('planning', $query, $params)['data'] ?? [], 'ortz_kz');
                }

                // Tüm selectedItems için validasyonları yap
                foreach ($selectedItems as $item) {
                    if (in_array($item, $allExistingItems))
                        returnHttpResponse(400, "Deleted material number has already been removed by another list at " . $item);

                    if ($listType === 'Typical') {
                        if (!in_array($item, $existingTypicals))
                            returnHttpResponse(400, "Deleted material number is not found under the typical ${item} in nachbau.");
                    }

                    if ($listType === 'Panel') {
                        if (!in_array($item, $existingPanels))
                            returnHttpResponse(400, "Deleted material number is not found in the panel ${item} in the order.");
                    }
                }
            }
            // OPTIMIZASYON 2 SONU

            if(!empty($projectWorkReleaseItemsArr))
                $rItems = array_merge($projectWorkReleaseItemsArr, $selectedItems);
            else
                $rItems = $selectedItems;

            $rItems = implode('|', $rItems);

            if (empty($result['affected_dto_numbers'])) {
                $query = "UPDATE project_works SET release_items=:rItems, release_status='to be released', added_on_work_center_id = :addedOnWorkCenterId WHERE id=:id";
                DbManager::fetchPDOQuery('dto_configurator', $query, [':rItems' => $rItems, ':addedOnWorkCenterId' => $selectedAddedOnWorkCenterId, ':id' => $projectWorkId]);

                $this->insertIntoBomChangeBatch($projectWorkId, $listType, $selectedItems, $releaseQuantity, $result['common_kmats'], 0, $accessoryParentKmat, $currentlyWorkingUser);
            } else {
                $query = "SELECT id FROM project_work_view WHERE project_number = :pNo AND nachbau_number = :nNo";

                $params = [
                    ':pNo' => $projectNo,
                    ':nNo' => $nachbauNo
                ];

                // Handle material_added_number
                if (empty($result['material_added_number'])) {
                    $query .= " AND (material_added_number = '' OR material_added_number IS NULL)";
                } else {
                    $query .= " AND material_added_number = :mAdded";
                    $params[':mAdded'] = $result['material_added_number'];
                }

                // Handle material_deleted_number
                if (empty($result['material_deleted_number'])) {
                    $query .= " AND (material_deleted_number = '' OR material_deleted_number IS NULL)";
                } else {
                    $query .= " AND material_deleted_number = :mDeleted";
                    $params[':mDeleted'] = $result['material_deleted_number'];
                }

                $query .= " AND affected_dto_numbers = :affectedDtos";
                $params[':affectedDtos'] = $result['affected_dto_numbers'];

                $affectedDtoRows = DbManager::fetchPDOQuery('dto_configurator', $query, $params)['data'];
                $affectedDtoProjectRowsId = array_column($affectedDtoRows, 'id');

                foreach ($affectedDtoProjectRowsId as $id) {
                    $query = "UPDATE project_works SET release_items=:rItems, release_status='to be released', added_on_work_center_id = :addedOnWorkCenterId WHERE id=:id";
                    DbManager::fetchPDOQuery('dto_configurator', $query, [':rItems' => $rItems, ':addedOnWorkCenterId' => $selectedAddedOnWorkCenterId, ':id' => $id]);

                    $this->insertIntoBomChangeBatch($id, $listType, $selectedItems, $releaseQuantity, $result['common_kmats'], 0, $accessoryParentKmat, $currentlyWorkingUser);
                }
            }

        }

        // Proje güncellenme tarihini updatele.
        $query = "UPDATE projects SET last_updated_by=:whom, last_updated_date=:uptDate WHERE project_number=:pNo AND nachbau_number=:nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':whom' => SharedManager::$fullname, ':uptDate' => (new DateTime())->format('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

        $query = "SELECT release_items, accessory_release_items, added_on_work_center, added_on_work_content, material_added_sap_defined, material_deleted_sap_defined 
              FROM project_work_view
              WHERE id = :id";
        $updatedData = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $projectWorkId])['data'][0];

        echo(json_encode($updatedData));
        exit;
    }

     // OPTIMIZASYON 3: insertIntoBomChange BATCH INSERT'E ÇEVRİLDİ
    public function insertIntoBomChangeBatch($projectWorkId, $listType, $releaseItemsArr, $releaseQuantity, $parentKmat, $accessoryReleaseType, $accessoryParentKmat, $currentlyWorkingUser): void
    {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Inserting Released Items Into Bom Change With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Inserting Released Items Into Bom Change With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Add To Bom Change");

        if (empty($releaseItemsArr)) {
            return;
        }

        if (intval($accessoryReleaseType) === 2)
            $parentKmat = $accessoryParentKmat;

        $isRevisionChange = false;
        if (in_array($currentlyWorkingUser['projectStatusId'], ['7', '8', '9']))
            $isRevisionChange = true;

        $insertQuery = "INSERT INTO bom_change (project_work_id, release_item, release_quantity, parent_kmat, release_type, is_accessory, accessory_release_type, is_revision_change, send_to_review_by, publish_by, released_status, active) VALUES ";

        $bomChanges = [];
        $index = 0;

        foreach($releaseItemsArr as $item) {
            // Her item için unique parameter key'leri oluştur
            $projectWorkIdKey = ":projectWorkId$index";
            $rItemKey = ":rItem$index";
            $rQuantityKey = ":rQuantity$index";
            $pKmatKey = ":pKmat$index";
            $releaseTypeKey = ":release_type$index";
            $isAccessoryKey = ":isAccessory$index";
            $accReleaseTypeKey = ":accReleaseType$index";
            $sendToReviewByKey = ":sendToReviewBy$index";
            $isRevisionChangeKey = ":isRevisionChange$index";

            $bomChanges[] = [
                $projectWorkIdKey => $projectWorkId,
                $rItemKey => $item,
                $rQuantityKey => $releaseQuantity . '.000',
                $pKmatKey => $parentKmat,
                $releaseTypeKey => $listType,
                $isAccessoryKey => $listType === 'Accessories' ? 1 : 0,
                $accReleaseTypeKey => $accessoryReleaseType,
                $sendToReviewByKey => SharedManager::$fullname,
                $isRevisionChangeKey => $isRevisionChange ? 1 : 0
            ];

            $insertQuery .= "($projectWorkIdKey, $rItemKey, $rQuantityKey, $pKmatKey, $releaseTypeKey, $isAccessoryKey, $accReleaseTypeKey, $isRevisionChangeKey, $sendToReviewByKey, NULL, 0, 1),";

            $index++;
        }

        $insertQuery = rtrim($insertQuery, ',');

        $allParameters = [];
        foreach ($bomChanges as $change) {
            $allParameters = $allParameters + $change;
        }

        DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $allParameters);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Released Items Inserted into Bom Change With Following Parameters | " . implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Released Items Inserted into Bom Change With Following Parameters | " . implode(' | ', $_POST),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_CREATED, implode(' | ', $_POST), "Add To Bom Change");
    }
    // OPTIMIZASYON 3 SONU

    //AYNI TİPİKTEN TEKRAR MI SİLİNMEYE ÇALIŞILIYOR?
    public function checkIfMaterialDeletedNumberAlreadyAddedInTypical($projectWorkId, $projectNo, $nachbauNo, $materialDeletedNumber, $typical):bool {
        $query = "SELECT release_items, accessory_release_items FROM project_work_view WHERE project_number = :pNo AND nachbau_number = :nNo AND material_deleted_number = :materialNo AND id != :id";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':materialNo' => $materialDeletedNumber, ':id' => $projectWorkId])['data'][0] ?? [];

        $releaseItems = explode('|', $result['release_items']);
        $accReleaseItems = explode('|', $result['accessory_release_items']);

        if (in_array($typical, $releaseItems) || in_array($typical, $accReleaseItems))
            return true;

        return false;
    }

    //TİPİKTEN SİLİNECEK MALZEME LİSTESİ AKTARIMDA VAR MI KONTROLÜ?
    public function checkIfMaterialDeletedNumberWithTypicalExistsInNachbau($projectNo, $nachbauNo, $materialDeletedStartsBy, $materialDeletedNumber, $typicalNo):bool {
        if ($materialDeletedStartsBy === ':: VTH:' || $materialDeletedStartsBy === ':: CTH:') {
            //If deleted material is a cable.
            $searchCableData = $materialDeletedStartsBy . $materialDeletedNumber;
            $searchPattern = str_ireplace(['x', '?'], '_', $searchCableData);

            $query = "SELECT id FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat_name LIKE :cableCode AND typical_no = :typicalNo";
            $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':cableCode' => '%' . $searchPattern . '%', ':typicalNo' => $typicalNo])['data'] ?? [];
        } else {
            $query = "SELECT id FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat LIKE :materialNo AND typical_no = :typicalNo";
            $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':materialNo' => '%' . $materialDeletedNumber . '%', ':typicalNo' => $typicalNo])['data'] ?? [];

            if(empty($result)) {
                // AKTARIMDA BULAMADI ANCAK AKTARIM HATASI OLARAK EKLENMİŞ OLABİLİR. YILDA 1 CASE DE BÖYLE BİR ŞEY ÇIKTI.
                $query = "SELECT id FROM project_works_nachbau_errors WHERE project_number = :pNo AND nachbau_number = :nNo AND material_added_number LIKE :materialNo AND typical_no = :typicalNo";
                $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':materialNo' => '%' . $materialDeletedNumber . '%', ':typicalNo' => $typicalNo])['data'] ?? [];
            }
        }

        if(empty($result))
            return false;

        return true;
    }

    //PANELDEN SİLİNECEK MALZEME LİSTESİ AKTARIMDA VAR MI KONTROLÜ?
    public function checkIfMaterialDeletedNumberWithPanelExistsInNachbau($projectNo, $nachbauNo, $materialDeletedStartsBy, $materialDeletedNumber, $panelNo):bool {

        if ($materialDeletedStartsBy === ':: VTH:' || $materialDeletedStartsBy === ':: CTH:') {
            //If deleted material is a cable.
            $searchCableData = $materialDeletedStartsBy . $materialDeletedNumber;
            $searchPattern = str_ireplace(['x', '?'], '_', $searchCableData);

            $query = "SELECT id FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat_name LIKE :cableCode AND ortz_kz = :ortzKz";
            $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo,
                                                                                ':cableCode' => '%' . $searchPattern . '%', ':ortzKz' => $panelNo])['data'] ?? [];
        } else {
            $query = "SELECT id FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat LIKE :materialNo AND ortz_kz = :ortzKz";
            $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo,
                ':materialNo' => '%' . $materialDeletedNumber . '%', ':ortzKz' => $panelNo])['data'] ?? [];
        }

        if(empty($result))
            return false;

        return true;
    }

    //    -------------------------------------------------------------- SPARE FUNCTIONS START --------------------------------------------------------------
    public function saveSpareListsAsAccessory(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Inserting Spare DTO Works With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Inserting Spare DTO Works With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_PROCESSING, implode(' | ', $_POST), "Add Spare DTO Works");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $materials = $_POST['materials'] ?? [];
        $accessoryTypicalNumber = $_POST['accessoryTypicalNo'];
        $accessoryParentKmat = $_POST['accessoryParentKmat'];
        $accessoryPanelNo = $_POST['accessoryPanelNo'];
        $spareDtoNumber = $materials[0]['spare_dto'];
        $spareTypicalNumber = $materials[0]['spare_typical_number'];
        $isRevisionNachbau = $_POST['isRevisionNachbau'];

        // Step 1: Fetch all the existing selected materials from the database for this dto_number
        $existingMaterialsQuery = "SELECT material_added_number FROM project_works_spare
                               WHERE project_number = :project_number
                               AND nachbau_number = :nachbau_number
                               AND dto_number = :dto_number
                               AND spare_typical_number = :spare_typical_number
                               AND accessory_typical_number = :accessory_typical_number
                               AND spare_dto_type = 1
                               AND deleted IS NULL";

        $existingMaterials = DbManager::fetchPDOQueryData('dto_configurator', $existingMaterialsQuery, [
            ':project_number' => $projectNo,
            ':nachbau_number' => $nachbauNo,
            ':dto_number' => $spareDtoNumber,
            ':spare_typical_number' => $spareTypicalNumber,
            ':accessory_typical_number' => $accessoryTypicalNumber
        ])['data'] ?? [];

        // Get material numbers that already exist in the database for this specific dto_number
        $existingMaterialNumbers = array_column($existingMaterials, 'material_added_number');

        if (!empty($existingMaterials)) {

            // Step 2: Get the material numbers of the current selection
            $currentMaterialNumbers = array_map(function ($materialNumber) {
                return preg_replace('/^00/', '', $materialNumber); // Only removes the first two leading zeros
            }, array_column($materials, 'material_added_number'));


            // Step 3: Find materials that have been removed (moved to the left)
            $materialsToRemove = array_diff($existingMaterialNumbers, $currentMaterialNumbers);

            // Step 4: Delete the materials that are in the database but not in the current selection for this dto_number
            if (!empty($materialsToRemove)) {

                $deleteQuery = "DELETE FROM project_works_spare
                            WHERE project_number = :pNo
                            AND nachbau_number = :nNo
                            AND dto_number = :dtoNumber
                            AND spare_typical_number = :spare_typical_number
                            AND accessory_typical_number = :accTypical
                            AND spare_dto_type = 1
                            AND material_added_number IN (:materialsToRemove)";

                DbManager::fetchPDOQueryData('dto_configurator', $deleteQuery, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => $spareDtoNumber, ':spare_typical_number' => $spareTypicalNumber, ':accTypical' => $accessoryTypicalNumber, ':materialsToRemove' => $materialsToRemove])['data'];
            }
        }

        if (!empty($materials)) {
            // Step 5: Insert new materials that do not already exist for this dto_number
            $insertQuery = "INSERT INTO project_works_spare (project_number, nachbau_number, dto_number, dto_description, spare_dto_type, accessory_typical_number, accessory_panel_no, accessory_parent_kmat, spare_typical_number,
                                                            spare_parent_kmat, material_added_starts_by, material_added_number, material_added_description, release_quantity, release_unit, is_revision_change, send_to_review_by, publish_by) VALUES ";
            $spareProjectWorks = [];

            foreach ($materials as $index => $material) {
                $dtoDescription = $this->formatDescription($this->getNachbauDescriptionByDtoNumber($spareDtoNumber, $projectNo, $nachbauNo, true), 4);
                $materialStartsBy = $this->getSapMaterialPrefixByMaterialNo($material['material_added_number']);

                // Only insert materials that are not already in the database for this dto_number
                if (!in_array($material['material_added_number'], $existingMaterialNumbers)) {
                    $projectNumberKey = ":project_number$index";
                    $nachbauNumberKey = ":nachbau_number$index";
                    $dtoNumberKey = ":dto_number$index";
                    $dtoDescriptionKey = ":dto_description$index";
                    $spareDtoTypeKey = ":spare_dto_type$index";
                    $accessoryTypicalKey = ":accessory_typical_number$index";
                    $accessoryPanelNoKey = ":accessory_panel_no$index";
                    $accessoryParentKmatKey = ":accessory_parent_kmat$index";
                    $spareTypicalKey = ":spare_typical_number$index";
                    $spareParentKmatKey = ":spare_parent_kmat$index";
                    $materialAddedStartsByKey = ":material_added_starts_by$index";
                    $materialNumberKey = ":material_added_number$index";
                    $materialDescriptionKey = ":material_added_description$index";
                    $releaseQuantityKey = ":release_quantity$index";
                    $releaseUnitKey = ":release_unit$index";
                    $isRevisionChange = ":is_revision_change$index";
                    $sendToReviewByKey = ":send_to_review_by$index";
                    $publishByKey = ":publish_by$index";

                    $spareProjectWorks[] = [
                        $projectNumberKey => $projectNo,
                        $nachbauNumberKey => $nachbauNo,
                        $dtoNumberKey => $spareDtoNumber,
                        $dtoDescriptionKey => $dtoDescription,
                        $spareDtoTypeKey => 1,
                        $accessoryTypicalKey => $accessoryTypicalNumber,
                        $accessoryPanelNoKey => $accessoryPanelNo,
                        $accessoryParentKmatKey => $accessoryParentKmat,
                        $spareTypicalKey => $spareTypicalNumber,
                        $spareParentKmatKey => $material['spare_parent_kmat'],
                        $materialAddedStartsByKey => $materialStartsBy,
                        $materialNumberKey => preg_replace('/^00/', '', $material['material_added_number']),
                        $materialDescriptionKey => $material['material_added_description'],
                        $releaseQuantityKey => $material['material_quantity'] . '.000',
                        $releaseUnitKey => $material['material_unit'],
                        $isRevisionChange => $isRevisionNachbau,
                        $sendToReviewByKey => SharedManager::$fullname,
                        $publishByKey => null
                    ];

                    $insertQuery .= "($projectNumberKey, $nachbauNumberKey, $dtoNumberKey, $dtoDescriptionKey, $spareDtoTypeKey, $accessoryTypicalKey, $accessoryPanelNoKey, $accessoryParentKmatKey,
                                $spareTypicalKey, $spareParentKmatKey, $materialAddedStartsByKey, $materialNumberKey, $materialDescriptionKey, $releaseQuantityKey, $releaseUnitKey, $isRevisionChange, $sendToReviewByKey, $publishByKey),";
                }
            }

            // If there are new materials to insert, execute the query
            if (!empty($spareProjectWorks)) {
                $insertQuery = rtrim($insertQuery, ',');  // Remove trailing comma
                DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $spareProjectWorks, [], false);

                SharedManager::saveLog('log_dtoconfigurator',"CREATED | Spare DTO Works Created Successfully With Following Parameters | ".implode(' | ', $_POST));
                Journals::saveJournal("CREATED | Spare DTO Works Created Successfully With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_CREATED, implode(' | ', $_POST), "Add Spare DTO Works");
            }
        }
    }

    public function removeSpareWorks(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Removing Spare DTO Works With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Removing Spare DTO Works With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_PROCESSING, implode(' | ', $_POST), "Remove Spare DTO Works");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $spareDto = $_POST['spareDto'];
        $spareTypical = $_POST['spareTypical'];

        $query = "SELECT id FROM project_works_spare WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :spareDto AND spare_typical_number = :spareTypical AND spare_dto_type = 1";
        $spareWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':spareDto' => $spareDto, ':spareTypical' => $spareTypical])['data'] ?? [];
        $spareWorksIds = array_column($spareWorks, 'id');

        if(empty($spareWorksIds))
            returnHttpResponse(404, 'There is no existing spare works for ' . $spareDto . ' - ' . $spareTypical);

        $query = "UPDATE project_works_spare SET deleted_user = :delUser, deleted = :deletedAt WHERE id IN (:spareWorkIds)";
        $parameters = [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':spareWorkIds' => $spareWorksIds];
        DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'];

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Spare DTO Works Removed Successfully With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Spare DTO Works Removed Successfully With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_CREATED, implode(' | ', $_POST), "Remove Spare DTO Works");
    }

    public function getSpareProjectsOfTypical(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $spareDtoNumber = $_GET['dtoNumber'];
        $spareTypical = $_GET['typicalNo'];
        $accessoryTypicalNumber = $_GET['accessoryTypicalNo'];

        $query = "SELECT material_added_number, material_added_description, spare_typical_number, spare_parent_kmat, release_quantity, release_unit FROM project_works_spare
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNumber AND spare_typical_number = :spareTypical AND accessory_typical_number = :accTypical AND spare_dto_type = 1 AND deleted IS NULL";

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => $spareDtoNumber, ':spareTypical' => $spareTypical, ':accTypical' => $accessoryTypicalNumber])['data'] ?? [];
        echo(json_encode($data)); exit;
    }

    public function getSpareParametersOfProject(): void {
        $spareProjectId = $_GET['spareProjectId'];

        $query = "SELECT spare_typical_number, spare_parent_kmat, dto_number, dto_description FROM project_works_spare WHERE id=:id AND deleted IS NULL";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $spareProjectId])['data'][0] ?? [];

        if (empty($data))
            returnHttpResponse(404, 'Spare Project with ID : ' . $spareProjectId . ' not found.');

        echo (json_encode($data));
    }

    public function getSparePartDtoKmatsOfProject(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];

        // 11,3, 13, 12 work centers are for spare part dto's.
        $query = "SELECT parent_kmat FROM material_kmat_subkmats WHERE work_center_id IN (11,3,12,13) GROUP BY parent_kmat";
        $parentKmats = array_column(DbManager::fetchPDOQueryData('dto_configurator', $query)['data'], 'parent_kmat') ?? [];

        $query = "SELECT sub_kmat FROM material_kmat_subkmats WHERE work_center_id IN (11,3,12,13) AND sub_kmat IS NOT NULL GROUP BY sub_kmat";
        $subKmats = array_column(DbManager::fetchPDOQueryData('dto_configurator', $query)['data'], 'sub_kmat') ?? [];

        $allKmats = array_unique(array_merge($parentKmats,$subKmats));
        $allKmatsWithZeros = array_map(function($kmat) {
            return "00" . $kmat;
        }, $allKmats);


        $query = "SELECT Id, position, kmat, qty, unit,
                  kmat_name, parent_kmat, typical_no, ortz_kz, panel_no
                    FROM nachbau_datas
                    WHERE project_no = :pNo
                      AND nachbau_no = :nNo
                      AND (kmat IN (:ids) OR parent_kmat IN (:ids))
                      ORDER BY Id ASC";

        $data = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':ids' => $allKmatsWithZeros])['data'] ?? [];

        ob_start("ob_gzhandler");

        header("Content-Encoding: gzip");
        header("Content-Type: application/json");

        echo json_encode($data);
        exit();
    }

    public function getSelectedSparePartItemsOfDto():void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $dtoNumber = $_GET['dtoNumber'];
        $typicalNo = $_GET['typicalNo'];

        $query = "SELECT id, material_added_number, material_added_description, release_quantity, release_unit FROM project_works_spare 
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND dto_number = :dtoNumber 
                  AND spare_typical_number = :typicalNo AND spare_dto_type = 2 AND deleted IS NULL";
        $data = DbManager::fetchPDOQuery('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => $dtoNumber ,':typicalNo' => $typicalNo])['data'] ?? [];
        echo json_encode($data);exit();
    }

    public function addSparePartToSelectedItems(): void {
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $accessoryTypicalNo = $_POST['accessoryTypicalNo'];
        $accessoryPanelNo = $_POST['accessoryPanelNo'];
        $accessoryParentKmat = $_POST['accessoryParentKmat'];
        $dtoNumber = $_POST['dtoNumber'];
        $description = $_POST['description'];
        $typicalNo = $_POST['typicalNo'];
        $quantity = $_POST['quantity'];
        $rowData = $_POST['rowData'];
        $isRevisionNachbau = $_POST['isRevisionNachbau'];

        $query = "INSERT INTO project_works_spare(project_number, nachbau_number, dto_number, dto_description, spare_dto_type, accessory_typical_number, accessory_panel_no, accessory_parent_kmat, 
                                spare_typical_number, spare_parent_kmat, material_added_starts_by, material_added_number, material_added_description, release_quantity, release_unit, is_revision_change, send_to_review_by, publish_by)";

        $parameters[] = [
            $projectNo,
            $nachbauNo,
            $dtoNumber,
            $this->formatDescription($description, 3),
            2,
            $accessoryTypicalNo,
            $accessoryPanelNo,
            $accessoryParentKmat,
            $typicalNo,
            ltrim($rowData['parent_kmat'], '0'), // parent kmat without zeros at first
            $this->getSapMaterialPrefixByMaterialNo($rowData['kmat']), // material starts by
            preg_replace('/^00/', '', $rowData['kmat']), // material no
            $rowData['kmat_name'], // material desc
            $quantity . '.000',
            $rowData['unit'],
            $isRevisionNachbau,
            SharedManager::$fullname,
            NULL
        ];

        $response_insert = DbManager::fetchInsert('dto_configurator', $query, $parameters);
        $lastInsertedSpareId = $response_insert["pdoConnection"]->lastInsertId();
        echo json_encode($lastInsertedSpareId);exit();
    }

    public function removeSparePartWork(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Delete Request For Spare Part Work With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Delete Request For Spare Part Work With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Delete Spare Part Work");

        $sparePartWorkId = $_POST['sparePartWorkId'];

        $query = "SELECT id FROM project_works_spare WHERE id = :id";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $sparePartWorkId])['data'][0] ?? [];

        if (empty($result))
            returnHttpResponse(404, "Selected spare part work could not found in DB!");

        $query = "UPDATE project_works_spare SET deleted_user = :delUser, deleted = :deletedAt WHERE id = :id";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':id' => $sparePartWorkId]);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Spare Part Work Deleted Successfully With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Spare Part Work Deleted Successfully With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_DELETED, implode(' | ', $_POST), "Delete Spare Part Work");
    }
    //    -------------------------------------------------------------- SPARE FUNCTIONS END --------------------------------------------------------------

    //    -------------------------------------------------------------- NACHBAU ERROR FUNCTIONS START --------------------------------------------------------------
    public function saveNachbauErrorChanges(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Nachbau Error Create Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Nachbau Error Create Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Create Nachbau Error");

        // Parameters
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $materialAddedNo = $_POST['materialAddedNo'];
        $materialDeletedNo = $_POST['materialDeletedNo'];
        $operation = $_POST['operation'];
        $note = $_POST['note'];
        $quantity = $_POST['quantity'];
        $unit = $_POST['unit'];
        $selectedItems = $_POST['selectedItems'];
        $accessoryTypicalCode = $_POST['accessoryTypicalCode'];
        $isRevisionNachbau = $_POST['isRevisionNachbau'];

        $insertData = [];
        $selectedTypicals = array_keys($selectedItems);
        $selectedPanels = array_merge(...array_values($selectedItems));
        $typicalToPanelsMap = $this->getTypicalAndPanelsDictionary($projectNo, $nachbauNo);

        // ----------------------------------- ERROR CONTROLS START -----------------------------------
        if ($operation !== 'add') {
             // Değişen bir listeyse, iki malzemenin KMAT değerleri aynı olmalı.
            if ($operation === 'replace' && !$this->compareWorkCentersOfReplaceList($materialAddedNo, $materialDeletedNo))
                returnHttpResponse(404, 'Work Centers of Replaced List are not equal!');

            // Silinen malzeme seçili pano veya tipiklerde aktarımdan gelmiş mi?
            $notFoundItems = $this->validateDeletedMaterial($projectNo, $nachbauNo, $materialDeletedNo, $selectedTypicals, $selectedPanels);
            if (!empty($notFoundItems))
                returnHttpResponse(404, 'Deleted list ' . implode(', ', $notFoundItems) . ' can not found in Nachbau TXT!  ');

            // Prepare data for insert
            foreach ($selectedItems as $typicalNo => $panels) {
                foreach ($panels as $ortzKz){
                    $materialData = $this->getParentKmatOfMaterialWithTypicalAndOrtzKz($projectNo, $nachbauNo, $materialDeletedNo, $typicalNo, $ortzKz);
                    $insertData[] = $this->prepareNachbauErrorInsertData($projectNo, $nachbauNo, $operation, $materialAddedNo, $materialDeletedNo, $note, $materialData['qty'], $materialData['unit'], $typicalNo, $ortzKz, ltrim($materialData['parent_kmat'], '0'), $typicalToPanelsMap, $isRevisionNachbau);
                }
            }
        } else {
            $productId = $this->getProductIdOfProject($projectNo);

            // Eklenecek malzemenin, istasyonuna ait mümkün olan bütün KMAT değerleri
            $query = "SELECT parent_kmat, sub_kmat, work_center_id, has_sub_kmat FROM material_kmat_subkmats WHERE material_number = :materialAddedNo AND product_id = :productId GROUP BY parent_kmat";
            $materialKmatsResult = DbManager::fetchPDOQueryData('dto_configurator',$query, [':materialAddedNo' => $materialAddedNo, ':productId' => $productId])['data'] ?? [];

            if (empty($materialKmatsResult))
                returnHttpResponse(404, 'Please update work center of added material!');

            $materialSubKmats = '';
            if ($materialKmatsResult[0]['has_sub_kmat'] === '1')
                $materialSubKmats = array_column($materialKmatsResult, 'sub_kmat');

            $materialParentKmats = array_unique(array_column($materialKmatsResult, 'parent_kmat'));

            // Eklenecek malzemenin KMAT değerlerinden biri Aktarımdan gelmiş mi kontrolünü yap ve duruma göre ekle
            foreach ($selectedItems as $typicalNo => $panels) {
                foreach ($panels as $ortzKz) {

                    if ($typicalNo === $accessoryTypicalCode) {
                        $commonKmatString = $this->getAccessoryKmatObjectOfProject($projectNo, $nachbauNo)['kmat'];
                    } else {
                        $commonKmatString = $this->getCommonKmat($projectNo, $nachbauNo, $typicalNo, $ortzKz, $materialSubKmats, $materialParentKmats);

                        // If no common KMAT found, return an error
                        if (empty($commonKmatString))
                            returnHttpResponse(404, 'KMAT of ' . $materialAddedNo  . ' is not equal KMAT of ' . $typicalNo . '!');
                    }

                    $insertData[] = $this->prepareNachbauErrorInsertData($projectNo, $nachbauNo, $operation, $materialAddedNo, $materialDeletedNo, $note, $quantity . '.000', $unit, $typicalNo, $ortzKz, $commonKmatString, $typicalToPanelsMap, $isRevisionNachbau);
                }
            }
        }

        // ----------------------------------- ERROR CONTROLS END -----------------------------------

        // REMOVE OLD DATA IF ALREADY EXITS
        $query = "UPDATE project_works_nachbau_errors SET deleted = :deletedAt, deleted_user = :delUser
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND material_added_number = :mAdded AND material_deleted_number = :mDeleted AND deleted IS NULL";
        $parameters = [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'),':pNo' => $projectNo, ':nNo' => $nachbauNo, ':mAdded' => $materialAddedNo, ':mDeleted' => $materialDeletedNo];
        DbManager::fetchPDOQueryData('dto_configurator', $query, $parameters)['data'] ?? [];

        // INSERT PROCESS
        $query = "INSERT INTO project_works_nachbau_errors (project_number, nachbau_number, operation, material_added_starts_by, material_added_number, material_added_description, material_deleted_starts_by, material_deleted_number, material_deleted_description,
                                                          note, quantity, unit, typical_no, ortz_kz, panel_no, parent_kmat, is_revision_change, send_to_review_by)";
        DbManager::fetchInsert('dto_configurator', $query, $insertData);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Nachbau Error Successfully Created With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Nachbau Error Successfully Created With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_CREATED, implode(' | ', $_POST), "Create Nachbau Error");
    }

    private function validateDeletedMaterial($projectNo, $nachbauNo, $materialNo, $typicals, $panels): array
    {
        $notFoundItems = [];

        // Check if deleted material exists in selected typicals
        foreach ($typicals as $typical) {
            $result = DbManager::fetchPDOQueryData(
                'planning',
                "SELECT Id FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat LIKE :materialNo AND typical_no = :typicalNo",
                [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':materialNo' => '%' . $materialNo . '%', ':typicalNo' => $typical]
            )['data'] ?? [];

            if (empty($result)) {
                $notFoundItems[] = $typical;
            }
        }

        // Check if deleted material exists in selected panels
        foreach ($panels as $panel) {
            $result = DbManager::fetchPDOQueryData(
                'planning',
                "SELECT Id FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat LIKE :materialNo AND ortz_kz = :ortzKz",
                [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':materialNo' => '%' . $materialNo . '%', ':ortzKz' => $panel]
            )['data'] ?? [];

            if (empty($result)) {
                $notFoundItems[] = $panel;
            }
        }

        return $notFoundItems;
    }

    private function getCommonKmat($projectNo, $nachbauNo, $typicalNo, $ortzKz, $materialSubKmats, $materialParentKmats): string
    {
        $query = "SELECT SUBSTRING(kmat, 3) as nachbau_kmats
                  FROM nachbau_datas
                  WHERE project_no = :pNo
                  AND nachbau_no = :nNo
                  AND (kmat LIKE '003003%' OR kmat LIKE '003013%')
                  AND typical_no = :typicalNo AND ortz_kz = :ortzKz
                  GROUP BY kmat";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':typicalNo' => $typicalNo, ':ortzKz' => $ortzKz])['data'] ?? [];
        $nachbauParentKmats = array_column($result, 'nachbau_kmats');

        if (!empty($materialSubKmats)) {
            $commonKmatArr = array_intersect($nachbauParentKmats, $materialSubKmats);
            if(empty($commonKmatArr))
                $commonKmatArr = array_intersect($nachbauParentKmats, $materialParentKmats);
        }
        else {
            $commonKmatArr = array_intersect($nachbauParentKmats, $materialParentKmats);
        }

        return implode('|', $commonKmatArr);
    }

    private function prepareNachbauErrorInsertData($projectNo, $nachbauNo, $operation, $materialAddedNo, $materialDeletedNo, $note, $quantity, $unit, $typicalNo, $ortzKz, $commonKmatString, $typicalToPanelsMap, $isRevisionNachbau) {

        if (!empty($materialAddedNo)) {
            $materialAddedStartsBy = $this->getSapMaterialPrefixByMaterialNo($materialAddedNo);
            $materialAddedDescription = DbManager::fetchPDOQueryData('dto_configurator',"SELECT description FROM materials WHERE material_number = :materialNo", [':materialNo' => $materialAddedNo])['data'][0]['description'] ?? '';
        }

        if (!empty($materialDeletedNo)) {
            $materialDeletedStartsBy = $this->getSapMaterialPrefixByMaterialNo($materialDeletedNo);
            $materialDeletedDescription = DbManager::fetchPDOQueryData('dto_configurator',"SELECT description FROM materials WHERE material_number = :materialNo", [':materialNo' => $materialDeletedNo])['data'][0]['description'] ?? '';
        }

        if (isset($typicalToPanelsMap[$typicalNo])) {
            foreach ($typicalToPanelsMap[$typicalNo] as $panel) {
                if ($panel['ortz_kz'] === $ortzKz) {
                    $panelNo = $panel['panel_no'];
                    break;
                }
            }
        }

        return [
            'project_number' => $projectNo,
            'nachbau_number' => $nachbauNo,
            'operation' => $operation,
            'material_added_starts_by' => $materialAddedStartsBy ?? '',
            'material_added_number' => $materialAddedNo,
            'material_added_description' => $materialAddedDescription ?? '',
            'material_deleted_starts_by' => $materialDeletedStartsBy ?? '',
            'material_deleted_number' => $materialDeletedNo,
            'material_deleted_description' => $materialDeletedDescription ?? '',
            'note' => $note,
            'quantity' => $quantity,
            'unit' => $unit,
            'typical_no' => $typicalNo,
            'ortz_kz' => $ortzKz ?? '',
            'panel_no' => $panelNo ?? '',
            'parent_kmat' => $commonKmatString,
            'is_revision_change' => $isRevisionNachbau,
            'send_to_review_by' => SharedManager::$fullname
        ];
    }

    public function compareWorkCentersOfReplaceList($materialAddedNo, $materialDeletedNo): bool
    {
        $query = "SELECT work_center_id FROM materials WHERE material_number = :mAddedNo";
        $materialAddedWc = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mAddedNo' => $materialAddedNo])['data'][0]['work_center_id'] ?? [];

        $query = "SELECT work_center_id FROM materials WHERE material_number = :mDeletedNo";
        $materialDeletedWc = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mDeletedNo' => $materialDeletedNo])['data'][0]['work_center_id'] ?? [];

        // Eğer Eklenen ve Silinen malzemenin istasyonları aynıysa true döndür.
        if ($materialAddedWc === $materialDeletedWc)
            return true;
        else
            return false;
    }

    public function deleteNachbauErrorChange(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Nachbau Error Delete Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Nachbau Error Delete Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_PROCESSING, implode(' | ', $_POST), "Delete Nachbau Error");

        $nachbauErrorId = $_POST['nachbauErrorId'];

        $query = "SELECT id FROM project_works_nachbau_errors WHERE id = :id";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $nachbauErrorId])['data'][0] ?? [];

        if (empty($result))
            returnHttpResponse(404, "Selected nachbau error could not found in database!");

        $query = "UPDATE project_works_nachbau_errors SET deleted_user = :delUser, deleted = :deletedAt WHERE id = :id";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':id' => $nachbauErrorId]);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Nachbau Error Deleted Successfully With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Nachbau Error Deleted Successfully With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_DELETED, implode(' | ', $_POST), "Delete Nachbau Error");
    }

    //    -------------------------------------------------------------- NACHBAU ERROR FUNCTIONS END --------------------------------------------------------------

    public function checkOutProject(): void {
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];

        $query = "UPDATE projects SET working_user = NULL,last_updated_by = :lastUpdatedBy, last_updated_date = :lastUpdatedDate, project_status = 2 WHERE project_number = :pNo AND nachbau_number = :nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':lastUpdatedBy' => SharedManager::$fullname, ':lastUpdatedDate' => (new DateTime())->format('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

        SharedManager::saveLog('log_dtoconfigurator',"UPDATED | User : " . SharedManager::$fullname . " Checked Out Project " . $projectNo . " and Nachbau " . $nachbauNo . " | ".implode(' | ', $_POST));
        Journals::saveJournal("UPDATED : " . SharedManager::$fullname . " Checked Out From Project " . $projectNo . " and Nachbau " . $nachbauNo . " | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_MODIFIED, implode(' | ', $_POST), "Project Work");
    }

    public function checkInProject(): void {
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];

        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized Designer for Project Work | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized Designer for Project Work | ".implode(' | ', $_POST),PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Project Work");
            returnHttpResponse(400, "Unauthorized user to to start working to order.");
        }

        $query = "UPDATE projects SET working_user = :workingUser, last_updated_by = :workingUser, last_updated_date = :lastUpdatedDate, project_status = 2 WHERE project_number = :pNo AND nachbau_number = :nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':workingUser' => SharedManager::$fullname, ':lastUpdatedDate' => (new DateTime())->format('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

        SharedManager::saveLog('log_dtoconfigurator',"UPDATED | User : " . SharedManager::$fullname . " Checked In Project " . $projectNo . " and Nachbau " . $nachbauNo . " | ".implode(' | ', $_POST));
        Journals::saveJournal("UPDATED | User : " . SharedManager::$fullname . " Checked In Project " . $projectNo . " and Nachbau " . $nachbauNo . " | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_MODIFIED, implode(' | ', $_POST), "Project Work");
        echo (json_encode(SharedManager::$fullname));exit();
    }

    public function updateCommonKmatOfDevice() {
        $projectWorkId = $_POST['projectWorkId'];
        $selectedKmat = $_POST['selectedKmat'];
        $selectedWorkCenterId = $_POST['selectedWorkCenterId'];

        $query = "UPDATE project_works SET common_kmats = :selectedKmat, added_on_work_center_id = :selectedWorkCenterId WHERE id = :id";
            DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $projectWorkId, ':selectedKmat' => $selectedKmat, ':selectedWorkCenterId' => $selectedWorkCenterId]) ?? [];
    }

    public function getOverviewOfProjectWork(): void {
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $nachbauDate = $_GET['nachbauDate'];
        $currentlyWorkingUser = $_GET['currentlyWorkingUser'];

        if($currentlyWorkingUser['name'] !== SharedManager::$fullname || in_array($currentlyWorkingUser['projectStatusId'], ['3', '5'])) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | You should be working user to send publish request! | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | You should be working user to send publish request! | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_ERROR, implode(' | ', $_POST), "Add Material To Project Work");
            returnHttpResponse(400, ' You should be working user to send publish request!');
        }

        $query = "SELECT OrderManager, Product, ProjectEngineer, ProjectName, Qty FROM dbo.OneX_ProjectDetails WHERE FactoryNumber = :pNo";
        $projectMtoolData = DbManager::fetchPDOQuery('MTool_INKWA', $query, [':pNo' => $projectNo])['result'][0] ?? [];
        $projectCharacteristics = $this->getProjectCharacteristics($projectNo, $projectMtoolData['Product']);
        $projectContacts = MtoolManager::getProjectContacts([$projectNo])[0];
        $totalChanges = count($this->prepareReleaseOrderChanges($projectNo, $nachbauNo, $currentlyWorkingUser['projectStatusId']));

        $projectInfoData[] = [
            'projectNumber' => $projectNo,
            'projectName' => $projectMtoolData['ProjectName'] ?? null,
            'nachbauNumber' => $nachbauNo,
            'nachbauDate' => $nachbauDate,
            'product' => $projectMtoolData['Product'] ?? null,
            'panelQty' => $projectMtoolData['Qty'] ?? null,
            'ratedVoltage' => $projectCharacteristics['rated_voltage'] ?? null,
            'ratedShortCircuit' => $projectCharacteristics['rated_short_circuit'] ?? null,
            'ratedCurrent' => $projectCharacteristics['rated_current'] ?? null,
            'electricalEngineer' => $projectContacts['ElectricalEngineer'] ?? null,
            'orderManager' => $projectContacts['OrderManager'] ?? null,
            'mechanicalEngineer' => $projectContacts['MechanicalEngineer'] ?? null,
            'workedUser' => SharedManager::$fullname ?? null,
            'totalChanges' => $totalChanges
        ];

        echo(json_encode($projectInfoData));
        exit();
    }

    public function getBomChangeWithOrderChanges() {
        ini_set('memory_limit', '512M');
        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];
        $projectStatusId = $_GET['projectStatusId'];

        $query = "SELECT position, feld_name, typical_no, ortz_kz, panel_no, kmat, qty, unit, kmat_name, parent_kmat, description
                  FROM nachbau_datas 
                  WHERE project_no=:pNo AND nachbau_no=:nNo";
        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];


        // Pre-process order changes into efficient lookup maps
        $replaceDeleteLookup = []; // [typical_no][ortz_kz][panel_no][material_deleted_number] = orderChange
        $addLookup = []; // [typical_no][ortz_kz][parent_kmat][] = orderChange

        $nachbauDtoNames = $this->getNachbauDtoNumbersStartsBy();
        $orderChanges = $this->prepareReleaseOrderChanges($projectNo, $nachbauNo, $projectStatusId, $result);

        foreach ($orderChanges as $orderChange) {
            if ($orderChange['operation'] === 'replace' || $orderChange['operation'] === 'delete') {
                $cleanMaterialDeleted = $orderChange['material_deleted_number'];
                $replaceDeleteLookup[$orderChange['typical_no']][$orderChange['ortz_kz']][$orderChange['panel_no']][$cleanMaterialDeleted] = $orderChange;
            } elseif ($orderChange['operation'] === 'add') {
                $cleanParentKmat = ltrim($orderChange['parent_kmat'], '0');
                $addLookup[$orderChange['typical_no']][$orderChange['ortz_kz']][$orderChange['panel_no']][$cleanParentKmat][] = $orderChange;
            }
        }

        $fullBomChange = [];

        foreach ($result as $row) {
            $isCable = false;
            $cleanParentKmat = ltrim($row['parent_kmat'], '0');
            $cleanKmat = preg_replace('/^00/', '', $row['kmat']);
            if (str_starts_with($row['kmat_name'], ':: VTH:') || str_starts_with($row['kmat_name'], ':: CTH:')) {
                $cleanKmat = substr($row['kmat_name'], 7); //REMOVE VTH or CTH if cable is replaced
                $isCable = true;
            }

            $nachbauRow = [
                'type' => 'nachbau_row',
                'position' => $row['position'],
                'feld_name' => $row['feld_name'],
                'typical_no' => $row['typical_no'],
                'ortz_kz' => $row['ortz_kz'],
                'panel_no' => $row['panel_no'],
                'parent_kmat' => $cleanParentKmat,
                'kmat' => $cleanKmat,
                'kmat_name' => $row['kmat_name'],
                'release_quantity' => preg_replace('/\.000$/', '', $row['qty']),
                'release_unit' => $row['unit'],
                'release_type' => '',
                'operation' => '',
                'dto_number' => '',
                'dto_description' => '',
                'affected_dto_numbers' => '',
                'material_added_starts_by' => '',
                'material_added_number' => '',
                'material_added_description' => '',
                'material_deleted_starts_by' => '',
                'material_deleted_number' => '',
                'material_deleted_description' => '',
                'is_accessory' => '',
                'released_dto_type_id' => '',
                'order_change_id' => null,
                'is_cable' => $isCable
            ];

            // Check for replace/delete operations on this row - O(1) lookup
            if (isset($replaceDeleteLookup[$row['typical_no']][$row['ortz_kz']][$row['panel_no']][$cleanKmat])) {
                $orderChange = $replaceDeleteLookup[$row['typical_no']][$row['ortz_kz']][$row['panel_no']][$cleanKmat];

                // Mark this nachbau row with the operation
                $nachbauRow['type'] = 'order_change_delete';
                $nachbauRow['order_change_id'] = $orderChange['id'];
                $nachbauRow['release_type'] = $orderChange['release_type'];
                $nachbauRow['operation'] = $orderChange['operation'];
                $nachbauRow['dto_number'] = $orderChange['dto_number'];
                $nachbauRow['dto_description'] = $orderChange['dto_description'];
                $nachbauRow['affected_dto_numbers'] = $orderChange['affected_dto_numbers'];
                $nachbauRow['material_added_starts_by'] = $orderChange['material_added_starts_by'];
                $nachbauRow['material_added_number'] = $orderChange['material_added_number'];
                $nachbauRow['material_added_description'] = $orderChange['material_added_description'];
                $nachbauRow['material_deleted_starts_by'] = $orderChange['material_deleted_starts_by'];
                $nachbauRow['material_deleted_number'] = $orderChange['material_deleted_number'];
                $nachbauRow['material_deleted_description'] = $orderChange['material_deleted_description'];
                $nachbauRow['is_accessory'] = $orderChange['is_accessory'];
                $nachbauRow['released_dto_type_id'] = $orderChange['released_dto_type_id'];
                $nachbauRow['is_revision_change'] = $orderChange['is_revision_change'];
                $nachbauRow['send_to_review_by'] = $orderChange['send_to_review_by'];
                $nachbauRow['created_at'] = $orderChange['created_at'];

                // For replace operations, also update the quantity if provided
                if ($orderChange['operation'] === 'replace' && !empty($orderChange['release_quantity'])) {
                    $nachbauRow['type'] = 'order_change_replace';
                    $nachbauRow['release_quantity'] = preg_replace('/\.000$/', '', $orderChange['release_quantity']);
                    $nachbauRow['release_unit'] = $orderChange['release_unit'];
                }

                if ($orderChange['released_dto_type_id'] === 5) {
                    $nachbauRow['nachbau_error_id'] = $orderChange['nachbau_error_id'];
                }
            }

            // Add the nachbau row (possibly marked with replace/delete operation)
            $fullBomChange[] = $nachbauRow;

            // Check for DTO description row
            $isDtoNumberRow = false;
            foreach ($nachbauDtoNames as $dtoName) {
                if (str_contains($row['kmat_name'], $dtoName)) {
                    $isDtoNumberRow = true;
                    break;
                }
            }

            // Add description row for non-DTO items
            if (!$isDtoNumberRow && $row['description']) {
                $fullBomChange[] = [
                    'type' => 'nachbau_description',
                    'position' => '',
                    'typical_no' => $row['typical_no'],
                    'ortz_kz' => $row['ortz_kz'],
                    'panel_no' => $row['panel_no'],
                    'parent_kmat' => $cleanParentKmat,
                    'kmat' => '',
                    'kmat_name' => $row['description'],
                    'release_quantity' => '',
                    'release_unit' => '',
                    'release_type' => '',
                    'operation' => '',
                    'dto_number' => '',
                    'dto_description' => '',
                    'affected_dto_numbers' => '',
                    'material_added_number' => '',
                    'material_added_description' => '',
                    'material_deleted_number' => '',
                    'material_deleted_description' => '',
                    'is_accessory' => '',
                    'released_dto_type_id' => '',
                    'order_change_data' => null
                ];
            }

            // Check for add operations that should be inserted after this row - O(1) lookup
            if (isset($addLookup[$row['typical_no']][$row['ortz_kz']][$row['panel_no']][$cleanKmat])) {
                $addOperations = $addLookup[$row['typical_no']][$row['ortz_kz']][$row['panel_no']][$cleanKmat];

                foreach ($addOperations as $orderChange) {
                    // Insert the new row for add operation
                    $addOperationRow = [
                        'type' => 'order_change_add',
                        'order_change_id' => $orderChange['id'],
                        'position' => $this->increaseNachbauRowPosition($row['position']),
                        'typical_no' => $orderChange['typical_no'],
                        'ortz_kz' => $orderChange['ortz_kz'],
                        'panel_no' => $orderChange['panel_no'],
                        'parent_kmat' => ltrim($orderChange['parent_kmat'], '0'),
                        'kmat' => ltrim($orderChange['material_added_number'], '0'),
                        'kmat_name' => $orderChange['material_added_description'],
                        'release_quantity' => preg_replace('/\.000$/', '', $orderChange['release_quantity']),
                        'release_unit' => $orderChange['release_unit'],
                        'release_type' => $orderChange['release_type'],
                        'operation' => $orderChange['operation'],
                        'dto_number' => $orderChange['dto_number'],
                        'dto_description' => $orderChange['dto_description'],
                        'affected_dto_numbers' => $orderChange['affected_dto_numbers'],
                        'material_added_starts_by' => $orderChange['material_added_starts_by'],
                        'material_added_number' => $orderChange['material_added_number'],
                        'material_added_description' => $orderChange['material_added_description'],
                        'material_deleted_starts_by' => $orderChange['material_deleted_starts_by'],
                        'material_deleted_number' => $orderChange['material_deleted_number'],
                        'material_deleted_description' => $orderChange['material_deleted_description'],
                        'is_accessory' => $orderChange['is_accessory'],
                        'released_dto_type_id' => $orderChange['released_dto_type_id'],
                        'is_revision_change' => $orderChange['is_revision_change'],
                        'send_to_review_by' => $orderChange['send_to_review_by'],
                        'created_at' => $orderChange['created_at'],
                        'is_cable' => false
                    ];

                    // IF SPARE DTO
                    if ($orderChange['released_dto_type_id'] === 2) {
                        $addOperationRow['spare_typical_no'] = $orderChange['spare_typical_no'];
                        $addOperationRow['spare_dto_type'] = $orderChange['spare_dto_type'];
                        $addOperationRow['spare_project_id'] = $orderChange['spare_project_id'];
                    }
                    if ($orderChange['released_dto_type_id'] === 3) {
                        $addOperationRow['note'] = $orderChange['note'];
                        $addOperationRow['extension_extra_note'] = $orderChange['extension_extra_note'];
                    }
                    if ($orderChange['released_dto_type_id'] === 5) {
                        $addOperationRow['nachbau_error_id'] = $orderChange['nachbau_error_id'];
                    }
                    $fullBomChange[] = $addOperationRow;
                }
            }
        }

        ob_start("ob_gzhandler");

        header("Content-Encoding: gzip");
        header("Content-Type: application/json");

        echo json_encode($fullBomChange);
        exit();
    }

    public function prepareReleaseOrderChanges($projectNo, $nachbauNo, $projectStatusId, $nachbauData = null): array {

        $finalChanges = [];
        $typicalToPanelsMap = $this->getTypicalAndPanelsDictionary($projectNo, $nachbauNo);

        if ($nachbauData === null) {
            $nachbauDataQuery = "SELECT typical_no, ortz_kz, panel_no, parent_kmat, kmat 
                                 FROM nachbau_datas 
                                 WHERE project_no = :pNo AND nachbau_no = :nNo";
            $nachbauData = DbManager::fetchPDOQueryData('planning', $nachbauDataQuery, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];
        }

        // ALL STANDARD ORDER CHANGES
        $query = "SELECT operation, release_type, release_item, release_quantity, unit, parent_kmat, is_accessory, dto_number, dto_description, send_to_review_by, affected_dto_numbers,
                     material_added_starts_by, material_added_number, material_added_description, material_deleted_starts_by, material_deleted_number, material_deleted_description, is_revision_change, created
                  FROM order_changes
                  WHERE project_number=:pNo AND nachbau_number=:nNo";
        $orderChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];

        foreach ($orderChanges as $change) {
            if ($change['release_type'] === 'Panel') {
                $ortzKz = $change['release_item'];

                foreach ($typicalToPanelsMap as $typical => $panels) {
                    foreach ($panels as $panelData) {
                        if ($panelData['ortz_kz'] === $ortzKz) {
                            $parentKmats = [$change['parent_kmat']];

                            if ($change['operation'] !== 'add' && $change['material_deleted_starts_by'] !== ':: CTH:' && $change['material_deleted_starts_by'] !== ':: VTH:') {

                                $parentKmats = [];
                                foreach ($nachbauData as $row) {
                                    if ($row['typical_no'] == $typical &&
                                        $row['ortz_kz'] == $ortzKz &&
                                        $row['panel_no'] == $panelData['panel_no'] &&
                                        str_contains($row['kmat'], $change['material_deleted_number']) &&
                                        str_contains($row['parent_kmat'], $change['parent_kmat'])) {

                                        $cleanParentKmat = preg_replace('/^00/', '', $row['parent_kmat']);
                                        $parentKmats[] = $cleanParentKmat;
                                    }
                                }

                                if (empty($parentKmats)) {
                                    $parentKmats = [$change['parent_kmat']];
                                }
                            }

                            foreach ($parentKmats as $parentKmat) {
                                $finalChanges[] = [
                                    'released_dto_type_id' => 1,
                                    'dto_number' => $change['dto_number'],
                                    'dto_description' => html_entity_decode($change['dto_description']),
                                    'affected_dto_numbers' => $change['affected_dto_numbers'],
                                    'operation' => $change['operation'],
                                    'typical_no' => $typical,
                                    'ortz_kz' => $ortzKz,
                                    'panel_no' => $panelData['panel_no'],
                                    'sap_pos_no' => $panelData['sap_pos_no'],
                                    'release_type' => $change['release_type'],
                                    'release_quantity' => $change['release_quantity'],
                                    'release_unit' => $change['unit'],
                                    'parent_kmat' => $parentKmat,
                                    'material_added_starts_by' => $change['material_added_starts_by'],
                                    'material_added_number' => $change['material_added_number'],
                                    'material_added_description' => $change['material_added_description'],
                                    'material_deleted_starts_by' => $change['material_deleted_starts_by'],
                                    'material_deleted_number' => $change['material_deleted_number'],
                                    'material_deleted_description' => $change['material_deleted_description'],
                                    'is_accessory' => $change['is_accessory'],
                                    'accessory_release_type' => $change['accessory_release_type'],
                                    'send_to_review_by' => $change['send_to_review_by'],
                                    'created_at' => $change['created'],
                                    'is_revision_change' => $change['is_revision_change']
                                ];
                            }
                        }
                    }
                }
            }
            else {
                $typical = $change['release_item'];

                if (isset($typicalToPanelsMap[$typical])) {
                    foreach ($typicalToPanelsMap[$typical] as $panelData) {
                        $parentKmats = [$change['parent_kmat']];

                        if ($change['operation'] !== 'add' &&
                            $change['material_deleted_starts_by'] !== ':: CTH:' &&
                            $change['material_deleted_starts_by'] !== ':: VTH:') {

                            // Use in-memory data instead of database query
                            $parentKmats = [];
                            foreach ($nachbauData as $row) {
                                if ($row['typical_no'] == $typical &&
                                    $row['ortz_kz'] == $panelData['ortz_kz'] &&
                                    $row['panel_no'] == $panelData['panel_no'] &&
                                    str_contains($row['kmat'], $change['material_deleted_number'])) {

                                    $cleanParentKmat = preg_replace('/^00/', '', $row['parent_kmat']);
                                    $parentKmats[] = $cleanParentKmat;
                                }
                            }

                            if (empty($parentKmats)) {
                                $parentKmats = [$change['parent_kmat']];
                            }
                        }

                        foreach ($parentKmats as $parentKmat) {
                            $finalChanges[] = [
                                'released_dto_type_id' => 1,
                                'dto_number' => $change['dto_number'],
                                'dto_description' => html_entity_decode($change['dto_description']),
                                'affected_dto_numbers' => $change['affected_dto_numbers'],
                                'operation' => $change['operation'],
                                'typical_no' => $typical,
                                'ortz_kz' => $panelData['ortz_kz'],
                                'panel_no' => $panelData['panel_no'],
                                'sap_pos_no' => $panelData['sap_pos_no'],
                                'release_type' => $change['release_type'],
                                'release_quantity' => $change['release_quantity'],
                                'release_unit' => $change['unit'],
                                'parent_kmat' => $parentKmat,
                                'material_added_starts_by' => $change['material_added_starts_by'],
                                'material_added_number' => $change['material_added_number'],
                                'material_added_description' => $change['material_added_description'],
                                'material_deleted_starts_by' => $change['material_deleted_starts_by'],
                                'material_deleted_number' => $change['material_deleted_number'],
                                'material_deleted_description' => $change['material_deleted_description'],
                                'is_accessory' => $change['is_accessory'],
                                'accessory_release_type' => $change['accessory_release_type'],
                                'send_to_review_by' => $change['send_to_review_by'],
                                'created_at' => $change['created'],
                                'is_revision_change' => $change['is_revision_change']
                            ];
                        }
                    }
                } else {
                    returnHttpResponse(500, 'Typical could not found in project : ' . $typical);
                }
            }
        }


        //SPARE CHANGES
        $query = "SELECT id, dto_number, dto_description, accessory_typical_number, release_quantity, release_unit, accessory_parent_kmat, accessory_panel_no, material_added_starts_by, material_added_number, material_added_description, send_to_review_by, spare_dto_type, spare_typical_number, is_revision_change
                  FROM project_works_spare
                  WHERE project_number=:pNo AND nachbau_number=:nNo AND deleted IS NULL";
        $spareChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];

        foreach ($spareChanges as $change) {
            $finalChanges[] = [
                'released_dto_type_id' => 2,
                'dto_number' => $change['dto_number'],
                'dto_description' => html_entity_decode($change['dto_description']),
                'operation' => 'add',
                'typical_no' =>  $change['accessory_typical_number'],
                'ortz_kz' => '',
                'panel_no' => $change['accessory_panel_no'],
                'sap_pos_no' => $typicalToPanelsMap[$change['accessory_typical_number']][0]['sap_pos_no'],
                'release_type' => 'Accessories',
                'release_quantity' => $change['release_quantity'],
                'release_unit' => $change['release_unit'],
                'parent_kmat' => $change['accessory_parent_kmat'],
                'material_added_starts_by' => $change['material_added_starts_by'],
                'material_added_number' => $change['material_added_number'],
                'material_added_description' => $change['material_added_description'],
                'material_deleted_starts_by' => '',
                'material_deleted_number' => '',
                'material_deleted_description' => '',
                'is_accessory' => 1,
                'accessory_release_type' => $change['accessory_release_type'],
                'is_revision_change' => $change['is_revision_change'],
                'send_to_review_by' => $change['send_to_review_by'],
                'spare_dto_type' => $change['spare_dto_type'],
                'spare_typical_no' => $change['spare_typical_number'],
                'spare_project_id' => $change['id']
            ];
        }

        //NACHBAU ERROR CHANGES
        $query = "SELECT id, operation, typical_no, ortz_kz, panel_no, quantity, unit, parent_kmat, material_added_starts_by, material_added_number, material_added_description, material_deleted_starts_by, material_deleted_number, material_deleted_description, is_revision_change, send_to_review_by
                  FROM project_works_nachbau_errors
                  WHERE project_number=:pNo AND nachbau_number=:nNo AND deleted IS NULL";
        $nachbauErrorChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];

        foreach ($nachbauErrorChanges as $change) {

            foreach ($typicalToPanelsMap[$change['typical_no']] as $panel) {
                if ($panel['ortz_kz'] === $change['ortz_kz']) {
                    $sapPosNo = $panel['sap_pos_no'];
                    break;
                }
            }

            $finalChanges[] = [
                'released_dto_type_id' => 5,
                'dto_number' => '',
                'dto_description' => '',
                'operation' => $change['operation'],
                'typical_no' =>  $change['typical_no'],
                'ortz_kz' => $change['ortz_kz'],
                'panel_no' => $change['panel_no'],
                'sap_pos_no' => $sapPosNo,
                'release_type' => 'Panel',
                'release_quantity' => $change['quantity'],
                'release_unit' => $change['unit'],
                'parent_kmat' => $change['parent_kmat'],
                'material_added_starts_by' => $change['material_added_starts_by'],
                'material_added_number' => $change['material_added_number'],
                'material_added_description' => $change['material_added_description'],
                'material_deleted_starts_by' => $change['material_deleted_starts_by'],
                'material_deleted_number' => $change['material_deleted_number'],
                'material_deleted_description' => $change['material_deleted_description'],
                'is_revision_change' => $change['is_revision_change'],
                'send_to_review_by' => $change['send_to_review_by'],
                'is_accessory' => 0,
                'nachbau_error_id' => $change['id']
            ];
        }

        //MINUS CHANGES
        $query = "SELECT dto_number, dto_description, dto_typical_number as typical_no, ortz_kz, panel_no, quantity, unit, parent_kmat, material_deleted_starts_by, material_deleted_number, material_deleted_description, is_revision_change, send_to_review_by 
                  FROM project_works_minus_price 
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND wont_be_produced = 1";
        $minusPriceChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

        foreach ($minusPriceChanges as $change) {

            foreach ($typicalToPanelsMap[$change['typical_no']] as $panel) {
                if ($panel['ortz_kz'] === $change['ortz_kz']) {
                    $sapPosNo = $panel['sap_pos_no'];
                    break;
                }
            }

            $finalChanges[] = [
                'released_dto_type_id' => 4,
                'dto_number' => $change['dto_number'],
                'dto_description' => html_entity_decode($change['dto_description']),
                'operation' => 'delete',
                'typical_no' =>  $change['typical_no'],
                'ortz_kz' => $change['ortz_kz'],
                'panel_no' => $change['panel_no'],
                'sap_pos_no' => $sapPosNo,
                'release_type' => 'Panel',
                'release_quantity' => $change['quantity'],
                'release_unit' => $change['unit'],
                'parent_kmat' => $change['parent_kmat'],
                'material_added_starts_by' => '',
                'material_added_number' => '',
                'material_added_description' => '',
                'material_deleted_starts_by' => $change['material_deleted_starts_by'],
                'material_deleted_number' => $change['material_deleted_number'],
                'material_deleted_description' => $change['material_deleted_description'],
                'is_revision_change' => $change['is_revision_change'],
                'is_accessory' => 0,
                'accessory_release_type' => 0,
                'send_to_review_by' => $change['send_to_review_by']
            ];
        }


        //EXTENSION CHANGES
        $query = "SELECT dto_number, dto_description, operation, typical_no, ortz_kz, panel_no, quantity, unit, parent_kmat, sub_kmat, material_added_starts_by, material_added_number,
                          material_added_description, material_deleted_starts_by, material_deleted_number, material_deleted_description, note, is_accessory, is_revision_change, send_to_review_by
                  FROM project_works_extensions WHERE project_number = :pNo AND nachbau_number = :nNo AND deleted IS NULL";
        $extensionChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

        foreach ($extensionChanges as $change) {

            if ($change['is_accessory'] === '1') {
                $ortzKz = '';
                $note = $change['ortz_kz'];
                $sapPosNo = $typicalToPanelsMap[$change['accessory_typical_number']][0]['sap_pos_no'];
            } else {
                $ortzKz = $change['ortz_kz'];
                $note = '';
                foreach ($typicalToPanelsMap[$change['typical_no']] as $panel) {
                    if ($panel['ortz_kz'] === $change['ortz_kz']) {
                        $sapPosNo = $panel['sap_pos_no'];
                        break;
                    }
                }
            }

            $finalChanges[] = [
                'released_dto_type_id' => 3,
                'dto_number' => $change['dto_number'],
                'dto_description' => html_entity_decode($change['dto_description']),
                'operation' => $change['operation'],
                'typical_no' =>  $change['typical_no'],
                'ortz_kz' => $ortzKz,
                'panel_no' => $change['panel_no'],
                'sap_pos_no' => $sapPosNo,
                'release_type' => $change['is_accessory'] === '1' ? 'Accessories' : 'Panel',
                'release_quantity' => $change['quantity'],
                'release_unit' => $change['unit'],
                'parent_kmat' => !empty($change['sub_kmat']) ? $change['sub_kmat'] : $change['parent_kmat'],
                'material_added_starts_by' => $change['material_added_starts_by'],
                'material_added_number' => $change['material_added_number'],
                'material_added_description' => $change['material_added_description'],
                'material_deleted_starts_by' => $change['material_deleted_starts_by'],
                'material_deleted_number' => $change['material_deleted_number'],
                'material_deleted_description' => $change['material_deleted_description'],
                'is_accessory' => $change['is_accessory'],
                'is_revision_change' => $change['is_revision_change'],
                'accessory_release_type' => $change['is_accessory'],
                'send_to_review_by' => $change['send_to_review_by'],
                'note' => $note,
                'extension_extra_note' => $change['note']
            ];
        }


        //INTERCHANGIBILITY CHANGES
        $query = "SELECT dto_number, dto_description, operation, typical_no, quantity, parent_kmat, quantity, unit, material_added_starts_by, material_added_number, material_added_description, 
                         material_deleted_starts_by, material_deleted_number, material_deleted_description, is_revision_change, send_to_review_by
                  FROM project_works_interchange WHERE project_number = :pNo AND nachbau_number = :nNo AND deleted IS NULL";
        $interchangeDtoChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

        foreach ($interchangeDtoChanges as $change) {
            // Sadece tipik bazlı çalışıldığı için db de tipik bazlı insert işlemi var. Değişiklikler altındaki her panoya uygulanmalı.
            $typical = $change['typical_no'];

            if (isset($typicalToPanelsMap[$typical])) {
                foreach ($typicalToPanelsMap[$typical] as $panelData) {

                    $finalChanges[] = [
                        'released_dto_type_id' => 6,
                        'dto_number' => $change['dto_number'],
                        'dto_description' => html_entity_decode($change['dto_description']),
                        'operation' => $change['operation'],
                        'typical_no' => $typical,
                        'ortz_kz' => $panelData['ortz_kz'],
                        'panel_no' => $panelData['panel_no'],
                        'sap_pos_no' => $panelData['sap_pos_no'],
                        'release_type' => 'Typical',
                        'release_quantity' => $change['quantity'],
                        'release_unit' => $change['unit'],
                        'parent_kmat' => $change['parent_kmat'],
                        'material_added_starts_by' => $change['material_added_starts_by'],
                        'material_added_number' => $change['material_added_number'],
                        'material_added_description' => $change['material_added_description'],
                        'material_deleted_starts_by' => $change['material_deleted_starts_by'],
                        'material_deleted_number' => $change['material_deleted_number'],
                        'material_deleted_description' => $change['material_deleted_description'],
                        'is_accessory' => 0,
                        'accessory_release_type' => 0,
                        'is_revision_change' => $change['is_revision_change'],
                        'send_to_review_by' => $change['send_to_review_by']
                    ];
                }
            } else {
                returnHttpResponse(500, 'Typical could not found in project : ' . $typical);
            }
        }

        //SPECIAL DTO CHANGES FOR AKD IMPORT
        $query = "SELECT * FROM project_works_special_dtos 
                  WHERE project_number = :pNo AND nachbau_number = :nNo AND deleted IS NULL";
        $specialDtoChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];


        if ($change['is_accessory'] === '1') {
            $sapPosNo = $typicalToPanelsMap[$change['accessory_typical_number']][0]['sap_pos_no'];
        } else {
            foreach ($typicalToPanelsMap[$change['typical_no']] as $panel) {
                if ($panel['ortz_kz'] === $change['ortz_kz']) {
                    $sapPosNo = $panel['sap_pos_no'];
                    break;
                }
            }
        }

        foreach ($specialDtoChanges as $change) {
            if ($change['is_accessory'] === '1') {
                $releaseType = 'Accessories';
                $typicalNo = $change['accessory_typical_number'];
                $ortzKz = '';
                $panelNo = $this->getAccessoryKmatObjectOfProject($projectNo)['panel_no'];
                $sapPosNo = $typicalToPanelsMap[$change['accessory_typical_number']][0]['sap_pos_no'];
                $parentKmat = $change['accessory_parent_kmat'];
                $materialAddedStartsBy = $this->getSapMaterialPrefixByMaterialNo($change['material_number']);
                $materialAdded = $change['material_number'];
                $materialAddedDesc = $change['material_description'];
                $materialDeletedStartsBy = '';
                $materialDeleted = '';
                $materialDeletedDesc = '';
            } else {
                $releaseType = 'Panel';
                $typicalNo = $change['typical_no'];
                $ortzKz = $change['ortz_kz'];
                $panelNo = $change['panel_no'];

                foreach ($typicalToPanelsMap[$change['typical_no']] as $panel) {
                    if ($panel['ortz_kz'] === $change['ortz_kz']) {
                        $sapPosNo = $panel['sap_pos_no'];
                        break;
                    }
                }

                $parentKmat = $change['wda_parent_kmat'];
                $materialAddedStartsBy = '';
                $materialAdded = '';
                $materialAddedDesc = '';
                $materialDeletedStartsBy = $this->getSapMaterialPrefixByMaterialNo($change['material_number']);
                $materialDeleted = $change['material_number'];
                $materialDeletedDesc = $change['material_description'];
            }

            $finalChanges[] = [
                'released_dto_type_id' => 7,
                'dto_number' => $change['dto_number'],
                'dto_description' => html_entity_decode($change['dto_description']),
                'operation' => $change['operation'],
                'typical_no' => $typicalNo,
                'ortz_kz' => $ortzKz,
                'panel_no' => $panelNo,
                'sap_pos_no' => $sapPosNo,
                'release_type' => $releaseType,
                'release_quantity' => $change['release_quantity'],
                'release_unit' => $change['release_unit'],
                'parent_kmat' => $parentKmat,
                'material_added_starts_by' => $materialAddedStartsBy,
                'material_added_number' => $materialAdded,
                'material_added_description' => $materialAddedDesc,
                'material_deleted_starts_by' => $materialDeletedStartsBy,
                'material_deleted_number' => $materialDeleted,
                'material_deleted_description' => $materialDeletedDesc,
                'is_accessory' => $change['is_accessory'],
                'accessory_release_type' => $change['is_accessory'],
                'send_to_review_by' => $change['send_to_review_by']
            ];
        }


        // If project is in any revision status
        // REVISION (SKIPPED WORKS) CHANGES
        if (in_array($projectStatusId, ['7', '8', '9'])) {
            $query = "SELECT *
                  FROM nachbau_transfer_skipped_works
                  WHERE project_number = :pNo
                    AND transfer_to_nachbau = :nNo
                    AND active = 1";
            $revisionChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [
                ':pNo' => $projectNo,
                ':nNo' => $nachbauNo
            ])['data'];

            foreach ($revisionChanges as $change) {
                // Panel sap_pos_no bul
                $sapPosNo = null;
                if (!empty($change['typical_no']) && isset($typicalToPanelsMap[$change['typical_no']])) {
                    foreach ($typicalToPanelsMap[$change['typical_no']] as $panel) {
                        if ($panel['ortz_kz'] === $change['ortz_kz']) {
                            $sapPosNo = $panel['sap_pos_no'];
                            break;
                        }
                    }
                }

                // Revizyon satırı oluştur
                $finalChanges[] = [
                    'released_dto_type_id' => $change['released_dto_type_id'], // orijinal değer korunuyor
                    'dto_number' => $change['dto_number'],
                    'dto_description' => html_entity_decode($change['dto_description']),
                    'operation' => $change['operation'],
                    'typical_no' => $change['typical_no'],
                    'ortz_kz' => $change['ortz_kz'],
                    'panel_no' => $change['panel_no'],
                    'sap_pos_no' => $sapPosNo,
                    'release_type' => 'Revision',
                    'release_quantity' => $change['quantity'],
                    'release_unit' => $change['unit'],
                    'parent_kmat' => $change['parent_kmat'],
                    'material_added_starts_by' => $change['material_added_starts_by'],
                    'material_added_number' => $change['material_added_number'],
                    'material_added_description' => $change['material_added_description'],
                    'material_deleted_starts_by' => $change['material_deleted_starts_by'],
                    'material_deleted_number' => $change['material_deleted_number'],
                    'material_deleted_description' => $change['material_deleted_description'],
                    'accessory_release_type' => 0,
                    'send_to_review_by' => $change['send_to_review_by'],

                    // Yeni ek alanlar
                    'skip_reason' => $change['skip_reason'],
                    'is_revision' => true,
                    'revision_created_by' => $change['send_to_review_by'],
                    'revision_created_at' => $change['created_at']
                ];
            }
        }

        ob_start("ob_gzhandler");

        header("Content-Encoding: gzip");
        header("Content-Type: application/json");

        return $finalChanges;
    }

    public function insertReleaseProjectAndOrderChangeData() {
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $projectStatusId = $_POST['projectStatusId'];
        $assemblyStart = $_POST['assemblyStart'];
        $isRevisionNachbau = $_POST['isRevisionNachbau'] === '1';
        $projectInfoData = $_POST['projectInfoData'][0];
        $projectId = $this->getDtoConfiguratorProjectId($projectNo, $nachbauNo);

        $checklistStatus = $this->getProjectChecklistStatus($projectId, $projectNo);

        if (!$checklistStatus['is_complete']) {
            echo json_encode([
                'success' => false,
                'message' => 'Checklist incomplete',
                'checklist_status' => $checklistStatus
            ]);
            exit();
        }

        $query = "SELECT released_project_id, submission_status, submission_status_name, reviewed_by
                  FROM view_released_projects
                  WHERE project_id = :projectId
                  ORDER BY released_project_id DESC
                  LIMIT 1";
        $latestSubmission = DbManager::fetchPDOQueryData('dto_configurator', $query, [':projectId' => $projectId])['data'][0];


        if(!empty($latestSubmission) && !$isRevisionNachbau) {
            if ($latestSubmission['submission_status'] === '3')
                returnHttpResponse(400, "This project and nachbau are already in pending approval status.");
            else if ($latestSubmission['submission_status'] === '5')
                returnHttpResponse(400, "This project and transfer have already been approved by " . $latestSubmission['reviewed_by'] . '.');
        }

        $query = "SELECT id FROM view_released_order_changes WHERE released_project_id = :pId";
        $activeOrderChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pId' => $latestSubmission['released_project_id']])['data'];

        if(!empty($activeOrderChanges))
            returnHttpResponse(400, "This project has active order changes that sent to approval. Please contact with DGT Team.");

        $orderChangeData = $this->prepareReleaseOrderChanges($projectNo, $nachbauNo, $projectStatusId);

        // Checking if order changes have zenf type materials.
        $this->checkIfOrderChangesHaveZENFTypeMaterials($orderChangeData);

        $query = "INSERT INTO released_projects(project_id, project_name, nachbau_date, product_name, assembly_start_date, panel_quantity, rated_voltage, rated_short_circuit, rated_current, electrical_engineer, order_manager, mechanical_engineer, submission_status, submitted_by, submitted_date)";
        $params[] = [$projectId, $projectInfoData['projectName'], $projectInfoData['nachbauDate'], $projectInfoData['product'], $assemblyStart, $projectInfoData['panelQty'], $projectInfoData['ratedVoltage'], $projectInfoData['ratedShortCircuit'], $projectInfoData['ratedCurrent'], $projectInfoData['electricalEngineer'], $projectInfoData['orderManager'], $projectInfoData['mechanicalEngineer'], $isRevisionNachbau ? 8 : 3, SharedManager::$fullname, date('Y-m-d H:i:s')];
        $responseInsert = DbManager::fetchInsert('dto_configurator', $query, $params);
        $lastInsertedId = $responseInsert["pdoConnection"]->lastInsertId();

        if(count($orderChangeData) !== 0) {

            $insertQuery = "INSERT INTO released_order_changes(released_project_id, released_dto_type_id, dto_number, dto_description, affected_dto_numbers, operation, typical_no, ortz_kz, panel_no, sap_pos_no, release_type, 
                                                               release_quantity, release_unit, parent_kmat, material_added_starts_by, material_added_number, material_added_description, 
                                                               material_deleted_starts_by, material_deleted_number, material_deleted_description, is_accessory, is_revision_change, send_to_review_by, note, active) VALUES ";

            $releasedOrderChanges = [];
            foreach ($orderChangeData as $index => $row) {

                if ($row['release_type'] === 'Revision')
                    continue;

                $releasedProjectIdKey = ":released_project_id$index";
                $releasedDtoTypeIdKey = ":released_dto_type_id$index";
                $dtoNumberKey = ":dto_number$index";
                $dtoDescriptionKey = ":dto_description$index";
                $affectedDtoNumbersKey = ":affected_dto_numbers$index";
                $operationKey = ":operation$index";
                $typicalNoKey = ":typical_no$index";
                $ortzKzKey = ":ortz_kz$index";
                $panelNoKey = ":panel_no$index";
                $sapPosNoKey = ":sap_pos_no$index";
                $releaseTypeKey = ":release_type$index";
                $releaseQuantityKey = ":release_quantity$index";
                $releaseUnitKey = ":release_unit$index";
                $parentKmatKey = ":parent_kmat$index";
                $materialAddedStartsByKey = ":material_added_starts_by$index";
                $materialAddedNumberKey = ":material_added_number$index";
                $materialAddedDescKey = ":material_added_description$index";
                $materialDeletedStartsByKey = ":material_deleted_starts_by$index";
                $materialDeletedNumberKey = ":material_deleted_number$index";
                $materialDeletedDescKey = ":material_deleted_description$index";
                $isAccessoryKey = ":is_accessory$index";
                $isRevisionChangeKey = ":is_revision_change$index";
                $sendToReviewByKey = ":send_to_review_by$index";
                $noteKey = ":note$index";
                $activeKey = ":active$index";

                $releasedOrderChanges[] = [
                    $releasedProjectIdKey => $lastInsertedId,
                    $releasedDtoTypeIdKey => $row['released_dto_type_id'],
                    $dtoNumberKey => $row['dto_number'],
                    $dtoDescriptionKey => $row['dto_description'],
                    $affectedDtoNumbersKey => $row['affected_dto_numbers'],
                    $operationKey => $row['operation'],
                    $typicalNoKey => $row['typical_no'],
                    $ortzKzKey => $row['ortz_kz'],
                    $panelNoKey => $row['panel_no'],
                    $sapPosNoKey => $row['sap_pos_no'],
                    $releaseTypeKey => $row['release_type'],
                    $releaseQuantityKey => $row['release_quantity'],
                    $releaseUnitKey => $row['release_unit'],
                    $parentKmatKey => $row['parent_kmat'],
                    $materialAddedStartsByKey => $row['material_added_starts_by'],
                    $materialAddedNumberKey => $row['material_added_number'],
                    $materialAddedDescKey => $row['material_added_description'],
                    $materialDeletedStartsByKey => $row['material_deleted_starts_by'],
                    $materialDeletedNumberKey => $row['material_deleted_number'],
                    $materialDeletedDescKey => $row['material_deleted_description'],
                    $isAccessoryKey => $row['is_accessory'],
                    $isRevisionChangeKey => $row['is_revision_change'],
                    $sendToReviewByKey => $row['send_to_review_by'],
                    $noteKey => $row['note'] ?? null,
                    $activeKey => 1
                ];

                $insertQuery .= "($releasedProjectIdKey, $releasedDtoTypeIdKey, $dtoNumberKey, $dtoDescriptionKey, $affectedDtoNumbersKey, $operationKey, $typicalNoKey, $ortzKzKey, $panelNoKey, $sapPosNoKey, $releaseTypeKey,
                                    $releaseQuantityKey, $releaseUnitKey, $parentKmatKey, $materialAddedStartsByKey, $materialAddedNumberKey, $materialAddedDescKey, $materialDeletedStartsByKey,
                                    $materialDeletedNumberKey, $materialDeletedDescKey, $isAccessoryKey, $isRevisionChangeKey, $sendToReviewByKey, $noteKey, $activeKey),";
            }

            $insertQuery = rtrim($insertQuery, ',');
            DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $releasedOrderChanges, [], false);
        }

        if($isRevisionNachbau){
            $query = "UPDATE projects SET who_send_approval = :sentBy, send_review_date = :date, who_send_revision = :whoSendRev, revision_send_review_date = :revSendRevDate, project_status = 8 WHERE id = :projectId";
            DbManager::fetchPDOQueryData('dto_configurator', $query, [':sentBy' => SharedManager::$fullname, ':date' => date('Y-m-d H:i:s'), ':whoSendRev' => SharedManager::$fullname, ':revSendRevDate' => date('Y-m-d H:i:s'), ':projectId' => $projectId])['data'];
        }
        else {
            $query = "UPDATE projects SET who_send_approval = :sentBy, send_review_date = :date, project_status = 3 WHERE id = :projectId";
            DbManager::fetchPDOQueryData('dto_configurator', $query, [':sentBy' => SharedManager::$fullname, ':date' => date('Y-m-d H:i:s'), ':projectId' => $projectId])['data'];
        }

         $this->sendPublishEmail($lastInsertedId, $projectNo, $nachbauNo, $assemblyStart, $projectInfoData, $isRevisionNachbau);

        echo json_encode([
            'success' => true,
            'message' => 'Publish request completed'
        ]);
        exit();
    }

    public function checkIfOrderChangesHaveZENFTypeMaterials($orderChangeData){
        $addedNumbers = [];
        $deletedNumbers = [];
        foreach ($orderChangeData as $change) {
            // Add concatenated material_added if both parts exist
            if (!empty($change['material_added_starts_by']) && !empty($change['material_added_number'])) {
                $addedNumbers[] = $change['material_added_starts_by'] . $change['material_added_number'];
            }

            // Add concatenated material_deleted if both parts exist AND starts_by is not VTH or CTH
            if (!empty($change['material_deleted_starts_by']) && !empty($change['material_deleted_number'])) {
                // Exclude if material_deleted_starts_by contains VTH or CTH
                if ($change['material_deleted_starts_by'] !== ':: VTH:' && $change['material_deleted_starts_by'] !== ':: CTH:') {
                    $deletedNumbers[] = $change['material_deleted_starts_by'] . $change['material_deleted_number'];
                }
            }
        }

        $allMaterialNumbers = array_values(array_unique(array_merge($addedNumbers, $deletedNumbers)));

        // Yayınlanmaya çalışan bom değişikliklerinde, SAP de ZENF olarak bulunan malzeme var mı kontrolü.
        $notDefinedMaterials = [];
        $apiErrors = [];
        foreach ($allMaterialNumbers as $materialNumber) {
            $materialDetail = $this->getMaterialDetail($materialNumber);

            if ($materialDetail === false) {
                $apiErrors[] = $materialNumber;
                continue;
            }

            if (!empty($materialDetail) && isset($materialDetail['MATL_TYPE']) && $materialDetail['MATL_TYPE'] === 'ZENF') {
                $notDefinedMaterials[] = $materialNumber;
            }
        }

        if (!empty($apiErrors)) {
            returnHttpResponse(500, "Possible WebApps 500 Error: " . implode(', ', $apiErrors));
        }

        if (!empty($notDefinedMaterials)) {
            echo json_encode([
                'success' => false,
                'message' => 'Materials with ZENF type in SAP found.',
                'not_defined_materials' => $notDefinedMaterials
            ]);
            exit();
        }
    }

    public function sendPublishEmail($releasedProjectId, $projectNo, $nachbauNo, $assemblyStart, $projectInfoData, $isRevision) {
        $revisionPrefix = $isRevision ? "🔄 REVISION - " : "";
        $subject = $revisionPrefix . "🔔 [ACTION REQUIRED] " . $projectNo . " - " .  $nachbauNo . " : Project Publish Approval Required via DTO Configurator System";

        $revisionBadge = $isRevision ? "<div style='display: inline-block; background-color: #fbbf24; color: #78350f; padding: 8px 16px; border-radius: 6px; font-weight: bold; margin-bottom: 15px;'>🔄 REVISION</div><br>" : "";

        $bodyContent = "
        <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                {$revisionBadge}
                <h2 style='color: #1e3a8a; margin: 0 0 10px 0; font-size: 24px;'>
                    🔔 Approval Required : $projectNo - $nachbauNo
                </h2>
                <p style='margin: 0; font-size: 16px; color: #6b7280;'>
                    A new project has been submitted via the DTO Configurator System and requires your immediate approval.
                </p>
            </div>
            
            <div style='background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                <div style='background-color: #1e3a8a; color: white; padding: 15px;'>
                    <h3 style='margin: 0; font-size: 18px;'>📋 Project Information</h3>
                </div>
                
                <table style='border-collapse: collapse; border-spacing: 0; width: 100%;'>
                    <tbody>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold; width: 35%;'>
                                Project Number
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['projectNumber']) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Project Name
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['projectName']) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Nachbau Number
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['nachbauNumber']) . "
                            </td>
                        </tr>
                        " . ($isRevision ? "
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Type
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                <span style='background-color: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-weight: bold;'>
                                    🔄 REVISION
                                </span>
                            </td>
                        </tr>
                        " : "") . "
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Product
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['product']) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Panel Quantity
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['panelQty']) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Nachbau Date
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['nachbauDate']) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Assembly Start Date
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($assemblyStart) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Total Order Change Rows
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;color:green; font-weight: bold;'>
                                " . htmlspecialchars($projectInfoData['totalChanges']) . "
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style='background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                <div style='background-color: #059669; color: white; padding: 15px;'>
                    <h3 style='margin: 0; font-size: 18px;'>⚡ Technical Specifications</h3>
                </div>
                
                <table style='border-collapse: collapse; border-spacing: 0; width: 100%;'>
                    <tbody>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold; width: 35%;'>
                                Rated Voltage
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['ratedVoltage']) . "kV
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Rated Current
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['ratedCurrent']) . "A
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Rated Short Circuit
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['ratedShortCircuit']) . "kA
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style='background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                <div style='background-color: #dc2626; color: white; padding: 15px;'>
                    <h3 style='margin: 0; font-size: 18px;'>👥 Project Engineers</h3>
                </div>
                
                <table style='border-collapse: collapse; border-spacing: 0; width: 100%;'>
                    <tbody>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold; width: 35%;'>
                                Electrical Engineer
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['electricalEngineer']) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Mechanical Engineer
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['mechanicalEngineer']) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Order Manager
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['orderManager']) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Submitted By
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars(SharedManager::$fullname) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Submission Date
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . date('d.m.Y H:i:s') . "
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style='background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin-bottom: 20px;'>
                <h3 style='color: #92400e; margin: 0 0 10px 0; font-size: 16px;'>
                    ⚠️ Action Required
                </h3>
                <p style='margin: 0; color: #92400e;padding:20px;'>
                    This project is now in <strong>Pending Approval</strong> status and requires your immediate review and approval.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='https://onex.siemens.com/dpm/dtoconfigurator/core/admin/order-changes.php?released-project-id={$releasedProjectId}' style='display: inline-block; background-color: #1e3a8a; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;'>
                    🔍 Review Project Details
                </a>
            </div>
            
            <div style='background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin-top: 20px;'>
                <p style='margin: 0; font-size: 14px; color: #6b7280; text-align: center;'>
                    This is an automated notification from the <b>DTO Configurator System</b>.<br>
                    Please do not reply to this email.
                </p>
            </div>
        </div>
        ";

        MailManager::sendMail($subject, $bodyContent, 35, "dto_conf_send_publish_request");

        $confirmationSubject = $revisionPrefix . $projectNo . " - " .  $nachbauNo . " : Project Publish Request Submitted Successfully";

        $confirmationRevisionBadge = $isRevision ? "<div style='display: inline-block; background-color: #fbbf24; color: #78350f; padding: 8px 16px; border-radius: 6px; font-weight: bold; margin-bottom: 15px;'>🔄 REVISION</div><br>" : "";

        $confirmationBodyContent = "
        <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
            <div style='background-color: #d1fae5; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                {$confirmationRevisionBadge}
                <h2 style='color: #059669; margin: 0 0 10px 0; font-size: 24px;'>
                    ✅ Project Submitted Successfully
                </h2>
                <p style='margin: 0; font-size: 16px; color: #065f46;'>
                    Your project <strong>" . htmlspecialchars($projectNo) . "</strong> - <strong>" . htmlspecialchars($nachbauNo) . "</strong> - <strong>" . htmlspecialchars($projectInfoData['projectName']) . "</strong> has been successfully submitted for approval.
                </p>
            </div>
            
            <div style='background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                <div style='background-color: #059669; color: white; padding: 15px;'>
                    <h3 style='margin: 0; font-size: 18px;'>📊 Submission Summary</h3>
                </div>
                
                <table style='border-collapse: collapse; border-spacing: 0; width: 100%;'>
                    <tbody>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold; width: 35%;'>
                                Project Number
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectNo). "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold; width: 35%;'>
                                Nachbau Number
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($nachbauNo) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Project Name
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectInfoData['projectName']) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Current Status
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                <span style='background-color: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-weight: bold;'>
                                    ⏳ Pending Approval
                                </span>
                            </td>
                        </tr>
                        " . ($isRevision ? "
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Type
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                <span style='background-color: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-weight: bold;'>
                                    🔄 REVISION
                                </span>
                            </td>
                        </tr>
                        " : "") . "
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Submitted By
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars(SharedManager::$fullname) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Submission Date & Time
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . date('d.m.Y H:i:s') . "
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style='background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin-top: 20px;'>
                <p style='margin: 0; font-size: 14px; color: #6b7280; text-align: center;'>
                    This is an automated confirmation from the <b>DTO Configurator System</b>.<br>
                    Please do not reply to this email. For assistance, contact Digital Transformation team.
                </p>
            </div>
        </div>
        ";

        $sentByEmail = SharedManager::$email;
        MailManager::sendMail($confirmationSubject, $confirmationBodyContent, 35, "dto_conf_send_publish_request_confirmation", [], [$sentByEmail]);
    }

    public function withdrawPublishRequest() {
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | User : " . SharedManager::$fullname . " Sent Withdraw Request From " . $projectNo . " and Nachbau " . $nachbauNo . " | " . implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | User : " . SharedManager::$fullname . " Sent Withdraw Request From " . $projectNo . " and Nachbau " . $nachbauNo . " | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_PROCESSING, implode(' | ', $_POST), "Withdraw Project Publish Request");

        // Step 1: Select release project data that published latest.
        $query = "SELECT * FROM view_released_projects 
                  WHERE project_number = :pNo AND nachbau_number = :nNo 
                  ORDER BY released_project_id DESC LIMIT 1";
        $releasedProjectData = DbManager::fetchPDOQueryData('dto_configurator', $query,[':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'][0] ?? [];

        if(count($releasedProjectData) === 0)
            returnHttpResponse(400, "Project with number " . $projectNo . " to be withdrawn could not be found in released projects.");

        // Step 2: If released project is already published (not pending approval or rejected), we need to remove bom excel and akd xml files inside the directory.
        $releasedProjectDocs = [];
        if ($releasedProjectData['submission_status'] === '5') {
            $query = "SELECT bom_document_name, akd_document_name FROM released_projects_documents WHERE released_project_id = :releasedProjectId AND active = 1";
            $releasedProjectDocs = DbManager::fetchPDOQueryData('dto_configurator', $query,[':releasedProjectId' => $releasedProjectData['released_project_id']])['data'][0] ?? [];

            $bomFolderPath = SharedManager::getProjectFilePath($projectNo) . "\\03. Mekanik";
            $akdFolderPath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\OrderProcessingCenter\\02_OPC_OM_1_2\\Boom_Change\\AKD Import Kontrol\\XML";
            $bomRemovedFolderPath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Digital_Transformation\\DTO_Configurator_Files\\Withdrawn_Bom_Excels";
            $akdRemovedFolderPath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Digital_Transformation\\DTO_Configurator_Files\\Withdrawn_Akd_Xmls";

            $errors = [];

            // Process BOM file
            if (!empty($releasedProjectDocs['bom_document_name'])) {
                $bomFileName = $releasedProjectDocs['bom_document_name'] . '.xlsx';
                $bomFilePath = $bomFolderPath . DIRECTORY_SEPARATOR . $bomFileName;

                if (file_exists($bomFilePath)) {
                    // Create backup filename with _REMOVED suffix
                    $bomBackupFileName = $releasedProjectDocs['bom_document_name'] . '_REMOVED.xlsx';
                    $bomBackupFilePath = $bomRemovedFolderPath . DIRECTORY_SEPARATOR . $bomBackupFileName;

                    // Copy to backup folder first
                    if (copy($bomFilePath, $bomBackupFilePath)) {
                        // Change file permissions to writable before deletion
                        if (chmod($bomFilePath, 0666)) {
                            clearstatcache();

                            // Try to delete - if it fails, just continue (don't add to errors)
                            if (!unlink($bomFilePath)) {
                                // File is probably locked/open - just log it but don't fail
                                error_log("Warning: Could not delete BOM file (possibly in use): " . $bomFileName);
                                $this->sendDeleteFileErrorMail($bomFileName, 'BOM', $releasedProjectData);
                            }
                        }
                    } else {
                        $errors[] = "Failed to create backup copy of BOM file: " . $bomFileName;
                    }
                }
            }

            // Process AKD file
            if (!empty($releasedProjectDocs['akd_document_name'])) {
                $akdFileName = $releasedProjectDocs['akd_document_name'] . '.xml';
                $akdFilePath = $akdFolderPath . DIRECTORY_SEPARATOR . $akdFileName;

                if (file_exists($akdFilePath)) {
                    // Create backup filename with _REMOVED suffix
                    $akdBackupFileName = $releasedProjectDocs['akd_document_name'] . '_REMOVED.xml';
                    $akdBackupFilePath = $akdRemovedFolderPath . DIRECTORY_SEPARATOR . $akdBackupFileName;

                    // Copy to backup folder first
                    if (copy($akdFilePath, $akdBackupFilePath)) {
                        // Change file permissions to writable before deletion
                        if (chmod($akdFilePath, 0666)) {
                            clearstatcache();

                            // Try to delete - if it fails, just continue (don't add to errors)
                            if (!unlink($akdFilePath)) {
                                // File is probably locked/open - just log it but don't fail
                                error_log("Warning: Could not delete AKD file (possibly in use): " . $akdFileName);
                                $this->sendDeleteFileErrorMail($akdFileName, 'AKD', $releasedProjectData);
                            }
                        }
                    } else {
                        $errors[] = "Failed to create backup copy of AKD file: " . $akdFileName;
                    }
                }
            }

            if (!empty($errors)) {
                returnHttpResponse(500, "File operation errors: " . implode(", ", $errors));
            }

            $query = "UPDATE released_projects_documents SET active = 0 WHERE released_project_id = :releasedProjectId AND active = 1";
            DbManager::fetchPDOQueryData('dto_configurator', $query, [':releasedProjectId' => $releasedProjectData['released_project_id']])['data'];
        }

        // Step 3: Order Changes of Released Project are setting to inactive.
        $query = "SELECT released_project_id FROM view_released_order_changes WHERE project_number = :pNo AND nachbau_number = :nNo AND active = 1 LIMIT 1";
        $latestReleaseIdWithOrderChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'][0]['released_project_id'];
        $query = "UPDATE released_order_changes SET active = 0 WHERE released_project_id = :releasedProjectId";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':releasedProjectId' => $latestReleaseIdWithOrderChanges])['data'];

        // Step 4: As we need to keep all steps, insert new released project with status of WITHDRAWN
        $query = "INSERT INTO released_projects(project_id, project_name, nachbau_date, product_name, assembly_start_date, panel_quantity, rated_voltage, rated_short_circuit, rated_current, electrical_engineer, order_manager, mechanical_engineer, submission_status, submitted_by, submitted_date)";
        $params[] = [
            $releasedProjectData['project_id'],
            $releasedProjectData['project_name'],
            $releasedProjectData['nachbau_date'],
            $releasedProjectData['product_name'],
            $releasedProjectData['assembly_start_date'],
            $releasedProjectData['panel_quantity'],
            $releasedProjectData['rated_voltage'],
            $releasedProjectData['rated_short_circuit'],
            $releasedProjectData['rated_current'],
            $releasedProjectData['electrical_engineer'],
            $releasedProjectData['order_manager'],
            $releasedProjectData['mechanical_engineer'],
            6,
            $releasedProjectData['submitted_by'],
            $releasedProjectData['submitted_date']
        ];
        DbManager::fetchInsert('dto_configurator', $query, $params);

        // Step 5: Update Project Status and Dates in DTO Configurator 'projects' table.
        $query = "UPDATE projects SET project_status = 6 WHERE id = :projectId";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':projectId' => $releasedProjectData['project_id']])['data'];

        // Step 6: Send email to inform that user has withdrawn the publish request.
        $this->sendWithdrawPublishRequestEmail($releasedProjectData, $releasedProjectDocs);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Withdraw Request Successful With Following Parameters" . implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Withdraw Request Successful With Following Parameters" . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_PROJECT_WORK, ACTION_CREATED, implode(' | ', $_POST), "Withdraw Project Publish Request");
    }

    public function sendWithdrawPublishRequestEmail($releasedProjectData, $releasedProjectDocs) {
        $emailSubject = "Project Publish Request Withdrawn - " . $releasedProjectData['project_number'] . ' - ' . $releasedProjectData['nachbau_number'];

        $emailBody = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                    .content { padding: 15px; }
                    .project-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
                    .project-details h3 { margin-top: 0; color: #495057; }
                    .detail-row { margin: 8px 0; }
                    .label { font-weight: bold; color: #495057; }
                    .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; font-size: 12px; color: #6c757d; }
                    .withdrawn-status { color: #dc3545; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Withdrawn Project Publish Request</h2>
                    </div>
                    
                    <div class='content'>
                        <p>Dear Manager,</p>
                        
                        <p>This is to inform you that the publish request for the following project has been <span class='withdrawn-status'>WITHDRAWN</span> by the user.</p>
                        
                        <div class='project-details'>
                            <h3>Project Details:</h3>
                            <div class='detail-row'>
                                <span class='label'>Project Number:</span> " . htmlspecialchars($releasedProjectData['project_number']) . "
                            </div>
                            <div class='detail-row'>
                                <span class='label'>Project Name:</span> " . htmlspecialchars($releasedProjectData['project_name']) . "
                            </div>
                            <div class='detail-row'>
                                <span class='label'>Nachbau Number:</span> " . htmlspecialchars($releasedProjectData['nachbau_number']) . "
                            </div>
                            <div class='detail-row'>
                                <span class='label'>Submitted By:</span> " . htmlspecialchars($releasedProjectData['submitted_by']) . "
                            </div>
                            <div class='detail-row'>
                                <span class='label'>Originally Submitted Date:</span> " . htmlspecialchars($releasedProjectData['submitted_date']) . "
                            </div>
                            <div class='detail-row'>
                                <span class='label'>Withdrawn By:</span> " . htmlspecialchars(SharedManager::$fullname) . "
                            </div>                                    
                            <div class='detail-row'>
                                <span class='label'>Withdrawal Date:</span> " . date('Y-m-d H:i:s') . "
                            </div>
                        </div>
                        
                        <p><strong>Actions Taken:</strong></p>
                        <ul>
                            <li>Project status has been updated to WITHDRAWN</li>
                            <li>Order changes have been deactivated</li>
                        </ul>" .
                        ($releasedProjectData['submission_status'] === '5' ? "
                            <div style='background-color: #fff3cd; border: 2px solid #ffeaa7; border-radius: 8px; padding: 15px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                                <h4 style='color: #856404; margin-top: 0; display: flex; align-items: center;'>
                                    <span style='font-size: 18px; margin-right: 8px;'>⚠️</span>
                                    CRITICAL ACTION PERFORMED
                                </h4>
                                <p style='color: #856404; font-weight: bold; margin: 10px 0;'>
                                    The following files have been <span style='color: #dc3545; text-decoration: underline;'>permanently removed</span> from the system:
                                </p>
                                <ul style='color: #856404; margin: 10px 0 0 20px;'>
                                    <li><strong>BOM Excel File:</strong> " . htmlspecialchars($releasedProjectDocs['bom_document_name'] ?? 'N/A') . "</li>
                                    <li><strong>AKD XML File:</strong> " . htmlspecialchars($releasedProjectDocs['akd_document_name'] ?? 'N/A') . "</li>
                                </ul>
                                <p style='color: #856404; font-size: 12px; margin-top: 15px; font-style: italic;'>
                                    These files were automatically generated during the publish process and have been removed as part of the withdrawal procedure.
                                </p>
                            </div>" : "") . "
                            
                        <p>If you have any questions regarding this withdrawal, please contact the project team.</p>
                        
                        <p>Best regards,<br>
                        DTO Configurator System</p>
                    </div>
                    
                    <div class='footer'>
                        <p>This is an automated notification from the <b>DTO Configurator System</b>. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>
            ";

        MailManager::sendMail($emailSubject, $emailBody, 35, "dto_conf_withdraw_publish_request", [], [], [SharedManager::$email]);
    }

    public function sendDeleteFileErrorMail($fileName, $fileType, $projectData = null) {
        $currentUser = SharedManager::$fullname;
        $currentUserEmail = SharedManager::$email;

        $projectNo = $projectData['project_number'];
        $projectName = $projectData['project_name'];
        $nachbauNo = $projectData['nachbau_number'];

        $subject = "⚠️ ERROR: File Deletion Failed During Project Withdrawal - " . $fileName;

        $bodyContent = "
            <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6; padding: 10px;'>
                <div style='background-color: #fef2f2; border: 2px solid #f87171; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                    <h2 style='color: #dc2626; margin: 0 0 10px 0; font-size: 24px;'>
                        ⚠️ File Deletion Error Alert
                    </h2>
                    <p style='font-size: 16px; color: #991b1b;'>
                        <strong>ATTENTION:</strong> A file could not be deleted during the project withdrawal process.
                    </p>
                    <p style='font-size: 15px; color: #991b1b;'>
                        The file <strong>" . htmlspecialchars($fileName) . "</strong> failed to be deleted from the system.
                    </p>
                </div>
                
                <div style='background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                    <div style='background-color: #dc2626; color: white; padding: 15px;'>
                        <h3 style='margin: 0; font-size: 18px;'>🚨 Error Details</h3>
                    </div>
                    
                    <table style='border-collapse: collapse; border-spacing: 0; width: 100%;'>
                        <tbody>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold; width: 35%;'>
                                    File Name
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($fileName) . "
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    File Type
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($fileType) . " File
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Project Number
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($projectNo) . "
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Project Name
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($projectName) . "
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Nachbau Number
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($nachbauNo) . "
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Operation
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    <span style='background-color: #fef2f2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-weight: bold;'>
                                        Project Withdrawal
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Performed By
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($currentUser) . " (" . htmlspecialchars($currentUserEmail) . ")
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Error Time
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . date('d.m.Y H:i:s') . "
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin-bottom: 20px;'>
                    <h3 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>
                        📋 What Happened?
                    </h3>
                    <ul style='color: #856404; margin: 10px 0 0 20px; padding-left: 0;'>
                        <li style='margin-bottom: 8px;'>✅ <strong>Backup was created successfully</strong> in the withdrawn files folder</li>
                        <li style='margin-bottom: 8px;'>❌ <strong>Original file deletion failed</strong> - possibly because the file is open in another application</li>
                        <li style='margin-bottom: 8px;'>⚠️ <strong>Manual cleanup required</strong> - the file may need to be deleted manually</li>
                    </ul>
                </div>
                
                <div style='background-color: #dbeafe; border: 1px solid #3b82f6; border-radius: 8px; padding: 20px; margin-bottom: 20px;'>
                    <h3 style='color: #1e40af; margin: 0 0 10px 0; font-size: 16px;'>
                        🔧 Recommended Actions
                    </h3>
                    <ol style='color: #1e40af; margin: 10px 0 0 20px; padding-left: 0;'>
                        <li style='margin-bottom: 8px;'>Check if the file is currently open in Excel or another application</li>
                        <li style='margin-bottom: 8px;'>Ask users to close the file if it's being used</li>
                        <li style='margin-bottom: 8px;'>Manually delete the file when it's no longer in use</li>
                        <li style='margin-bottom: 8px;'>Verify the backup file was created in the withdrawn files folder</li>
                    </ol>
                </div>
                
                <div style='background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin-top: 20px;'>
                    <p style='margin: 0; font-size: 14px; color: #6b7280; text-align: center;'>
                        This is an automated error notification from the <b>DTO Configurator System</b>.<br>
                        Please investigate and resolve the file deletion issue when convenient.
                    </p>
                </div>
            </div>
            ";

        MailManager::sendMail($subject, $bodyContent, 35, "dto_conf_file_deletion_error", [], [], [$currentUserEmail]);
    }

    public function getProjectChecklistStatus($projectId, $projectNo) {

        try {
            $productId = $this->getProductIdOfProject($projectNo);

            // Get all checklist items for this product and their completion status
            $statusQuery = "
                SELECT 
                    ci.id as checklist_item_id,
                    ci.detail as checklist_detail,
                    cc.name as category_name,
                    CASE 
                        WHEN cpp.active = 1 THEN 1 
                        ELSE 0 
                    END as is_completed
                FROM checklist_product_match cpm
                INNER JOIN checklist_items ci ON cpm.checklist_item_id = ci.id
                LEFT JOIN checklist_categories cc ON ci.category_id = cc.id
                LEFT JOIN checklist_project_progress cpp ON (
                    cpp.checklist_item_id = ci.id 
                    AND cpp.project_id = :projectId
                )
                WHERE cpm.product_type_id = :productId
                ORDER BY cc.name, ci.detail
            ";

            $items = DbManager::fetchPDOQueryData('dto_configurator', $statusQuery, [
                ':projectId' => $projectId,
                ':productId' => $productId
            ])['data'];

            $totalItems = count($items);
            $completedItems = array_filter($items, function($item) {
                return $item['is_completed'] == 1;
            });
            $completedCount = count($completedItems);

            $incompleteItems = array_filter($items, function($item) {
                return $item['is_completed'] == 0;
            });

            $isComplete = ($totalItems > 0 && $completedCount == $totalItems);

            return [
                'success' => true,
                'is_complete' => $isComplete,
                'total_items' => $totalItems,
                'completed_items' => $completedCount,
                'remaining_items' => $totalItems - $completedCount,
                'incomplete_items' => array_values($incompleteItems) // List of incomplete items
            ];

        } catch (Exception $e) {
            error_log("Error getting checklist status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get checklist status'
            ];
        }
    }

    public function updateProjectRevisionStatus() {
        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $revisionStatus = $_POST['revisionStatus'];

        // Set status to revision start
        $query = "UPDATE projects 
                    SET project_status = :status, working_user = :workingUser, last_updated_by = :workingUser, 
                        last_updated_date = :lastUpdatedDate, revision_start_date = :revisionStarts, is_revision = 1 
                    WHERE project_number = :pNo AND nachbau_number = :nNo";

        $parameters = [
            ':status' => $revisionStatus,
            ':workingUser' => SharedManager::$fullname,
            ':lastUpdatedDate' => (new DateTime())->format('Y-m-d H:i:s'),
            ':revisionStarts' => (new DateTime())->format('Y-m-d H:i:s'),
            ':pNo' => $projectNo,
            ':nNo' => $nachbauNo
        ];

        DbManager::fetchPDOQuery('dto_configurator', $query, $parameters);
    }
}

$controller = new ProjectController($_POST);

$response = match ($_GET['action']) {
    'getProjectData' => $controller->getProjectData(),
    'getProjectInfo' => $controller->getProjectInfo(),
    'getAllProjects' => $controller->getAllProjects(),
    'searchProject' => $controller->searchProject(),
    'getSpareParametersOfProject' => $controller->getSpareParametersOfProject(),
    'getSpareProjectsOfTypical' => $controller->getSpareProjectsOfTypical(),
    'getOverviewOfProjectWork' => $controller->getOverviewOfProjectWork(),
    'getOrderChangesOverviewTableData' => $controller->getOrderChangesOverviewTableData(),
    'getSparePartDtoKmatsOfProject' => $controller->getSparePartDtoKmatsOfProject(),
    'getSelectedSparePartItemsOfDto' => $controller->getSelectedSparePartItemsOfDto(),
    'getBomChangeWithOrderChanges' => $controller->getBomChangeWithOrderChanges(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    'updateProjectWork' => $controller->updateProjectWork(),
    'removeAllProjectWorkData' => $controller->removeAllProjectWorkData(),
    'updateNotDefinedWorkCenters' => $controller->updateNotDefinedWorkCenters(),
    'addMaterialToProject' => $controller->addMaterialToProject(),
    'removeAllSelectionsFromProject' => $controller->removeAllSelectionsFromProject(),
    'removeTypeNumberFromWork' => $controller->removeTypeNumberFromWork(),
    'saveSpareListsAsAccessory' => $controller->saveSpareListsAsAccessory(),
    'removeSpareWorks' => $controller->removeSpareWorks(),
    'addSparePartToSelectedItems' => $controller->addSparePartToSelectedItems(),
    'removeSparePartWork' => $controller->removeSparePartWork(),
    'saveNachbauErrorChanges' => $controller->saveNachbauErrorChanges(),
    'deleteNachbauErrorChange' => $controller->deleteNachbauErrorChange(),
    'checkOutProject' => $controller->checkOutProject(),
    'checkInProject' => $controller->checkInProject(),
    'updateCommonKmatOfDevice' => $controller->updateCommonKmatOfDevice(),
    'insertReleaseProjectAndOrderChangeData' => $controller->insertReleaseProjectAndOrderChangeData(),
    'withdrawPublishRequest' => $controller->withdrawPublishRequest(),
    'updateProjectRevisionStatus' => $controller->updateProjectRevisionStatus(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};