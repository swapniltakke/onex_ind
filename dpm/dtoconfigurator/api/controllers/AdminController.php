<?php
include_once '../../api/controllers/BaseController.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/shared/api/MtoolManager.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class AdminController extends BaseController {

    public function getReleasedProjects() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Released Projects Request From Admin");
        Journals::saveJournal("PROCESSING | Get Released Projects Request From Admin", PAGE_ADMIN, ADMIN_INDEX, ACTION_PROCESSING, '', "Admin Index");

        // Aynı projenin tüm geçmiş hareketleri insert edildiği için, projenin son hareketinin olduğu güncel statusu almak gerekli
        $query = "
                SELECT vrp.* 
                FROM view_released_projects vrp
                INNER JOIN (
                    SELECT project_id, MAX(released_project_id) as max_released_project_id 
                    FROM view_released_projects 
                    GROUP BY project_id
                ) latest ON vrp.project_id = latest.project_id 
                        AND vrp.released_project_id = latest.max_released_project_id
                ORDER BY vrp.released_project_id DESC";

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'];

        foreach($data as &$row)
            $row['contacts'] = MtoolManager::getProjectContacts([$row['project_number']])[0] ?? [];

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Released Projects Request From Admin Successful");
        Journals::saveJournal("RETURNED | Released Projects Request From Admin Successful", PAGE_ADMIN, ADMIN_INDEX, ACTION_VIEWED, '', "Admin Index");
        echo json_encode($data);
        exit();
    }

    public function getReleasedProjectDetailsById() {
        $releasedProjectId = $_GET['releasedProjectId'];

        $query = "SELECT * FROM view_released_projects WHERE released_project_id = :id";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $releasedProjectId])['data'][0];

        echo json_encode($data);
        exit();
    }

    public function getReleasedBomChangeWithOrderChanges() {
        $releasedProjectId = $_GET['releasedProjectId'];

        $query = "SELECT project_number, nachbau_number, submission_status, project_id FROM view_released_projects WHERE released_project_id = :releasedProjectId";
        $releasedProject = DbManager::fetchPDOQueryData('dto_configurator', $query, [':releasedProjectId' => $releasedProjectId])['data'][0];

        // Son submission rejected veya approval ise, eklenen sipariş değişiklikleri ilgili projenin pending approval a gönderilmiş son release_project_id'sinde.
        if (in_array($releasedProject['submission_status'], ['4', '5'])) {
            $query = "SELECT released_project_id FROM view_released_projects 
                        WHERE project_id = :projectId AND submission_status NOT IN ('4', '5') 
                        ORDER BY released_project_id DESC LIMIT 1";
            $releasedProjectId = DbManager::fetchPDOQueryData('dto_configurator', $query, [':projectId' => $releasedProject['project_id']])['data'][0]['released_project_id'];
        }

        // Fetch Order Changes
        $query = "SELECT * FROM view_released_order_changes WHERE released_project_id = :id";
        $orderChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $releasedProjectId])['data'];

        // Fetch Nachbau Data
        $projectNo = $releasedProject['project_number'];
        $nachbauNo = $releasedProject['nachbau_number'];
        $query = "SELECT position, feld_name, typical_no, ortz_kz, panel_no, kmat, qty, unit, kmat_name, parent_kmat, description
                  FROM nachbau_datas 
                  WHERE project_no=:pNo AND nachbau_no=:nNo";
        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];

        $nachbauDtoNames = $this->getNachbauDtoNumbersStartsBy();

        // Pre-process order changes into efficient lookup maps
        $replaceDeleteLookup = []; // [typical_no][ortz_kz][material_deleted_number] = orderChange
        $addLookup = []; // [typical_no][ortz_kz][parent_kmat][] = orderChange

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
                'is_revision_change' => '',
                'send_to_review_by' => '',
                'created_at' => '',
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
                $nachbauRow['is_revision_change'] = $orderChange['is_revision_change'];
                $nachbauRow['is_accessory'] = $orderChange['is_accessory'];
                $nachbauRow['send_to_review_by'] = $orderChange['send_to_review_by'];
                $nachbauRow['created_at'] = $orderChange['created'];
                $nachbauRow['released_dto_type_id'] = $orderChange['released_dto_type_id'];

                // For replace operations, also update the quantity if provided
                if ($orderChange['operation'] === 'replace' && !empty($orderChange['release_quantity'])) {
                    $nachbauRow['type'] = 'order_change_replace';
                    $nachbauRow['release_quantity'] = preg_replace('/\.000$/', '', $orderChange['release_quantity']);
                    $nachbauRow['release_unit'] = $orderChange['release_unit'];
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
                    'is_revision_change' => '',
                    'send_to_review_by' => '',
                    'created_at' => '',
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
                        'is_revision_change' => $orderChange['is_revision_change'],
                        'is_accessory' => $orderChange['is_accessory'],
                        'send_to_review_by' => $orderChange['send_to_review_by'],
                        'created_at' => $orderChange['created'],
                        'released_dto_type_id' => $orderChange['released_dto_type_id'],
                        'is_cable' => false
                    ];

                    // IF SPARE DTO
                    if ($orderChange['released_dto_type_id'] === '2') {
                        $addOperationRow['spare_typical_no'] = $orderChange['spare_typical_no'];
                        $addOperationRow['spare_dto_type'] = $orderChange['spare_dto_type'];
                        $addOperationRow['spare_project_id'] = $orderChange['spare_project_id'];
                    }
                    if ($orderChange['released_dto_type_id'] === '3') {
                        $addOperationRow['note'] = $orderChange['note'];
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

    public function updateProjectStatus() {
        $releasedProjectId = $_POST['releasedProjectId'];
        $status = $_POST['status'];
        $note = $_POST['note'];

        $query = "SELECT * FROM view_released_projects WHERE released_project_id = :released_project_id";
        $releasedProject = DbManager::fetchPDOQueryData('dto_configurator', $query, [':released_project_id' => $releasedProjectId])['data'][0];

        if (empty($releasedProject))
            returnHttpResponse(500, 'Project with ID : ' . $releasedProjectId . ' could not found!');

        $query = "SELECT material_added_number, material_deleted_number 
                  FROM view_released_order_changes WHERE released_project_id = :released_project_id";
        $releasedOrderChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':released_project_id' => $releasedProjectId])['data'] ?? [];

        if (count($releasedOrderChanges) !== 0)
            $this->checkIfAllOrderChangeMaterialsAreSapDefined($releasedOrderChanges);

        // Approve veya Reject olunca yeni satırla insert olacak. Eski hareketlerin tutulması için.
        $statusConfig = [
            'revision_approved' => [
                'submission_status' => 9, // Revision Published (Approved)
                'note_field' => 'approval_notes'
            ],
            'approved' => [
                'submission_status' => 5, // Published (Approved)
                'note_field' => 'approval_notes'
            ],
            'rejected' => [
                'submission_status' => 4, // Rejected
                'note_field' => 'rejection_reason'
            ]
        ];

        if (!isset($statusConfig[$status]))
            returnHttpResponse(500, "Invalid status: $status");

        $config = $statusConfig[$status];

        $query = "INSERT INTO released_projects(
                      project_id, project_name, nachbau_date, product_name, assembly_start_date, 
                      panel_quantity, rated_voltage, rated_short_circuit, rated_current, 
                      electrical_engineer, order_manager, mechanical_engineer, submission_status, 
                      submitted_by, submitted_date, reviewed_by, reviewed_date, {$config['note_field']}
                  )";

        $params[] = [
            $releasedProject['project_id'],
            $releasedProject['project_name'],
            $releasedProject['nachbau_date'],
            $releasedProject['product_name'],
            $releasedProject['assembly_start_date'],
            $releasedProject['panel_quantity'],
            $releasedProject['rated_voltage'],
            $releasedProject['rated_short_circuit'],
            $releasedProject['rated_current'],
            $releasedProject['electrical_engineer'],
            $releasedProject['order_manager'],
            $releasedProject['mechanical_engineer'],
            $config['submission_status'],
            $releasedProject['submitted_by'],
            $releasedProject['submitted_date'],
            SharedManager::$email,
            date('Y-m-d H:i:s'),
            $note
        ];

        $responseInsert = DbManager::fetchInsert('dto_configurator', $query, $params);
        $lastInsertedReleaseProjectId = $responseInsert["pdoConnection"]->lastInsertId();

        // Reject edileceği için, release için gönderilmiş olan bütün satırları pasif hale getir.
        if ($status === 'rejected') {
            $query = "UPDATE released_order_changes SET active = 0 WHERE released_project_id = :releasedProjectId AND active = 1";
            DbManager::fetchPDOQueryData('dto_configurator', $query, [':releasedProjectId' => $releasedProjectId])['data'];
        }

        // Update projects table data
        $updateQuery = "UPDATE projects SET project_status = :statusId, reviewed_date = :reviewDate, review_note = :reviewNote WHERE id = :pId";
        $updateParams = [':statusId' => $config['submission_status'], ':reviewDate' => date('Y-m-d H:i:s'), ':reviewNote' => $note, ':pId' => $releasedProject['project_id']];
        DbManager::fetchPDOQueryData('dto_configurator', $updateQuery, $updateParams)['data'];

        if ($status === 'approved') {
            $query = "INSERT INTO released_projects_documents(released_project_id, project_number, nachbau_number, bom_document_name, akd_document_name, created_by, active)";
            $today = date('Y-m-d');
            $akdFileName = "Delta_{$releasedProject['project_number']}-{$releasedProject['nachbau_number']}-{$today}";
            $bomFileName = 'BOM_CHANGE_' . $releasedProject['project_number'] . '_' . $releasedProject['nachbau_number'] . '_' . date("YmdHi");

            $parameters[] = [
                $lastInsertedReleaseProjectId,
                $releasedProject['project_number'],
                $releasedProject['nachbau_number'],
                $bomFileName,
                $akdFileName,
                SharedManager::$email,
                1
            ];

            DbManager::fetchInsert('dto_configurator', $query, $parameters);
        } else if ($status === 'revision_approved') {
            $query = "INSERT INTO released_projects_documents(released_project_id, project_number, nachbau_number, bom_document_name, akd_document_name, created_by, active)";
            $today = date('Y-m-d');
            $bomFileName = 'REVISION_BOM_CHANGE_' . $releasedProject['project_number'] . '_' . $releasedProject['nachbau_number'] . '_' . date("YmdHi");

            $parameters[] = [
                $lastInsertedReleaseProjectId,
                $releasedProject['project_number'],
                $releasedProject['nachbau_number'],
                $bomFileName,
                null,
                SharedManager::$email,
                1
            ];

            DbManager::fetchInsert('dto_configurator', $query, $parameters);
        }
    }

    public function getDescriptionsOfAffectedDtoNumbers() {
        $affectedDtoResponseObj = [];
        $dtoNumbers = $_GET['dtoNumbers'];

        foreach ($dtoNumbers as $affectedDto) {
            $query = "SELECT LEFT(description, 200) as description FROM tkforms WHERE dto_number = :dtoNumber";
            $description = DbManager::fetchPDOQueryData('dto_configurator', $query, [':dtoNumber' => $affectedDto])['data'][0]['description'] ?? '';
            $affectedDtoResponseObj[$affectedDto] = $description;
        }

        echo json_encode($affectedDtoResponseObj);
        exit();
    }

    public function sendPublishResultMail()
    {
        $releasedProjectId = $_POST['releasedProjectId'];
        $note = $_POST['note'];
        $resultStatus = $_POST['resultStatus']; // approved or rejected
        $isRevision = $_POST['isRevision'] === 'true';

        $query = "SELECT * FROM view_released_projects WHERE released_project_id = :id";
        $releasedProjectData = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $releasedProjectId])['data'][0];

        $projectNo = $releasedProjectData['project_number'] ?? $releasedProjectData['project_id'];
        $nachbauNo = $releasedProjectData['nachbau_number'] ?? $releasedProjectData['nachbau_date'];
        $projectName = $releasedProjectData['project_name'];
        $reviewedBy = SharedManager::$fullname;
        $reviewedByEmail = SharedManager::$email;

        // TODO: GEÇİCİ OLARAK TK TABLOSUNDAN MAİL GETİRİCEM. DİREK MAİLİ BASMAK GEREKİYOR released_projectse
        $query = "SELECT created_email FROM tkforms WHERE created_name = :submittedPersonName LIMIT 1";
        $submittedByEmail = DbManager::fetchPDOQueryData('dto_configurator', $query, [':submittedPersonName' => $releasedProjectData['submitted_by']])['data'][0]['created_email'];
        $submittedDate = (new DateTime($releasedProjectData['submitted_date']))->format('d.m.Y H:i:s');

        if ($resultStatus === 'approved') {
            // APPROVAL EMAIL
            $revisionPrefix = $isRevision ? "🔄 REVISION - " : "";
            $subject = $revisionPrefix . "✅ APPROVED: " . $projectNo . " - " . $projectName . " : DTO Bom Change Delta List Release Approved";
            $mechanicFolderPath = SharedManager::getProjectFilePath($projectNo) . "\\03. Mekanik";
            $xmlFolderPath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\OrderProcessingCenter\\02_OPC_OM_1_2\\Boom_Change\\AKD Import Kontrol\\XML";

            $revisionBadge = $isRevision ? "<div style='display: inline-block; background-color: #fbbf24; color: #78350f; padding: 8px 16px; border-radius: 6px; font-weight: bold; margin-bottom: 15px;'>🔄 REVISION</div><br>" : "";

            $bodyContent = "
            <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;padding:10px;'>
                <!-- Email Compatible Header Section -->
                <table cellpadding='0' cellspacing='0' border='0' width='100%' style='margin-bottom: 20px;'>
                    <tr>
                        <td style='background-color: #d1fae5; padding: 20px;'>
                            {$revisionBadge}
                            <h2 style='color: #059669; margin: 0 0 10px 0; font-size: 24px; font-family: Arial, sans-serif;'>
                                ✅ DTO Bom Change Delta List Has Been Released Successfully
                            </h2>
                            <p style='font-size: 16px; color: #065f46; margin: 0 0 15px 0; font-family: Arial, sans-serif;'>
                                Great news! Project <strong>{$projectNo}</strong> - <strong>{$nachbauNo}</strong> publish request has been <strong>APPROVED</strong> via <strong>DTO Configurator System</strong>.
                            </p>
                            <p style='font-size: 15px; color: #065f46; margin: 0; font-family: Arial, sans-serif;'>
                                BOM Change Excel is created in <span style='color: #059669; text-decoration: underline;'><strong>Mekanik</strong></span> folder.<br>
                                <{$mechanicFolderPath}> <br><br>
                                AKD Import XML file is created in <span style='color: #059669; text-decoration: underline;'><strong>XML</strong></span> folder. <br>
                                <{$xmlFolderPath}>
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Progress Bar Section - Modern UI -->
                <div style='background-color: #1e3a8a; padding: 25px; border-radius: 12px; margin-bottom: 20px;'>
                    <!-- Progress Line Background -->
                    <div style='height: 4px; background-color: #4b5563; border-radius: 2px; margin-bottom: 20px; position: relative;'>
                        <!-- Progress Line Completed (25% for Step 1) -->
                        <div style='width: 25%; height: 4px; background-color: #10b981; border-radius: 2px;'></div>
                    </div>
                    
                    <!-- Steps Container -->
                    <table cellpadding='0' cellspacing='0' border='0' width='100%' style='margin-top: -30px;'>
                        <tr>
                            <!-- Step 1  Bom Change Preparation (PRD) -->
                            <td width='20%' align='center' valign='top'>
                                <div style='width: 48px; height: 48px; background-color: #10b981; color: white; text-align: center; line-height: 48px; font-size: 18px; font-weight: bold; border-radius: 24px; margin: 0px auto 12px auto; border: 3px solid #9ca3af;'>✓</div>
                                <div style='color: #333333; font-size: 14px; font-weight: bold; text-align: center;'>Step 1</div>
                                <div style='color: #10b981; font-size: 13px; text-align: center; margin-top: 2px; font-weight: 600;'>BOM Change Preparation (PRD)</div>
                            </td>
                            
                            <!-- Step 2 - DTO Bom Change Delta List Release (PRD) -->
                            <td width='20%' align='center' valign='top'>
                                <div style='width: 48px; height: 48px; background-color: #10b981; color: white; text-align: center; line-height: 48px; font-size: 18px; font-weight: bold; border-radius: 24px; margin: 0px auto 12px auto; border: 3px solid #9ca3af;'>✓</div>
                                <div style='color: #333333; font-size: 14px; font-weight: bold; text-align: center;'>Step 2</div>
                                <div style='color: #10b981; font-size: 13px; text-align: center; margin-top: 2px; font-weight: 600;'>DTO Bom Change Delta List Release (PRD)</div>
                            </td>
                            
                            <!-- Step 3 - Release Mechanical List in SAP -->
                            <td width='20%' align='center' valign='top'>
                                <div style='width: 48px; height: 48px; background-color: #6b7280; color: white; text-align: center; line-height: 48px; font-size: 18px; font-weight: bold; border-radius: 24px; margin: 0 auto 12px auto; border: 3px solid #9ca3af;'>3</div>
                                <div style='color: #333333; font-size: 14px; font-weight: bold; text-align: center;'>Step 3</div>
                                <div style='color: #6b7280; font-size: 13px; text-align: center; margin-top: 2px; font-weight: 500;'>SAP Mechanical Release (TBC)</div>
                            </td>
                            
                            <!-- Step 4 - Mechanical Release & SAP Changes Done -->
                            <td width='20%' align='center' valign='top'>
                                <div style='width: 48px; height: 48px; background-color: #6b7280; color: white; text-align: center; line-height: 48px; font-size: 18px; font-weight: bold; border-radius: 24px; margin: 0 auto 12px auto; border: 3px solid #9ca3af;'>4</div>
                                <div style='color: #333333; font-size: 14px; font-weight: bold; text-align: center;'>Step 4</div>
                                <div style='color: #6b7280; font-size: 13px; text-align: center; margin-top: 2px; font-weight: 500;'>Mechanical Release & SAP Changes Done (FP)</div>
                            </td>
                        </tr>
                    </table>
                </div>";

            // Approval Note Section
            if (!empty($note)) {
                $bodyContent .= "
                <div style='background-color: #e0f2fe; border: 1px solid #0284c7; border-radius: 8px; padding: 20px; margin-bottom: 20px;'>
                    <h3 style='color: #0369a1; margin: 0 0 10px 0; font-size: 16px;'>
                        💬 Approval Note
                    </h3>
                    <p style='margin: 0; color: #0369a1; font-style: italic;'>
                        \"" . htmlspecialchars($note) . "\"
                    </p>
                </div>";
            }

            $bodyContent .= "
                <!-- Project Details Section -->
                <div style='background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                    <div style='background-color: #059669; color: white; padding: 15px;'>
                        <h3 style='margin: 0; font-size: 18px;'>📋 Project Details</h3>
                    </div>
                    
                    <table style='border-collapse: collapse; border-spacing: 0; width: 100%;'>
                        <tbody>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold; width: 35%;'>
                                    Project Number
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($projectNo) . "
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
                                    Project Name
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($projectName) . "
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Status
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    <span style='background-color: #d1fae5; color: #059669; padding: 4px 8px; border-radius: 4px; font-weight: bold;'>
                                        ✅ APPROVED
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
                                    " . htmlspecialchars($releasedProjectData['submitted_by']) . "
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Submission Date & Time
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($submittedDate) . "
                                </td>
                            </tr>                               
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Approved By
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($reviewedBy) . "
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Approval Date & Time
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
                        This is an automated notification from the <b>DTO Configurator System</b>.<br>
                        Congratulations on your successful project approval!
                    </p>
                </div>
            </div>
            ";

            //TODO: simdilik om mail bu sekilde alıyorum cc ye eklemek gerekiyor
            $query = "SELECT email FROM users WHERE CONCAT(name, ' ', surname) = :orderManagerFullName";
            $omMail = DbManager::fetchPDOQueryData('php_auth', $query, [':orderManagerFullName' => $releasedProjectData['order_manager']])['data'][0]['email'] ?? '';
            MailManager::sendMail($subject, $bodyContent, 35, "dto_conf_success_publish_result", [], [], [$submittedByEmail, $reviewedByEmail, $omMail]);
        } else {
            // REJECTION EMAIL
            $revisionPrefix = $isRevision ? "🔄 REVISION - " : "";
            $subject = $revisionPrefix . "❌ REJECTED: " . $projectNo . " - " . $nachbauNo . " : Project Publish Request";

            $revisionBadge = $isRevision ? "<div style='display: inline-block; background-color: #fbbf24; color: #78350f; padding: 8px 16px; border-radius: 6px; font-weight: bold; margin-bottom: 15px;'>🔄 REVISION</div><br>" : "";

            $bodyContent = "
        <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
            <div style='background-color: #fee2e2; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                {$revisionBadge}
                <h2 style='color: #dc2626; margin: 0 0 10px 0; font-size: 24px;'>
                    ❌ Project Publish Request Rejected
                </h2>
                <p style='margin: 0; font-size: 16px; color: #991b1b;'>
                    Unfortunately, your project publish request has been <strong>REJECTED</strong> and requires revision.
                </p>
            </div>
            
            <div style='background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                <div style='background-color: #dc2626; color: white; padding: 15px;'>
                    <h3 style='margin: 0; font-size: 18px;'>📋 Project Details</h3>
                </div>
                
                <table style='border-collapse: collapse; border-spacing: 0; width: 100%;'>
                    <tbody>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold; width: 35%;'>
                                Project Number
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectNo) . "
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
                                Project Name
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($projectName) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Status
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                <span style='background-color: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-weight: bold;'>
                                    ❌ REJECTED
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
                                " . htmlspecialchars($releasedProjectData['submitted_by']) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Submission Date & Time
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($submittedDate) . "
                            </td>
                        </tr>                            
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Reviewed By
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . htmlspecialchars($reviewedBy) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                Rejection Date & Time
                            </td>
                            <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                " . date('d.m.Y H:i:s') . "
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>";

            if (!empty($note)) {
                $bodyContent .= "
            <div style='background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin-bottom: 20px;'>
                <h3 style='color: #92400e; margin: 0 0 10px 0; font-size: 16px;'>
                    💬 Rejection Reason
                </h3>
                <p style='margin: 0; color: #92400e; font-style: italic; background-color: white; padding: 15px; border-radius: 4px;'>
                    \"" . htmlspecialchars($note) . "\"
                </p>
            </div>";
            }

            $bodyContent .= "
            <div style='background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin-top: 20px;'>
                <p style='margin: 0; font-size: 14px; color: #6b7280; text-align: center;'>
                    This is an automated notification from the <b>DTO Configurator System</b>.<br>
                    For questions regarding this rejection, please contact the Digital Transformation team.
                </p>
            </div>
        </div>
        ";

            MailManager::sendMail($subject, $bodyContent, 35, "dto_conf_reject_publish_result", [], [$submittedByEmail], [$reviewedByEmail]);
        }
    }

    public function checkIfAllOrderChangeMaterialsAreSapDefined($releasedOrderChanges) {

        // Step 1: Get all unique materials in released order
        foreach ($releasedOrderChanges as $change) {
            if (!empty($change['material_added_number'])) {
                $orderChangeMaterials[] = $change['material_added_number'];
            }
            if (!empty($change['material_deleted_number'])) {
                $orderChangeMaterials[] = $change['material_deleted_number'];
            }
        }

        $orderChangeMaterials = array_values(array_unique($orderChangeMaterials));

        // Step 2: Check if any of materials are not defined in SAP
        $query = "SELECT id, material_number, sap_defined FROM materials WHERE material_number IN (:materials) AND sap_defined = 0";
        $notDefinedMaterials = DbManager::fetchPDOQueryData('dto_configurator', $query, [':materials' => $orderChangeMaterials])['data'];

        if (count($notDefinedMaterials) !== 0) {
            foreach($notDefinedMaterials as $materialData) {
                $materialStartsBy = $this->getSapMaterialPrefixByMaterialNo($materialData['material_number']);
                $materialFull =  $materialStartsBy . $materialData['material_number'];
                $materialDetail = $this->getMaterialDetail($materialFull);

                if (!empty($materialDetail['CREATED_ON'])) {
                    $this->updateMaterialSapDefined($materialData['material_number']);
                }
                else {
                    returnHttpResponse(400, "Material $materialFull has not been defined in SAP.");
                }
            }
        }
    }
}


$controller = new AdminController($_POST);

$response = match ($_GET['action']) {
    'getReleasedProjects' => $controller->getReleasedProjects(),
    'getReleasedProjectDetailsById' => $controller->getReleasedProjectDetailsById(),
    'getReleasedBomChangeWithOrderChanges' => $controller->getReleasedBomChangeWithOrderChanges(),
    'getDescriptionsOfAffectedDtoNumbers' => $controller->getDescriptionsOfAffectedDtoNumbers(),
    'getProjectOrderChanges' => $controller->getProjectOrderChanges(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    'updateProjectStatus' => $controller->updateProjectStatus(),
    'sendPublishResultMail' => $controller->sendPublishResultMail(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};