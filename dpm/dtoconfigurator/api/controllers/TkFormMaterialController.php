<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class TkFormMaterialController extends BaseController {

    public function getTKFormMaterialsById(): void
    {
        $id = $_GET['id'];
        $query = "SELECT * FROM tkform_materials_view WHERE id = :id";
        $data = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id])['data'][0] ?? [];
        echo(json_encode($data));
    }

    public function getTKFormMaterialsByTkFormId(): void
    {
        $tkformId = $_GET['id'];
        $query = "SELECT * FROM tkform_materials_view WHERE tkform_id = :tkformId";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':tkformId' => $tkformId])['data'];

        foreach($data as &$row) {
            $addedMaterialLatestRevDate = '';
            $deletedMaterialLatestRevDate = '';
            $addedMaterialCost = '';
            $addedMaterialCostDate = '';
            $deletedMaterialCost = '';
            $deletedMaterialCostDate = '';

            if (!empty($row['material_added_sap_material_number'])) {
                $query = "SELECT DATE_FORMAT(createdOn, '%d.%m.%Y') AS createdOn FROM sap_spiridon_078 WHERE object = :materialNo ORDER BY revLev DESC";
                $addedMaterialLatestRevDate = DbManager::fetchPDOQueryData('rpa', $query, [':materialNo' => $row['material_added_sap_material_number']])['data'][0]['createdOn'];

                $query = "SELECT total_cost_euro, DATE_FORMAT(date, '%d.%m.%Y') AS date FROM material_costs WHERE material = :materialNo";
                $addedMaterialCostData = DbManager::fetchPDOQueryData('rpa', $query, [':materialNo' => $row['material_added_sap_material_number']])['data'][0];
                $addedMaterialCost = $addedMaterialCostData['total_cost_euro'];
                $addedMaterialCostDate = $addedMaterialCostData['date'];
            }

            if (!empty($row['material_deleted_sap_material_number'])) {
                $query = "SELECT DATE_FORMAT(createdOn, '%d.%m.%Y') AS createdOn FROM sap_spiridon_078 WHERE object = :materialNo ORDER BY revLev DESC";
                $deletedMaterialLatestRevDate = DbManager::fetchPDOQueryData('rpa', $query, [':materialNo' => $row['material_deleted_sap_material_number']])['data'][0]['createdOn'];

                $query = "SELECT total_cost_euro, DATE_FORMAT(date, '%d.%m.%Y') AS date FROM material_costs WHERE material = :materialNo";
                $deletedMaterialCostData = DbManager::fetchPDOQueryData('rpa', $query, [':materialNo' => $row['material_deleted_sap_material_number']])['data'][0];
                $deletedMaterialCost = $deletedMaterialCostData['total_cost_euro'];
                $deletedMaterialCostDate = $deletedMaterialCostData['date'];
            }

            $row['material_added_last_revision_date'] = $addedMaterialLatestRevDate;
            $row['material_deleted_last_revision_date'] = $deletedMaterialLatestRevDate;
            $row['material_added_cost'] = $addedMaterialCost;
            $row['material_added_cost_date'] = $addedMaterialCostDate;
            $row['material_deleted_cost'] = $deletedMaterialCost;
            $row['material_deleted_cost_date'] = $deletedMaterialCostDate;
        }

        echo(json_encode($data));
        exit();
    }

    public function createTkFormMaterial(): void {
        SharedManager::saveLog('log_dtoconfigurator',"CREATED | TK Form Material Create Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("CREATED | TK Form Material Create Request With Following Parameters | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_ADD_MATERIAL_FORM, ACTION_PROCESSING, implode(' | ', $_POST), "Create TK Form Material");

        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized User for Creating TK Form Material | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized User for Creating TK Form Material | ".implode(' | ', $_POST),PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_ERROR, implode(' | ', $_POST), "Create TK Form Material");
            returnHttpResponse(400, "Unauthorized user to Creating TK Form Material.");
        }

        // Request parametreleri
        $tkformId = $_POST['tkformId'];
        $dtoNumber = $_POST['dtoNumber'];
        $projectNo = $_POST['referenceProject'];
        $operation = $_POST['operation'];
        $type = $_POST['changeType'];
        $quantity = $_POST['quantity'];
        $unit = $_POST['unit'];
        $acc = $_POST['specialNote'];
        $effective = $_POST['sidePanelEffect'] === 'true' ? 1 : 0;
        $affectedDtoNumbers = $_POST['affectedDtoNumbers'] ?? [];
        $materialAddedStartsBy = $_POST['materialAddedStartsBy'];
        $materialAddedId = $_POST['materialAdded'];
        $materialDeletedStartsBy = $_POST['materialDeletedStartsBy'];
        $materialDeletedId = $_POST['materialDeleted'];
        $secondaryWorkCenterId = $_POST['secondaryWcSelect'];
        $secondarySubWorkCenterName = $_POST['secondarySubWcSelectName'];

        // Malzeme listesi TK'ya daha önceden kaydedilmiş mi? (Devicelar birden fazla kmat da geçebildiği için eklenebilir.)
        $query = "SELECT id FROM tkform_materials_view 
                    WHERE tkform_id=:tkform_id 
                    AND material_added_id=:mAddedId 
                    AND material_deleted_id=:mDeletedId 
                    AND material_added_is_device = 0";
        $params = [':tkform_id' => $tkformId, ':mAddedId' => $materialAddedId, ':mDeletedId' => $materialDeletedId];
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, $params)['data'] ?? [];

        if(count($result) && empty($affectedDtoNumbers)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | TK Form Material Create Error | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | TK Form Material Create Error | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_ADD_MATERIAL_FORM, ACTION_ERROR, implode(' | ', $_POST), "Create TK Form Material");
            returnHttpResponse(400, "Material List has already been defined before.");
        }

        $query = "INSERT INTO tkform_materials(tkform_id, project_number, material_added_starts_by, material_added_id, material_deleted_starts_by, material_deleted_id, 
                                               operation, type, unit, acc, quantity, affected_dto_numbers, secondary_work_center_id, created_by, updated_by, effective)";

        if (empty($materialAddedStartsBy) && in_array($operation, ['add', 'replace'])) {
            $q1 = "SELECT sap_material_number FROM materials WHERE id = :mAddedId";
            $sapMaterial = DbManager::fetchPDOQueryData('dto_configurator', $q1, [':mAddedId' => $materialAddedId])['data'][0]['sap_material_number'];

            $prefixes = array('A7E00', 'A7ETKBL', 'A7ET0', 'A7ET', 'A7E');
            $materialAddedStartsBy = '';

            foreach ($prefixes as $prefix) {
                if (str_starts_with($sapMaterial, $prefix)) {
                    $materialAddedStartsBy = $prefix;
                    break;
                }
            }

            if (empty($materialAddedStartsBy))
                $materialAddedStartsBy = $this->getSapMaterialPrefixByMaterialId($materialAddedId);
        }

        if (empty($materialDeletedStartsBy) && in_array($operation, ['replace', 'delete'])) {
            $q2 = "SELECT sap_material_number FROM materials WHERE id = :mDeletedId";
            $sapMaterial = DbManager::fetchPDOQueryData('dto_configurator', $q2, [':mDeletedId' => $materialDeletedId])['data'][0]['sap_material_number'];

            $prefixes = array('A7E00', 'A7ETKBL', 'A7ET0', 'A7ET', 'A7E');
            $materialDeletedStartsBy = '';

            foreach ($prefixes as $prefix) {
                if (str_starts_with($sapMaterial, $prefix)) {
                    $materialDeletedStartsBy = $prefix;
                    break;
                }
            }

            if (empty($materialDeletedStartsBy))
                $materialDeletedStartsBy = $this->getSapMaterialPrefixByMaterialId($materialDeletedId);
        }

        if (empty($affectedDtoNumbers)) {
            $parameters[] = [
                $tkformId, $projectNo, $materialAddedStartsBy, $materialAddedId, $materialDeletedStartsBy, $materialDeletedId, $operation,
                $type, $unit, $acc, $quantity.'.000', null, $secondaryWorkCenterId, SharedManager::$fullname, SharedManager::$fullname, $effective
            ];

            $responseInsert = DbManager::fetchInsert('dto_configurator', $query, $parameters);
            $tkformMaterialId = $responseInsert["pdoConnection"]->lastInsertId();

            //tkform last updated
            $query = "UPDATE tkforms SET updated_email = :uptEmail, updated_name = :uptName, updated = :updated WHERE id = :tkformId";
            DbManager::fetchPDOQueryData('dto_configurator', $query, [':uptEmail' => SharedManager::$email, ':uptName' => SharedManager::$fullname, ':updated' => (new DateTime())->format('Y-m-d H:i:s'), ':tkformId' => $tkformId])['data'] ?? [];

            SharedManager::saveLog('log_dtoconfigurator',"CREATED | Material With Following Parameters Is Created in TK Form | ".implode(' | ', $_POST));
            Journals::saveJournal("CREATED | Material With Following Parameters Is Created in TK Form | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_MATERIAL_LIST, ACTION_CREATED, implode(' | ', $_POST), "Create Material List");

            // device ise secondary wc seçimi oluyor sonrasında material-kmatsı ona göre güncelliyor.
            // bu sayede device ekleme kısmında, nachbasunda bulunan ortakları getircek modalda.kontrolü lzım
            if (!empty($secondaryWorkCenterId)) {
                $this->updateDeviceMaterialKmatsWithSecondaryWorkCenters($materialAddedId, $secondaryWorkCenterId, $secondarySubWorkCenterName);
            }

            $this->addTkMaterialToProjectWorks($tkformMaterialId);
        } else {
            $affectedDtoData = array_map(function($dtoString) {
                list($id, $dto_number) = explode('|', $dtoString);
                return ['id' => $id, 'dto_number' => $dto_number];
            }, $affectedDtoNumbers);

            $affectedDtoData[] = ['id' => $tkformId, 'dto_number' => $dtoNumber]; //Add Current Dto too.

            foreach($affectedDtoData as $tkObj) {
                $parameters[] = [
                    $tkObj['id'], $projectNo, $materialAddedStartsBy, $materialAddedId, $materialDeletedStartsBy, $materialDeletedId, $operation,
                    $type, $unit, $acc, $quantity.'.000', implode('|', array_column($affectedDtoData, 'dto_number')), $secondaryWorkCenterId, SharedManager::$fullname, SharedManager::$fullname, $effective
                ];

                $tkIds[] = $tkObj['id'];
            }

            //Insert for other affected dto numbers
            DbManager::fetchInsert('dto_configurator', $query, $parameters);

            SharedManager::saveLog('log_dtoconfigurator',"CREATED | Material With Following Parameters Is Created in TK Form | ".implode(' | ', $_POST));
            Journals::saveJournal("CREATED | Material With Following Parameters Is Created in TK Form | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_MATERIAL_LIST, ACTION_CREATED, implode(' | ', $_POST), "Create Material List");

            //Get all ids of tk material data for affected dto numbers
            $query = "SELECT id FROM tkform_materials_view ORDER BY id DESC LIMIT " . count($affectedDtoData);
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'] ?? [];
            $tkformMaterialsIds = array_column($result, 'id');

            //Update TK Form's last updated time
            $inQuery = implode(',', array_fill(0, count($tkIds), '?'));
            $tkQuery = "UPDATE tkforms SET updated_email = ?, updated_name = ?, updated = ? WHERE id IN ($inQuery)";
            $params = array_merge([SharedManager::$email, SharedManager::$fullname, (new DateTime())->format('Y-m-d H:i:s')], $tkIds);
            DbManager::fetchPDOQueryData('dto_configurator', $tkQuery, $params)['data'] ?? [];

            if (!empty($secondaryWorkCenterId)) {
                $this->updateDeviceMaterialKmatsWithSecondaryWorkCenters($materialAddedId, $secondaryWorkCenterId, $secondarySubWorkCenterName);
            }

            //Eğer birden fazla DTO ya etki ediyosa bütün dtoların tk id lerinin bulunduğu project worksleri getirip hepsine insert atmalı. aşağıdaki metodu ya ayır ya da içini düzenle.
            foreach ($tkformMaterialsIds as $tkformMaterialId) {
                $this->addTkMaterialToProjectWorks($tkformMaterialId);
            }
        }
    }

    public function updateDeviceMaterialKmatsWithSecondaryWorkCenters($materialAddedId, $secondaryWorkCenterId, $secondarySubWorkCenterName) {

        $query = "SELECT id, work_center_id, material_kmats FROM materials WHERE id = :id";
        $material = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $materialAddedId])['data'][0] ?? [];
        $materialKmats = explode('|', $material['material_kmats']);

        $query = "SELECT DISTINCT parent_kmat, sub_kmat FROM material_kmat_subkmats WHERE work_center_id = :secondaryWcId AND (sub_kmat IS NULL OR sub_kmat_name = :subKmatName)";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':secondaryWcId' => $secondaryWorkCenterId, ':subKmatName' => $secondarySubWorkCenterName])['data'] ?? [];
        $parentKmats = array_column($result, 'parent_kmat');
        $subKmats = array_column($result, 'sub_kmat') ?? [];
        $allKmats = array_merge($parentKmats, $subKmats);

        $resultKmats = array_unique(array_merge($materialKmats, $allKmats));
        $resultKmatsStr = rtrim(implode('|', $resultKmats), '|');

        $query = "UPDATE materials SET material_kmats = :resultKmats WHERE id = :id";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':resultKmats' => $resultKmatsStr, ':id' => $materialAddedId])['data'];
    }

    public function addTkMaterialToProjectWorks($tkformMaterialId): void {
        $insertQuery = "INSERT INTO project_works(project_number, nachbau_number, nachbau_dto_number, dto_number, product_id, tkform_id, tkform_materials_id, work_center_id, 
                   release_status, last_updated_by, error_message_id, tk_kmats, common_kmats, nachbau_kmats, nachbau_panels, nachbau_typicals) VALUES ";

        //Eklenen TK Form Materyali
        $query = "SELECT id, tkform_id, dto_number, material_added_number, material_deleted_starts_by, material_deleted_number, material_added_work_center_id, material_deleted_work_center_id, type,
                         tk_work_center_id, tk_kmats, operation, effective FROM tkform_materials_view WHERE id=:id";
        $tkformMaterial = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $tkformMaterialId])['data'][0] ?? [];

        $query = "SELECT project_number, nachbau_number, nachbau_dto_number, dto_number, product_id, error_message_id
                  FROM project_work_view WHERE dto_number = :dtoNumber 
                  GROUP BY project_number, nachbau_number";
        $tkformProjectWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':dtoNumber' => $tkformMaterial['dto_number']])['data'] ?? [];
        if (empty($tkformProjectWorks))
            return;

        $projectWorks = [];
        foreach ($tkformProjectWorks as $index => $row) {

            if ($tkformMaterial['type'] === 'Accessories') {
                $accessoryObjectOfProject = $this->getAccessoryKmatObjectOfProject($row['project_number']);
                $accessoryTypicalNumber = $accessoryObjectOfProject['typical_no'];
                $accessoryParentKmat = $accessoryObjectOfProject['kmat'];
            }

            $nachbauResultItems = $this->checkMaterialStatusInNachbau($row['project_number'], $row['nachbau_number'], $tkformMaterial, $accessoryTypicalNumber ?? '', $accessoryParentKmat ?? '');

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
                $projectNumberKey => $row['project_number'],
                $nachbauNumberKey => $row['nachbau_number'],
                $nachbauDtoNumberKey => $row['nachbau_dto_number'],
                $dtoNumberKey => $row['dto_number'],
                $productIdKey => $row['product_id'],
                $tkformIdKey => $tkformMaterial['tkform_id'],
                $tkformMaterialsIdKey => $tkformMaterial['id'],
                $workCenterIdKey => $tkformMaterial['tk_work_center_id'],
                $tkKmatsKey => $tkformMaterial['tk_kmats'],
                $commonKmatsKey => $nachbauResultItems['common_kmats'],
                $nachbauKmatsKey => $nachbauResultItems['nachbau_kmats'],
                $releaseStatusKey => 'initial',
                $lastUpdatedByKey => SharedManager::$fullname,
                $errorMessageKey => intval($nachbauResultItems['error_message_id']),
                $nachbauPanelsKey => $nachbauResultItems['nachbau_panels'],
                $nachbauTypicalsKey => $nachbauResultItems['nachbau_typicals']
            ];

            $insertQuery .= "($projectNumberKey,$nachbauNumberKey, $nachbauDtoNumberKey, $dtoNumberKey, $productIdKey, $tkformIdKey, $tkformMaterialsIdKey, $workCenterIdKey, $releaseStatusKey, 
                                $lastUpdatedByKey, $errorMessageKey, $tkKmatsKey, $commonKmatsKey, $nachbauKmatsKey, $nachbauPanelsKey, $nachbauTypicalsKey),";
        }

        $insertQuery = rtrim($insertQuery, ',');
        DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $projectWorks, [], false);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | TK Material Added Into Project Work Successfully | " . implode(' | ', $_POST));
        Journals::saveJournal("CREATED | TK Material Added Into Project Work Successfully | " . implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_MATERIAL_LIST, ACTION_CREATED, implode(' | ', $_POST), "Create TK Form Material");
    }

    public function editTkFormMaterial(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Material Edit Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Material Edit Request With Following Parameters in TK Form | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_MATERIAL_LIST, ACTION_PROCESSING, implode(' | ', $_POST), "Edit TK Form Material List");

        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized User for Edit TK Form Material | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized User for Edit TK Form Material | ".implode(' | ', $_POST),PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_ERROR, implode(' | ', $_POST), "Edit TK Form Material");
            returnHttpResponse(400, "Unauthorized user to Edit TK Form Material.");
        }

        // Request parametreleri
        $id = intval($_POST['id']);
        $projectNo = $_POST['referenceProject'];
        $type = $_POST['changeType'];
        $quantity = $_POST['quantity'];
        $unit = $_POST['unit'];
        $acc = $_POST['specialNote'];
        $effective = intval($_POST['effective']);
        $forceUpdate = isset($_POST['forceUpdate']) && boolval($_POST['forceUpdate']);

        //old version
        $query = "SELECT * FROM tkform_materials_view WHERE id=:id";
        $oldTkFormMaterial = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id])['data'][0];

        $query = "SELECT project_number, nachbau_number, last_updated_by
                    FROM project_work_view 
                    WHERE tkform_materials_id = :id 
                    AND (release_items != NULL OR release_items != '' OR accessory_release_items != NULL OR accessory_release_items != '')";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' =>  $oldTkFormMaterial['id']])['data'] ?? [];

        if (!empty($result) && !$forceUpdate) {
            $data = ['data' => $result, 'responseStatus' => 'warning'];
        } else {
            $query = "UPDATE tkform_materials
                  SET project_number=:projectNo, type=:type, unit=:unit, acc=:acc, quantity=:quantity, updated_by=:updated_by, effective=:effective
                  WHERE id=:id";

            $parameters = [
                ':projectNo'=>$projectNo,
                ':type'=>$type,
                ':unit'=>$unit,
                ':quantity'=>$quantity.'.000',
                ':acc'=>$acc,
                ':effective' => $effective,
                ':updated_by'=>SharedManager::$fullname,
                ':id'=>$id
            ];

            DbManager::fetchPDOQuery('dto_configurator', $query, $parameters);

            $query = "SELECT * FROM project_work_view WHERE tkform_materials_id=:id";
            $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id])['data'] ?? [];
            $projectWorkIds = array_column($result, 'id');

            if (!empty($projectWorkIds))
            {
                foreach($result as $row){
                    if((intval($oldTkFormMaterial['effective'])) !== $effective)
                    {
                        $projectWorkId = $row['id'];
                        $this->updateNachbauPanelsOfProjectWork($oldTkFormMaterial, $projectWorkId, $effective);
                    }

                    if ($type === 'Accessories') {
                        $accessoryObjectOfProject = $this->getAccessoryKmatObjectOfProject($row['project_number']);
                        $accessoryTypicalNumber = $accessoryObjectOfProject['typical_no'];
                        $accessoryParentKmat = $accessoryObjectOfProject['kmat'];
                    }

                    $nachbauResultItems = $this->checkMaterialStatusInNachbau($row['project_number'], $row['nachbau_number'], $row, $accessoryTypicalNumber ?? '', $accessoryParentKmat ?? '');

                    $query = "UPDATE project_works SET release_items = '', accessory_release_items = '', release_status = 'initial', 
                                error_message_id = :errMsgId, common_kmats = :commonKmats, nachbau_kmats = :nachbauKmats, nachbau_panels = :nachbauPanels, nachbau_typicals = :nachbauTypicals
                              WHERE id = :id";

                    DbManager::fetchPDOQuery('dto_configurator', $query,[
                        ':id' => $row['id'],
                        ':errMsgId' => $nachbauResultItems['error_message_id'],
                        ':commonKmats' => $nachbauResultItems['common_kmats'],
                        ':nachbauKmats' => $nachbauResultItems['nachbau_kmats'],
                        ':nachbauPanels' => $nachbauResultItems['nachbau_panels'],
                        ':nachbauTypicals' => $nachbauResultItems['nachbau_typicals']
                    ]);
                }

                $query = "UPDATE bom_change SET active = 0, deleted_user = :delUser, deleted = :deletedAt WHERE project_work_id IN (:ids)";
                DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $projectWorkIds]);
            }

            //KEEP OLD/UPDATED TK FORM MATERIALS CHANGE
            $query = "INSERT INTO tkform_materials_changes(process, tkform_id, project_number, material_added_starts_by, material_added_id, material_deleted_starts_by, material_deleted_id, 
                                                operation, type, unit, acc, quantity, secondary_work_center_id, affected_dto_numbers, created_by, updated_by, effective)";

            $params[] = [
                'UPDATE', $oldTkFormMaterial['tkform_id'], $oldTkFormMaterial['project_number'],$oldTkFormMaterial['material_added_starts_by'], $oldTkFormMaterial['material_added_id'],
                $oldTkFormMaterial['material_deleted_starts_by'], $oldTkFormMaterial['material_deleted_id'], $oldTkFormMaterial['operation'], $oldTkFormMaterial['type'],
                $oldTkFormMaterial['unit'], $oldTkFormMaterial['acc'],$oldTkFormMaterial['quantity'], $oldTkFormMaterial['secondary_work_center_id'], $oldTkFormMaterial['affected_dto_numbers'],
                $oldTkFormMaterial['created_by'], $oldTkFormMaterial['updated_by'], $oldTkFormMaterial['effective']
            ];

            DbManager::fetchInsert('dto_configurator', $query, $params);

            SharedManager::saveLog('log_dtoconfigurator',"UPDATED | TK Form Material With Following Parameters Updated | ".implode(' | ', $_POST));
            Journals::saveJournal("UPDATED | TK Form Material With Following Parameters Updated | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_MATERIAL_LIST, ACTION_MODIFIED, implode(' | ', $_POST), "Edit TK Form Material List");

            $data = ['responseStatus' => 'success'];
        }
        echo json_encode($data);
        exit();
    }

    public function deleteTkFormMaterial(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | TK Form Material Delete Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | TK Form Material Delete Request With Following Parameters in TK Form | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_MATERIAL_LIST, ACTION_PROCESSING, implode(' | ', $_POST), "Delete Material List");
        $id = $_POST['id'];
        $selectedDtoOption = $_POST['selectedDtoOption']; //none - all - specific DTO
        $forceUpdate = isset($_POST['forceUpdate']) && boolval($_POST['forceUpdate']);

        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized User for Delete TK Form Material | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized User for Delete TK Form Material | ".implode(' | ', $_POST),PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_ERROR, implode(' | ', $_POST), "Delete TK Form Material");
            returnHttpResponse(400, "Unauthorized user to Delete TK Form Material.");
        }

        //old version
        $query = "SELECT * FROM tkform_materials_view WHERE id=:id";
        $oldTkFormMaterial = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id])['data'][0];

        $query = "SELECT project_number, nachbau_number, last_updated_by
                    FROM project_work_view 
                    WHERE tkform_materials_id = :id 
                    AND (release_items != NULL OR release_items != '' OR accessory_release_items != NULL OR accessory_release_items != '')";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' =>  $oldTkFormMaterial['id']])['data'] ?? [];

        if (!empty($result) && !$forceUpdate) {
            $data = ['data' => $result, 'responseStatus' => 'warning'];
        } else {
            //affected yoksa
            if ($selectedDtoOption === 'none') {
                $this->deleteAllProjectWorksOfTkMaterial($id);

                //Son olarak kendisini sil.
                $query = "UPDATE tkform_materials SET deleted = :deletedAt, deleted_user = :delUser WHERE id=:id";
                DbManager::fetchPDOQuery('dto_configurator', $query, [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':id'=>$id]);
            } else {
                $query = "SELECT dto_number, affected_dto_numbers FROM tkform_materials_view WHERE id = :id";
                $tkFormMaterial = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id])['data'][0] ?? [];
                $affectedDtoNumbersArr = explode('|', $tkFormMaterial['affected_dto_numbers']);

                //Listenin etkilediği bütün DTO'lar
                $query = "SELECT id FROM tkform_materials_view WHERE affected_dto_numbers IS NOT NULL AND affected_dto_numbers != '' 
                                AND material_added_id = :mAddedId AND material_deleted_id = :mDeletedId AND dto_number IN (:dtonumbers)";
                $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':mAddedId' => $oldTkFormMaterial['material_added_id'], ':mDeletedId' => $oldTkFormMaterial['material_deleted_id'], ':dtonumbers' => $affectedDtoNumbersArr])['data'] ?? [];
                $affectedDtoTkMaterialIds = array_column($result, 'id');

                if ($selectedDtoOption === 'all') {
                    // Etkilediği Bütün DTOlardan silme işlemi
                    $query = "UPDATE tkform_materials SET deleted = :deletedAt, deleted_user = :delUser WHERE id IN (:ids)";
                    DbManager::fetchPDOQuery('dto_configurator', $query, [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids'=>$affectedDtoTkMaterialIds]);

                    $query = "SELECT id FROM project_work_view WHERE tkform_materials_id IN (:ids)";
                    $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':ids' => $affectedDtoTkMaterialIds])['data'] ?? [];
                    $projectWorkIds = array_column($result, 'id');

                    if (!empty($projectWorkIds))
                    {
                        //Bom'a eklenenleri sil.
                        $query = "UPDATE bom_change SET active = 0, deleted_user = :delUser, deleted = :deletedAt WHERE project_work_id IN (:ids)";
                        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $projectWorkIds]);

                        //Çalışmadakileri sil.
                        $query = "UPDATE project_works SET deleted = :deletedAt, deleted_user = :delUser WHERE id IN (:ids)";
                        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $projectWorkIds]);
                    }
                }
                else {
                    $query = "SELECT id FROM tkform_materials_view WHERE affected_dto_numbers IS NOT NULL AND affected_dto_numbers != '' AND dto_number = :dtoNumber;";
                    $tkMaterialId = DbManager::fetchPDOQuery('dto_configurator', $query, [':dtoNumber' => $selectedDtoOption])['data'][0]['id'] ?? [];

                    //Ardından kendisini sil.
                    $query = "UPDATE tkform_materials SET deleted = :deletedAt, deleted_user = :delUser WHERE id=:id";
                    DbManager::fetchPDOQuery('dto_configurator', $query, [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':id'=>$tkMaterialId]);

                    //Diğer etkilenen DTOları getir
                    $affectedDtoNumbersArr = array_diff($affectedDtoNumbersArr, [$selectedDtoOption]);
                    //Eğer liste önceden 2 DTOya etki ediyorsa ve biri silinince tek dto ya etki eden standart bir hale geldiyse,
                    if (count($affectedDtoNumbersArr) === 1) {
                        $query = "UPDATE tkform_materials SET affected_dto_numbers = NULL WHERE id IN (:ids)";
                        DbManager::fetchPDOQuery('dto_configurator', $query, [':ids'=>$affectedDtoTkMaterialIds]);
                    } else {
                        $affectedDtoNumbers = implode('|', $affectedDtoNumbersArr);
                        $query = "UPDATE tkform_materials SET affected_dto_numbers = :affectedDtos WHERE id IN (:ids)";
                        DbManager::fetchPDOQuery('dto_configurator', $query, [':affectedDtos' => $affectedDtoNumbers, ':ids'=>$affectedDtoTkMaterialIds]);
                    }
                }

                //KEEP OLD/DELETED TK FORM MATERIALS CHANGE
                $query = "INSERT INTO tkform_materials_changes(process, tkform_id, project_number, material_added_starts_by, material_added_id, material_deleted_starts_by, material_deleted_id, 
                                                operation, type, unit, acc, quantity, secondary_work_center_id, affected_dto_numbers, created_by, updated_by, effective)";

                $params[] = [
                    'DELETE', $oldTkFormMaterial['tkform_id'], $oldTkFormMaterial['project_number'],$oldTkFormMaterial['material_added_starts_by'], $oldTkFormMaterial['material_added_id'],
                    $oldTkFormMaterial['material_deleted_starts_by'], $oldTkFormMaterial['material_deleted_id'], $oldTkFormMaterial['operation'], $oldTkFormMaterial['type'],
                    $oldTkFormMaterial['unit'], $oldTkFormMaterial['acc'],$oldTkFormMaterial['quantity'], $oldTkFormMaterial['secondary_work_center_id'], $oldTkFormMaterial['affected_dto_numbers'],
                    $oldTkFormMaterial['created_by'], $oldTkFormMaterial['updated_by'], $oldTkFormMaterial['effective']
                ];

                DbManager::fetchInsert('dto_configurator', $query, $params);

                SharedManager::saveLog('log_dtoconfigurator',"DELETED | TK Form Material List Deleted Successfully | ".implode(' | ', $_POST));
                Journals::saveJournal("DELETED | TK Form Material List Deleted Successfully | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_MATERIAL_LIST, ACTION_MODIFIED, implode(' | ', $_POST), "Delete TK Form Material List");
            }

            $data = ['responseStatus' => 'success'];
        }
        echo json_encode($data);
        exit();
    }

    //error control - İlgili TK nın geçtiği her DTO number da bu işlem uygulanmalı.
    public function updateMaterialListType(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Update TK Form Material List Type Request | ".implode(' | ', $_POST));

        $tkformId = $_POST['tkformId'];
        $materialAddedId = $_POST['materialAddedId'];
        $materialDeletedId = $_POST['materialDeletedId'];
        $type = $_POST['type'];
        $forceUpdate = isset($_POST['forceUpdate']) && boolval($_POST['forceUpdate']);

        $query = "SELECT id FROM tkform_materials WHERE tkform_id=:tkform_id AND material_added_id=:ma_id AND material_deleted_id=:md_id AND deleted IS NULL";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':tkform_id' => $tkformId, ':ma_id' => $materialAddedId, ':md_id' => $materialDeletedId])['data'] ?? [];
        $tkformMaterialsIds = array_column($result, 'id') ?? [];

        if(empty($tkformMaterialsIds))
            returnHttpResponse(400, 'No TK Form material list found.');

        $query = "SELECT project_number, nachbau_number, last_updated_by
                FROM project_work_view 
                WHERE tkform_materials_id IN (:ids) 
                AND (release_items IS NOT NULL AND release_items != '' OR accessory_release_items IS NOT NULL AND accessory_release_items != '')";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':ids' =>  $tkformMaterialsIds])['data'] ?? [];

        if (!empty($result) && !$forceUpdate) {
            echo json_encode(['responseStatus' => 'warning', 'data' => $result]);
        } else {
            $query = "UPDATE tkform_materials SET type=:type, updated_by=:uby WHERE id IN (:ids)";
            DbManager::fetchPDOQuery('dto_configurator', $query, [':type' => $type, ':uby' => SharedManager::$fullname, ':ids' => $tkformMaterialsIds]);

            $query = "SELECT * FROM project_work_view WHERE tkform_materials_id IN (:ids)";
            $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':ids' =>  $tkformMaterialsIds])['data'] ?? [];
            $projectWorkIds = array_column($result, 'id') ?? [];

            if (!empty($projectWorkIds)) {

                foreach($result as $row) {
                    if ($type === 'Accessories') {
                        $accessoryObjectOfProject = $this->getAccessoryKmatObjectOfProject($row['project_number']);
                        $accessoryTypicalNumber = $accessoryObjectOfProject['typical_no'];
                        $accessoryParentKmat = $accessoryObjectOfProject['kmat'];
                    }

                    $nachbauResultItems = $this->checkMaterialStatusInNachbau($row['project_number'], $row['nachbau_number'], $row, $accessoryTypicalNumber ?? '', $accessoryParentKmat ?? '');

                    $query = "UPDATE project_works SET release_items = '', accessory_release_items = '', release_status = 'initial', 
                                error_message_id = :errMsgId, common_kmats = :commonKmats, nachbau_kmats = :nachbauKmats, nachbau_panels = :nachbauPanels, nachbau_typicals = :nachbauTypicals
                              WHERE id = :id";

                    DbManager::fetchPDOQuery('dto_configurator', $query,[
                        ':id' => $row['id'],
                        ':errMsgId' => $nachbauResultItems['error_message_id'],
                        ':commonKmats' => $nachbauResultItems['common_kmats'],
                        ':nachbauKmats' => $nachbauResultItems['nachbau_kmats'],
                        ':nachbauPanels' => $nachbauResultItems['nachbau_panels'],
                        ':nachbauTypicals' => $nachbauResultItems['nachbau_typicals']
                    ]);
                }

                $query = "UPDATE bom_change SET active = 0, deleted_user = :delUser, deleted = :deletedAt WHERE project_work_id IN (:ids)";
                DbManager::fetchPDOQuery('dto_configurator', $query, [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $projectWorkIds]);
            }

            SharedManager::saveLog('log_dtoconfigurator',"UPDATED | TK Form Material List Type Updated Successfully | ".implode(' | ', $_POST));
            Journals::saveJournal("UPDATED | TK Form Material List Type Updated Successfully", PAGE_TKFORM, TKFORM_MODAL_MATERIAL_LIST, ACTION_MODIFIED, implode(' | ', $_POST), "Update Material List Type");

            echo json_encode(['responseType' => 'success', 'type' => $type]);
        }
        exit();
    }


    public function deleteAllProjectWorksOfTkMaterial($id):void {
        $query = "SELECT id FROM project_work_view WHERE tkform_materials_id=:id";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $id])['data'] ?? [];
        $projectWorkIds = array_column($result, 'id');

        if(!empty($projectWorkIds)) {
            //Bom'a eklenenleri sil.
            $query = "UPDATE bom_change SET active = 0, deleted_user = :delUser, deleted = :deletedAt WHERE project_work_id IN (:ids)";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $projectWorkIds]);

            //Çalışmadakileri sil.
            $query = "UPDATE project_works SET deleted = :deletedAt, deleted_user = :delUser WHERE id IN (:ids)";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $projectWorkIds]);
        }
    }
}

$controller = new TkFormMaterialController($_POST);

$response = match ($_GET['action']) {
    'getTKFormMaterialsByTkFormId' => $controller->getTKFormMaterialsByTkFormId(),
    'getTKFormMaterialsById' => $controller->getTKFormMaterialsById(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    'createTkFormMaterial' => $controller->createTkFormMaterial(),
    'editTkFormMaterial' => $controller->editTkFormMaterial(),
    'deleteTkFormMaterial' => $controller->deleteTkFormMaterial(),
    'updateMaterialListType' => $controller->updateMaterialListType(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};