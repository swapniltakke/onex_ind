<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class BomNotesController extends BaseController
{
    public function getAllBomNotesByProjectAndNachbau(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | BOM Note Get All Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | BOM Note Get All Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_PROCESSING, implode(' | ', $_POST), "Get All BOM Note");

        $projectNo = $_GET['projectNo'];
        $nachbauNo = $_GET['nachbauNo'];

        $query = "SELECT id, project_number, file_name, nachbau_number, note, user_created, created
                  FROM bom_notes 
                  WHERE project_number = :projectNo 
                  AND nachbau_number = :nachbauNo 
                  AND deleted IS NULL 
                  ORDER BY id DESC";

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':projectNo' => $projectNo, ':nachbauNo' => $nachbauNo])['data'] ?? [];

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | BOM Note Get All Successful.");
        Journals::saveJournal("RETURNED | BOM Note Get All Successful", PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_VIEWED, implode(' | ', $_POST), "BOM Note Get All");
        echo json_encode($data);exit();
    }

    private string $basePath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Product_Management\\04_Design_Documents\\99_DTO_Configurator_Files";
    private string $uploadSubPath = "1_BOM_Note_Images";
    private array $acceptedTypes = ["jpeg", "jpg", "png"];
    private int $fileSizeLimit = 3145728;

    public function addBomNoteToProject(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | BOM Note Create Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | BOM Note Create Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_PROCESSING, implode(' | ', $_POST), "Create BOM Note");

        $projectNo = $_POST['projectNo'];
        $nachbauNo = $_POST['nachbauNo'];
        $note = $_POST['note'];
        $imageFileUploaded = isset($_POST['imageFileUploaded']) && ($_POST['imageFileUploaded'] === 'true');
        $fileName = '';
        $userCreated = SharedManager::$fullname;

        if ($imageFileUploaded) {
            if (isset($_FILES['image']) || !empty($_FILES['image']['name'])) {
                $image = $_FILES['image'];
                $fileInfo = pathinfo($image['name']);
                $extension = strtolower($fileInfo['extension']);

                // Set the upload path
                $uploadPath = "$this->basePath\\$this->uploadSubPath";
                if (!file_exists($uploadPath)) {
                    SharedManager::saveLog('log_dtoconfigurator',"ERROR | BOM Note Create Error | ".implode(' | ', $_POST));
                    Journals::saveJournal("ERROR | BOM Note Create Error | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Create BOM Note");
                    returnHttpResponse(400, "Directory not found.");
                }

                // Validate the file extension
                if (!in_array($extension, $this->acceptedTypes)) {
                    SharedManager::saveLog('log_dtoconfigurator',"ERROR | BOM Note Create Error | ".implode(' | ', $_POST));
                    Journals::saveJournal("ERROR | BOM Note Create Error | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Create BOM Note");
                    returnHttpResponse(400, "Extension must be " . join(', ', $this->acceptedTypes));
                }

                // Validate the file size
                $fileSize = $image['size'];
                if ($fileSize > $this->fileSizeLimit) {
                    SharedManager::saveLog('log_dtoconfigurator',"ERROR | BOM Note Create Error | ".implode(' | ', $_POST));
                    Journals::saveJournal("ERROR | BOM Note Create Error | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Create BOM Note");
                    returnHttpResponse(400, "File size must be maximum 3MB");
                }

                // Check for file upload errors
                if ($image['error']) {
                    SharedManager::saveLog('log_dtoconfigurator',"ERROR | BOM Note Create Error | ".implode(' | ', $_POST));
                    Journals::saveJournal("ERROR | BOM Note Create Error | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Create BOM Note");
                    returnHttpResponse(500, "Unexpected error occurs : {$image['name']}");
                }

                // Set the file name using last six of projectNo and first six of nachbauNo
                $fileName = substr($projectNo, -6) . '_' . substr($nachbauNo, 0, 6) . '_' . date("Ymd_His") . '.' . $extension;
                $tempPath = $image['tmp_name'];
                $targetPath = "$uploadPath\\$fileName";

                // Move the uploaded file to the target path
                if (!move_uploaded_file($tempPath, $targetPath)) {
                    SharedManager::saveLog('log_dtoconfigurator',"ERROR | BOM Note Create Error | ".implode(' | ', $_POST));
                    Journals::saveJournal("ERROR | BOM Note Create Error | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Create BOM Note");
                    returnHttpResponse(500, "Unexpected error occurs");
                }
            }
        }

        $query = "INSERT INTO bom_notes(project_number, nachbau_number, note, file_name, user_created)";
        $parameters[] = [$projectNo, $nachbauNo, $note, $fileName, $userCreated];
        DbManager::fetchInsert('dto_configurator', $query, $parameters);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | BOM Note Successfully Created");
        Journals::saveJournal("CREATED | BOM Note Successfully Created", PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_CREATED, implode(' | ', $_POST), "Create BOM Note");
    }

    public function deleteBomNoteById(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | BOM Note Delete Request With Following Parameters | ".implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | BOM Note Delete Request With Following Parameters | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_PROCESSING, implode(' | ', $_POST), "Delete BOM Note");

        $noteId = $_POST['noteId'];
        $query = "SELECT id, file_name FROM bom_notes WHERE id = :noteId";
        $bomNote = DbManager::fetchPDOQueryData('dto_configurator', $query, [':noteId' => $noteId])['data'][0] ?? [];

        $query = "UPDATE bom_notes SET deleted_user = :delUser, deleted = :deletedAt WHERE id = :noteId";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':delUser' => SharedManager::$fullname,':deletedAt' => date('Y-m-d H:i:s'),':noteId' => $noteId]);

        if (!empty($bomNote['file_name'])) {
            $filePath = $this->basePath . "\\" . $this->uploadSubPath . "\\" . $bomNote['file_name'];

            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    SharedManager::saveLog('log_dtoconfigurator',"ERROR | BOM Note Delete Error | ".implode(' | ', $_POST));
                    Journals::saveJournal("ERROR | BOM Note Delete Error | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Delete BOM Note");
                    returnHttpResponse(400, 'An error occurs while removing the file.');

                }
            } else {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | BOM Note Delete Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | BOM Note Delete Error | ".implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_ERROR, implode(' | ', $_POST), "Delete BOM Note");
                returnHttpResponse(400, 'The file which will be removed could not found.');
            }
        }

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | BOM Note Successfully Deleted");
        Journals::saveJournal("DELETED | BOM Note Successfully Deleted", PAGE_PROJECTS, DESIGN_DETAIL_BOM_NOTES, ACTION_DELETED, implode(' | ', $_POST), "Delete BOM Note");
    }
}

$controller = new BomNotesController($_POST);

$response = match ($_GET['action']) {
    'getAllBomNotesByProjectAndNachbau' => $controller->getAllBomNotesByProjectAndNachbau(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    'addBomNoteToProject' => $controller->addBomNoteToProject(),
    'deleteBomNoteById' => $controller->deleteBomNoteById(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};
