<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/shared.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/api/MtoolManager.php';
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');


class TkFormController extends BaseController {

    public function createTkForm(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | TK Form Create Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | TK Form Create Request With Following Parameters | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_INFO, ACTION_PROCESSING, implode(' | ', $_POST), "Create TK Form");

        if (!SharedManager::hasAccessRight(24, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized User for Create TK Form | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized User for Create TK Form | ".implode(' | ', $_POST),PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_ERROR, implode(' | ', $_POST), "Create TK Form");
            returnHttpResponse(400, "Unauthorized user to create a TK Form.");
        }

        $documentNumber = $_POST['documentNumber'];
        $dtoNumber = $_POST['dtoNumber'];
        $description = $_POST['description'];
        $descriptionTr = $_POST['descriptionTr'];
        $isRearbox = $_POST['isRearbox'] ?? 'no';
        $connectionType = $_POST['connectionType'] ?? null;
        $ctVtQuantity = $_POST['ctVtQuantity'] ?? null;

        $search = ['mm2', 'cm2', 'm2', 'dm2', 'km2'];
        $replace = ['mm²', 'cm²', 'm²', 'dm²', 'km²'];

        $description = str_replace($search, $replace, $description);
        $descriptionTr = str_replace($search, $replace, $descriptionTr);

        if ($isRearbox === 'yes') {
            if (empty($connectionType) || empty($ctVtQuantity)) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Rearbox fields are required | ".implode(' | ', $_POST));
                returnHttpResponse(400, "Connection Type and CT/VT Quantity are required when Rearbox is Yes");
            }
        }

        $this->tkFormRuleCheck('Create', $documentNumber, $dtoNumber);

        // INSERT TK FORM
        $query = "INSERT INTO tkforms(document_number, dto_number, description, description_tr, created_email, created_name, updated_email, updated_name)";
        $parameters[] = [
            trim($documentNumber), trim($dtoNumber), $description, $descriptionTr,
            SharedManager::$email, SharedManager::$fullname,
            SharedManager::$email, SharedManager::$fullname
        ];

        $responseInsert = DbManager::fetchInsert('dto_configurator', $query, $parameters);
        $lastInsertedTkFormId = $responseInsert['pdoConnection']->lastInsertId();

        // INSERT TK FORM SPECS
        $specsQuery = "INSERT INTO tkform_specs(tkform_id, is_rearbox, connection_type, ct_vt_quantity)";
        $specsParameters[] = [
            $lastInsertedTkFormId,
            $isRearbox,
            $isRearbox === 'yes' ? trim($connectionType) : null,
            $isRearbox === 'yes' ? (int)$ctVtQuantity : null
        ];

        DbManager::fetchInsert('dto_configurator', $specsQuery, $specsParameters);

        $data = $this->getTkFormById($lastInsertedTkFormId);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | TK Form Created Successfully With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("CREATED | TK Form Created Successfully With Following Parameters | ".implode(' | ', $_POST), PAGE_TKFORM,TKFORM_MODAL_INFO,ACTION_CREATED, implode(' | ', $_POST),"Delete TK Form");

        echo (json_encode($data));
        exit();
    }

    public function deleteTkForm(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | TK Form Delete Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | TK Form Delete Request With Following Parameters | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_INFO, ACTION_PROCESSING, implode(' | ', $_POST), "Delete TK Form");

        $id = $_POST['id'];
        $documentNumber = $_POST['documentNumber'];
        $dtoNumber = $_POST['dtoNumber'];

        if (!SharedManager::hasAccessRight(24, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized User for Delete TK Form | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized User for Delete TK Form | ".implode(' | ', $_POST),PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_ERROR, implode(' | ', $_POST), "Update TK Form");
            returnHttpResponse(400, "Unauthorized user to Delete a TK Form.");
        }

        $query = "SELECT * FROM tkforms WHERE id = :id AND deleted IS NULL";
        $oldTkForm = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $id])['data'][0];

        $query = "UPDATE tkform_materials SET deleted=:deletedAt, deleted_user=:delUser WHERE tkform_id=:tkform_id";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':tkform_id' => $id]);

        // Check if the dto_number already ends with "deleted"
        $deletedDtoNumber = str_ends_with($dtoNumber, ' deleted') ? $dtoNumber : $dtoNumber . ' deleted';
        $deletedDocumentNumber = str_ends_with($documentNumber, ' deleted') ? $documentNumber : $documentNumber . ' deleted';

        // Check if the modified dto_number already exists
        $query = "SELECT id FROM tkforms WHERE dto_number = :dtoNo";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':dtoNo' => $deletedDtoNumber]) ?? [];

        // Update the tkforms table with the appropriate dto_number and document_number
        $query = "UPDATE tkforms SET dto_number=:dtoNo, document_number=:docNo, deleted=:deletedAt, deleted_user=:delUser WHERE id=:id";
        DbManager::fetchPDOQuery('dto_configurator', $query, [
            ':dtoNo' => empty($result) ? $deletedDtoNumber : $deletedDtoNumber . ' (duplicate)',
            ':docNo' => $deletedDocumentNumber,
            ':delUser' => SharedManager::$fullname,
            ':deletedAt' => date('Y-m-d H:i:s'),
            ':id' => $id
        ]);

        $this->deleteAllProjectWorksOfDto($dtoNumber);

        //KEEP OLD/DELETED TK FORM CHANGE
        $query = "INSERT INTO tkforms_changes(process, document_number, dto_number, description, description_tr, created_email, created_name, updated_email, updated_name)";
        $parameters[] = [
            'DELETE', trim($oldTkForm['document_number']), trim($oldTkForm['dto_number']), $oldTkForm['description'], $oldTkForm['description_tr'],
            $oldTkForm['created_email'], $oldTkForm['created_name'],
            $oldTkForm['updated_email'], $oldTkForm['updated_name']
        ];
        DbManager::fetchInsert('dto_configurator', $query, $parameters);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | TK Form with Dto Number " . $dtoNumber . " is successfully deleted by " . SharedManager::$fullname);
        Journals::saveJournal("DELETED | TK Form with " . $dtoNumber . " is successfully deleted by " . SharedManager::$fullname, PAGE_TKFORM,TKFORM_MODAL_INFO,ACTION_DELETED, implode(' | ', $_POST),'Delete TK Form');
    }

    public function updateTkForm(): void  {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | TK Form Update Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | TK Form Update Request With Following Parameters | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_MODAL_INFO, ACTION_PROCESSING, implode(' | ', $_POST), "Update TK Form");

        $id = $_POST['id'];
        $documentNumber = $_POST['documentNumber'];
        $dtoNumber = $_POST['dtoNumber'];
        $description = $_POST['description'];
        $descriptionTr = $_POST['descriptionTr'];

        if (!SharedManager::hasAccessRight(24, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized User for Update TK Form | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized User for Update TK Form | ".implode(' | ', $_POST),PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_ERROR, implode(' | ', $_POST), "Update TK Form");
            returnHttpResponse(400, "Unauthorized user to Update a TK Form.");
        }

        $this->tkFormRuleCheck('Update', trim($documentNumber), trim($dtoNumber));

        $query = "SELECT * FROM tkforms WHERE id = :id AND deleted IS NULL";
        $oldTkForm = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $id])['data'][0];

        $query = "UPDATE tkforms SET document_number=:p1, dto_number=:p2, description=:p3, description_tr=:p4, updated_email=:p5, updated_name=:p6 WHERE id=:id";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':p1' => $documentNumber, ':p2' => $dtoNumber,
            ':p3' => $description, ':p4' => $descriptionTr, ':p5' => SharedManager::$email, ':p6' => SharedManager::$fullname, ':id' => $id]);


        //KEEP OLD/UPDATED TK FORM CHANGE
        $query = "INSERT INTO tkforms_changes(process, document_number, dto_number, description, description_tr, created_email, created_name, updated_email, updated_name)";
        $parameters[] = [
            'UPDATE', trim($oldTkForm['document_number']), trim($oldTkForm['dto_number']), $oldTkForm['description'], $oldTkForm['description_tr'],
            $oldTkForm['created_email'], $oldTkForm['created_name'],
            $oldTkForm['updated_email'], $oldTkForm['updated_name']
        ];
        DbManager::fetchInsert('dto_configurator', $query, $parameters);

        SharedManager::saveLog('log_dtoconfigurator',"UPDATED | TK Form with Dto Number " . $dtoNumber . " is successfully updated with following parameters | " . implode(' | ', $_POST));
        Journals::saveJournal("UPDATED | TK Form with Dto Number " . $dtoNumber . " is successfully updated with following parameters | " . implode(' | ', $_POST), PAGE_TKFORM,TKFORM_MODAL_INFO,ACTION_MODIFIED, implode(' | ', $_POST),'Update TK Form');
    }

    public function getTkFormById($id): array
    {
        $query = "SELECT * FROM tkforms WHERE id=:id AND deleted IS NULL";
        return DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $id])['data'][0] ?? [];
    }

    public function getTkForm(): void
    {
        $id = $_GET['id'];

        $query = "SELECT * FROM tkforms WHERE id = :id AND deleted IS NULL";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query,  [':id'=> $id])['data'][0] ?? null;

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | TK Form ID : " . $id . " details returned successfully.");
        Journals::saveJournal("RETURNED | TK Form ID : " . $id . " details returned successfully.", TKFORM_MAIN,TKFORM_MODAL_INFO,ACTION_VIEWED, implode(' | ', $_POST),'Get TK Form');

        echo json_encode($data);
        exit();
    }

    public function getTkFormByDtoNumber(): void
    {
        $dtoNumber = $_GET['dtoNumber'];

        $query = "SELECT * FROM tkforms WHERE dto_number = :dtoNumber AND deleted IS NULL";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query,  [':dtoNumber'=> $dtoNumber])['data'][0] ?? null;

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | TK Form DTO Number : " . $dtoNumber . " details returned successfully.");
        Journals::saveJournal("RETURNED | TK Form DTO Number : " . $dtoNumber . " details returned successfully.", TKFORM_MAIN,TKFORM_MODAL_INFO,ACTION_VIEWED, implode(' | ', $_POST),'Get TK Form');
        echo json_encode($data);
    }

    public function getAllTkForms(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | List of TK Forms Loading Request" . implode(' | ', $_GET));
        Journals::saveJournal("PROCESSING | List of TK Forms Loading Request", PAGE_TKFORM, TKFORM_MAIN, ACTION_PROCESSING, implode(' | ', $_POST), "Get All TK Forms");

        $query = "SELECT id, document_number, dto_number, description, created_name,
                     DATE_FORMAT(created, '%d.%m.%Y') AS created, 
                     DATE_FORMAT(updated, '%d.%m.%Y') AS updated 
                  FROM tkforms 
                  WHERE deleted IS NULL 
                  ORDER BY id DESC";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'] ?? [];

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | List of TK Forms are loaded successfully" . implode(' | ', $_GET));
        Journals::saveJournal("RETURNED | List of TK Forms are loaded successfully", PAGE_TKFORM, TKFORM_MAIN, ACTION_VIEWED, implode(' | ', $_GET), "Get All TK Forms");

        echo (json_encode( ['data' => $result]));
    }

    public function getOrdersOfTkForm(): void {
        $dtoNumber = $_GET['dtoNumber'];

        $query = "SELECT projectno
                FROM product_list p1
                WHERE mlfb LIKE :dtoNumber AND mlfb NOT LIKE '%KUKO%'
                AND filename = (
                    SELECT MAX(filename) 
                    FROM product_list p2 
                    WHERE p1.projectno = p2.projectno
                )
               GROUP BY projectno;";

        $projects = DbManager::fetchPDOQueryData('omportal', $query, [':dtoNumber'=>$dtoNumber.'%'])['data'] ?? [];

        $projectNumbers = array_column($projects, 'projectno');

        $projectDetails = [];
        if (!empty($projectNumbers)) {
            $mtoolResults = MtoolManager::getProjectDetailsByProjectNos($projectNumbers);

            // Create a lookup array indexed by FactoryNumber
            foreach ($mtoolResults as $detail) {
                $projectDetails[$detail['FactoryNumber']] = $detail['ProjectName'] ?? '';
            }
        }

        foreach ($projects as &$project) {
            $project['projectname'] = $projectDetails[$project['projectno']] ?? '';
        }
        unset($project);

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Get Orders Of TK Form Request Successful" . implode(' | ', $_GET));
        Journals::saveJournal("RETURNED | Get Orders Of TK Form Request Successful", PAGE_TKFORM, TKFORM_TK_ORDERS, ACTION_VIEWED, implode(' | ', $_GET), "Get All TK Form Orders");

        ob_start("ob_gzhandler");

        header("Content-Encoding: gzip");
        header("Content-Type: application/json");
        echo json_encode($projects);
        exit();
    }

    public function searchDtoNumbers(): void {
        $keyword = $_GET['keyword'];
        $query = "SELECT id, dto_number, description FROM tkforms WHERE dto_number LIKE :dtoNumber AND deleted IS NULL";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [ ':dtoNumber'=>"%$keyword%"])['data'] ?? [];
        echo(json_encode($data));
    }

    public function tkFormRuleCheck($actionStr, $documentNumber, $dtoNumber): void {

        if (!str_starts_with($documentNumber, "DTO_")) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Wrong Entry For TK Number - Should Starts with DTO | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Wrong Entry For TK Number - Should Starts with DTO | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_WARNING, implode(' | ', $_POST), $actionStr . "Create TK Form");
            returnHttpResponse(400, "TK Wrong Entry");
        }

        if (preg_match('/[*\/\-\.,]/', $dtoNumber)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Wrong Entry DTO Number - Special Characters | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Wrong Entry For DTO Number - Special Characters | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_ERROR, implode(' | ', $_POST), $actionStr . "Create TK Form");
            returnHttpResponse(400, "DTO Special Characters");
        }

        $rulesArray = $this->getDtoNumberStartsBy();
        $count = 0;
        foreach($rulesArray as $rule)
        {
            if(str_starts_with($dtoNumber, $rule))
                break;
            else
                $count++;
        }

        if($count === count($rulesArray)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Wrong Entry DTO Number - Should Starts with Rules | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Wrong Entry For DTO Number - Should Starts with Rules | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_ERROR, implode(' | ', $_POST), $actionStr . "Create TK Form");
            returnHttpResponse(400, "DTO Wrong Entry");
        }

        if ($actionStr === 'Create') {
            $query = "SELECT id FROM tkforms WHERE document_number = :docNumber OR dto_number = :dtoNumber AND deleted IS NULL";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [ ':docNumber' => $documentNumber, ':dtoNumber' => $dtoNumber])['data'] ?? [];
            if(!empty($result)) {
                SharedManager::saveLog('log_dtoconfigurator',"WARNING | TK Form is already exists | ".implode(' | ', $_POST));
                Journals::saveJournal("WARNING | TK Form is already exists | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_WARNING, implode(' | ', $_POST), $actionStr . "Create TK Form");
                returnHttpResponse(400, "TK Form Exists");
            }
        }
    }

    public function deleteAllProjectWorksOfDto($dtoNumber):void {
        //TK nın geçtiği bütün çalışmaları getir.
        $query = "SELECT id FROM project_work_view WHERE dto_number = :dtoNumber";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':dtoNumber' => $dtoNumber])['data'] ?? [];
        $projectWorkIds = array_column($result, 'id');

        if(!empty($projectWorkIds)) {
            //Bom'a eklenenleri sil.
            $query = "UPDATE bom_change SET active = 0, deleted_user = :delUser, deleted = :deletedAt WHERE project_work_id IN (:ids)";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $projectWorkIds]);

            //Çalışmadakileri sil.
            $query = "UPDATE project_works SET deleted = :deletedAt, deleted_user = :delUser WHERE id IN (:ids)";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $projectWorkIds]);

            //Varsa spare DTO çalışmalarını temizle.
            $query = "UPDATE project_works_spare SET deleted = :deletedAt, deleted_user = :delUser WHERE dto_number = :dtoNumber";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':dtoNumber' => $dtoNumber]);

            //Varsa extension DTO çalışmalarını temizle .
            $query = "UPDATE project_works_extensions SET deleted = :deletedAt, deleted_user = :delUser WHERE dto_number = :dtoNumber";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':dtoNumber' => $dtoNumber]);
        }
    }

    public function getCountOfNcAndTkNotesOfTkForm() {
        $id = $_GET['id'];
        $dtoNumber = $_GET['dtoNumber'];

        // NC COUNT
        $query = "SELECT COUNT(*) as NcCount FROM Stopped
                  LEFT JOIN Orders ON Orders.OrderID = Stopped.OrderID
                  LEFT JOIN Origin ON Origin.OriginID = Stopped.OriginID
                  WHERE DtoNo LIKE :DtoNo AND Origin.OriginID IN(32,94,96)";
        $ncCount = DbManager::fetchPDOQueryData('SI_EA_QR_Tracking', $query, [':DtoNo' => '%'.$dtoNumber.'%'])['data'][0]["NcCount"] ?? 0;

        // TK NOTES COUNT
        $query = "SELECT COUNT(*) as TKNoteCount FROM tkform_notes WHERE tkform_id = :id AND deleted IS NULL";
        $tkNoteCount = DbManager::fetchPDOQueryData('dto_configurator', $query,  [':id'=> $id])['data'][0]["TKNoteCount"] ?? 0;;

        $data['nc_count'] = $ncCount ?? 0;
        $data['tk_notes_count'] = $tkNoteCount ?? 0;

        echo json_encode($data);
        exit();
    }

    public function downloadJtFilesOfTkForm()
    {
        $dtoNumber = $_GET['dtoNumber'] ?? '';
        $materials = $_GET['materials'] ?? [];

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | " . $dtoNumber . " TK Form JT Collection Download Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | " . $dtoNumber . " TK Form JT Collection Download Request With Following Parameters | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_JT_COLLECTION, ACTION_PROCESSING, implode(' | ', $_POST), "TK Form JT Collection");

        if (empty($dtoNumber) || empty($materials))
            returnHttpResponse(400, 'DTO number or materials are missing.');


        $timestamp = date('dmYHis');
        $zipFileName = $dtoNumber . '_' . $timestamp . '.zip';
        $folderName = $dtoNumber . '_' . $timestamp;
        $sourceRootPath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Product_Management\\04_Design_Documents\\02_MV_Docs\\NormResimlerListeler";

        $zip = new ZipArchive();
        if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
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

            unset($materials);

            $zip->close();

            if (file_exists($zipFileName)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($zipFileName) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($zipFileName));
                ob_clean();
                flush();
                readfile($zipFileName);
                unlink($zipFileName);

                SharedManager::saveLog('log_dtoconfigurator',"RETURNED | " . $dtoNumber . " TK Form JT Collection Download Successful | ".implode(' | ', $_POST));
                Journals::saveJournal("RETURNED | " . $dtoNumber . " TK Form JT Collection Download Successful | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_JT_COLLECTION, ACTION_VIEWED, implode(' | ', $_POST), "TK Form JT Collection");

                exit;
            } else {

                SharedManager::saveLog('log_dtoconfigurator',"ERROR | ZIP file could not be created | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | ZIP file could not be created | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_JT_COLLECTION, ACTION_ERROR, implode(' | ', $_POST), "TK Form JT Collection");

                returnHttpResponse(500, 'ZIP file could not be created.');
            }
        }else {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Failed to open ZIP file | ".implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Failed to open ZIP file | ".implode(' | ', $_POST), PAGE_TKFORM, TKFORM_JT_COLLECTION, ACTION_ERROR, implode(' | ', $_POST), "TK Form JT Collection");

            returnHttpResponse(500, 'Failed to open ZIP file.');
        }
    }
}


$controller = new TkFormController($_POST);

$response = match ($_GET['action']) {
    'getAllTkForms' => $controller->getAllTkForms(),
    'getTkForm' => $controller->getTkForm(),
    'getTkFormByDtoNumber' => $controller->getTkFormByDtoNumber(),
    'getOrdersOfTkForm' => $controller->getOrdersOfTkForm(),
    'searchDtoNumbers' => $controller->searchDtoNumbers(),
    'downloadJtFilesOfTkForm' => $controller->downloadJtFilesOfTkForm(),
    'getCountOfNcAndTkNotesOfTkForm' => $controller->getCountOfNcAndTkNotesOfTkForm(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};
$response = match ($_POST['action']) {
    'createTkForm' => $controller->createTkForm(),
    'updateTkForm' => $controller->updateTkForm(),
    'deleteTkForm' => $controller->deleteTkForm(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};
